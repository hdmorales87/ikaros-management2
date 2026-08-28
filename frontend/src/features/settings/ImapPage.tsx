import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { getGridRows, GridRow, mailApi } from '../../api'
import CatalogPage from '../catalogs/CatalogPage'

export default function ImapPage() {
  const [imapId, setImapId] = useState(0)
  const [message, setMessage] = useState('')
  const [testing, setTesting] = useState(false)
  const configs = useQuery({ queryKey: ['imap'], queryFn: () => getGridRows('imap', '', {}, ['servidor', 'correo']) })
  const rules = useQuery({ queryKey: ['imap-rules', imapId], queryFn: () => getGridRows('imap_reglas', '', { id_imap: imapId }, ['palabra_clave', 'tipo', 'asunto_default']), enabled: imapId > 0 })

  async function testConnection() {
    setTesting(true); setMessage('')
    try { const result = await mailApi.checkImap(); setMessage(`Conexión correcta. Carpetas: ${result.folders}`) } catch { setMessage('No fue posible conectar con IMAP.') } finally { setTesting(false) }
  }

  return <section><div className="topbar"><div><h1>Configuración IMAP</h1><p className="muted">Configura correo entrante y reglas de clasificación.</p></div></div><div className="panel"><h2>Cuenta IMAP</h2>{message && <p className="muted" role="status">{message}</p>}<button className="secondary" onClick={testConnection} disabled={testing}>{testing ? 'Conectando...' : 'Probar conexión'}</button><div className="table-wrap"><table><thead><tr><th>Servidor</th><th>Correo</th><th>Puerto</th><th>TLS</th><th>Acción</th></tr></thead><tbody>{configs.data?.map((config: GridRow, index) => <tr key={String(config.id ?? index)}><td>{String(config.servidor ?? '-')}</td><td>{String(config.correo ?? '-')}</td><td>{String(config.puerto ?? '-')}</td><td>{String(config.tls ?? '-')}</td><td><button className="secondary" onClick={() => setImapId(Number(config.id))}>Ver reglas</button></td></tr>)}</tbody></table></div></div><CatalogPage table="imap" title="Nueva configuración IMAP" description="Registra una cuenta de correo entrante." fields={['servidor', 'correo', 'password', 'puerto', 'tls']} />{imapId > 0 && <div className="panel"><h2>Reglas IMAP</h2><p className="muted">Cuenta #{imapId}.</p><div className="table-wrap"><table><thead><tr><th>Palabra clave</th><th>Tipo</th><th>Asunto por defecto</th></tr></thead><tbody>{rules.data?.map((rule: GridRow, index) => <tr key={String(rule.id ?? index)}><td>{String(rule.palabra_clave ?? '-')}</td><td>{String(rule.tipo ?? '-')}</td><td>{String(rule.asunto_default ?? '-')}</td></tr>)}</tbody></table></div></div>}</section>
}
