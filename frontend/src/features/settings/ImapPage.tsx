import { FormEvent, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { apiErrorMessage, catalogApi, GridRow, imapApi, ImapRule, mailApi } from '../../api'
import CatalogPage from '../catalogs/CatalogPage'

type RuleForm = { palabra_clave: string; tipo: 'incidencia' | 'problema' | 'servicio'; impacto: string; urgencia: string; id_area: string; id_categoria: string; id_subcategoria: string; asunto_default: string }
const emptyRule: RuleForm = { palabra_clave: '', tipo: 'incidencia', impacto: '', urgencia: '', id_area: '', id_categoria: '', id_subcategoria: '', asunto_default: '' }

export default function ImapPage() {
  const [imapId, setImapId] = useState(0)
  const [message, setMessage] = useState('')
  const [testing, setTesting] = useState(false)
  const [rule, setRule] = useState<RuleForm>(emptyRule)
  const [editingRuleId, setEditingRuleId] = useState<number | null>(null)
  const queryClient = useQueryClient()
  const configs = useQuery({ queryKey: ['v1-imap-accounts'], queryFn: () => catalogApi.list('imap-accounts') })
  const rules = useQuery({ queryKey: ['v1-imap-rules', imapId], queryFn: () => imapApi.rules(imapId), enabled: imapId > 0 })
  const saveRule = useMutation({
    mutationFn: () => {
      const data = { ...rule, impacto: Number(rule.impacto), urgencia: Number(rule.urgencia), id_area: Number(rule.id_area), id_categoria: Number(rule.id_categoria), id_subcategoria: Number(rule.id_subcategoria) }
      return editingRuleId ? imapApi.updateRule(imapId, editingRuleId, data) : imapApi.createRule(imapId, data)
    },
    onSuccess: () => { queryClient.invalidateQueries({ queryKey: ['v1-imap-rules', imapId] }); setRule(emptyRule); setEditingRuleId(null); setMessage('Regla IMAP guardada.') },
    onError: (error) => setMessage(apiErrorMessage(error, 'No fue posible guardar la regla IMAP.')),
  })
  const deactivateRule = useMutation({ mutationFn: (id: number) => imapApi.deactivateRule(imapId, id), onSuccess: () => { queryClient.invalidateQueries({ queryKey: ['v1-imap-rules', imapId] }); setMessage('Regla IMAP desactivada.') }, onError: (error) => setMessage(apiErrorMessage(error, 'No fue posible desactivar la regla.')) })

  async function testConnection() {
    setTesting(true)
    setMessage('')
    try {
      const result = await mailApi.checkImap()
      setMessage(`Conexión correcta. Carpetas: ${result.folders}`)
    } catch {
      setMessage('No fue posible conectar con IMAP.')
    } finally {
      setTesting(false)
    }
  }

  function selectAccount(id: number) { setImapId(id); setEditingRuleId(null); setRule(emptyRule); setMessage('') }
  function submitRule(event: FormEvent) { event.preventDefault(); saveRule.mutate() }
  function editRule(row: ImapRule) { setEditingRuleId(row.id); setRule({ palabra_clave: row.palabra_clave, tipo: row.tipo, impacto: String(row.impacto), urgencia: String(row.urgencia), id_area: String(row.id_area), id_categoria: String(row.id_categoria), id_subcategoria: String(row.id_subcategoria), asunto_default: row.asunto_default }); setMessage('') }
  function setRuleField(field: keyof RuleForm, value: string) { setRule((current) => ({ ...current, [field]: value })) }

  return <section>
    <div className="topbar"><div><h1>Configuración IMAP</h1><p className="muted">Configura correo entrante y reglas de clasificación.</p></div></div>
    <div className="panel"><h2>Cuenta IMAP</h2>{message && <p className="muted" role="status">{message}</p>}<button className="secondary" type="button" onClick={testConnection} disabled={testing}>{testing ? 'Conectando...' : 'Probar conexión'}</button><div className="table-wrap"><table><thead><tr><th>Servidor</th><th>Correo</th><th>Puerto</th><th>TLS</th><th>Acción</th></tr></thead><tbody>{configs.data?.map((config, index) => <tr key={String(config.id ?? index)}><td>{String(config.servidor ?? '-')}</td><td>{String(config.correo ?? '-')}</td><td>{String(config.puerto ?? '-')}</td><td>{String(config.tls ?? '-')}</td><td><button className="secondary" type="button" onClick={() => selectAccount(Number(config.id))}>Ver reglas</button></td></tr>)}</tbody></table></div></div>
    <CatalogPage resource="imap-accounts" title="Nueva configuración IMAP" description="Registra una cuenta de correo entrante." fields={['servidor', 'correo', 'password', 'puerto', 'tls']} sensitiveFields={['password']} />
    {imapId > 0 && <div className="panel"><h2>{editingRuleId ? 'Editar regla IMAP' : 'Nueva regla IMAP'}</h2><p className="muted">Cuenta #{imapId}.</p><form onSubmit={submitRule}><div className="form-grid"><label className="field">Palabra clave<input value={rule.palabra_clave} onChange={(event) => setRuleField('palabra_clave', event.target.value)} maxLength={50} required /></label><label className="field">Tipo<select value={rule.tipo} onChange={(event) => setRuleField('tipo', event.target.value)}><option value="incidencia">Incidencia</option><option value="problema">Problema</option><option value="servicio">Servicio</option></select></label><label className="field">Impacto<input type="number" min="1" value={rule.impacto} onChange={(event) => setRuleField('impacto', event.target.value)} required /></label><label className="field">Urgencia<input type="number" min="1" value={rule.urgencia} onChange={(event) => setRuleField('urgencia', event.target.value)} required /></label><label className="field">Área<input type="number" min="1" value={rule.id_area} onChange={(event) => setRuleField('id_area', event.target.value)} required /></label><label className="field">Categoría<input type="number" min="1" value={rule.id_categoria} onChange={(event) => setRuleField('id_categoria', event.target.value)} required /></label><label className="field">Subcategoría<input type="number" min="1" value={rule.id_subcategoria} onChange={(event) => setRuleField('id_subcategoria', event.target.value)} required /></label><label className="field">Asunto predeterminado<input value={rule.asunto_default} onChange={(event) => setRuleField('asunto_default', event.target.value)} maxLength={255} required /></label></div><button className="primary" disabled={saveRule.isPending}>{editingRuleId ? 'Guardar regla' : 'Crear regla'}</button>{editingRuleId && <button className="secondary" type="button" onClick={() => { setEditingRuleId(null); setRule(emptyRule) }}>Cancelar</button>}</form><div className="table-wrap"><table><thead><tr><th>Palabra clave</th><th>Tipo</th><th>Asunto</th><th>Acciones</th></tr></thead><tbody>{rules.data?.map((row, index) => <tr key={String(row.id ?? index)}><td>{String(row.palabra_clave ?? '-')}</td><td>{String(row.tipo ?? '-')}</td><td>{String(row.asunto_default ?? '-')}</td><td><button className="secondary" type="button" onClick={() => editRule(row)}>Editar</button><button className="secondary" type="button" onClick={() => row.id && deactivateRule.mutate(Number(row.id))}>Desactivar</button></td></tr>)}</tbody></table></div></div>}
  </section>
}
