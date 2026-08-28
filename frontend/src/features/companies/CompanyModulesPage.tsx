import { useEffect, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { companyApi } from '../../api'
import { getConfig } from '../../config'

export default function CompanyModulesPage() {
  const queryClient = useQueryClient(); const [selected, setSelected] = useState<number[]>([]); const [message, setMessage] = useState(''); const config = useQuery({ queryKey: ['configuration'], queryFn: getConfig }); const active = useQuery({ queryKey: ['company-modules'], queryFn: companyApi.modules }); const company = useQuery({ queryKey: ['company'], queryFn: companyApi.current }); const save = useMutation({ mutationFn: () => companyApi.saveModules(selected, Number(company.data?.id)), onSuccess: () => { queryClient.invalidateQueries({ queryKey: ['company-modules'] }); setMessage('Módulos guardados correctamente.') }, onError: () => setMessage('No fue posible guardar los módulos.') })
  useEffect(() => { if (active.data) setSelected(active.data) }, [active.data])
  function toggle(id: number) { setSelected((current) => current.includes(id) ? current.filter((item) => item !== id) : [...current, id]) }
  const modules = config.data?.modulos || []
  return <section><div className="topbar"><div><h1>Módulos de empresa</h1><p className="muted">Activa las funcionalidades disponibles para la organización.</p></div></div><div className="panel"><fieldset className="permission-list"><legend>Módulos disponibles</legend>{config.isLoading || active.isLoading ? <p className="muted">Cargando módulos...</p> : modules.map((module) => <label className="permission-item" key={module.modulo}><input type="checkbox" checked={selected.includes(module.modulo || 0)} onChange={() => toggle(module.modulo || 0)} />{module.titulo}</label>)}</fieldset>{message && <p className="muted" role="status">{message}</p>}<button className="primary" onClick={() => save.mutate()} disabled={save.isPending || !company.data?.id}>Guardar módulos</button></div></section>
}
