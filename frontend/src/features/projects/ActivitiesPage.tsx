import { FormEvent, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { getGridRows, gridApi, GridRow } from '../../api'

export default function ActivitiesPage() {
  const queryClient = useQueryClient()
  const [activityId, setActivityId] = useState(0)
  const [name, setName] = useState('')
  const [responsible, setResponsible] = useState(0)
  const [message, setMessage] = useState('')
  const activities = useQuery({ queryKey: ['all-activities'], queryFn: () => getGridRows('proyectos_actividades', '', {}, ['nombre']) })
  const users = useQuery({ queryKey: ['activity-users'], queryFn: () => getGridRows('users', '', { activo: true }, ['nombre', 'apellido']) })
  const subactivities = useQuery({ queryKey: ['subactivities', activityId], queryFn: () => getGridRows('proyectos_subactividades', '', { id_actividad: activityId }, ['nombre']), enabled: activityId > 0 })
  const create = useMutation({ mutationFn: () => gridApi.insert('proyectos_subactividades', { id_actividad: activityId, nombre: name, estado: 0 }), onSuccess: () => { setName(''); setMessage('Subactividad creada correctamente.'); queryClient.invalidateQueries({ queryKey: ['subactivities', activityId] }) }, onError: () => setMessage('No fue posible crear la subactividad.') })
  const assign = useMutation({ mutationFn: () => gridApi.update('proyectos_actividades', { id: activityId, id_responsable: responsible }), onSuccess: () => { setMessage('Responsable asignado.'); queryClient.invalidateQueries({ queryKey: ['all-activities'] }) }, onError: () => setMessage('No fue posible asignar el responsable.') })

  function submit(event: FormEvent) { event.preventDefault(); create.mutate() }

  return <section><div className="topbar"><div><h1>Actividades y subactividades</h1><p className="muted">Gestiona el desglose de trabajo de los proyectos.</p></div></div><div className="panel"><label className="field">Actividad<select value={activityId} onChange={(event) => { setActivityId(Number(event.target.value)); setMessage('') }}><option value="0">Selecciona una actividad</option>{activities.data?.map((activity: GridRow) => <option value={Number(activity.id)} key={String(activity.id)}>{String(activity.nombre || activity.id)}</option>)}</select></label>{activityId > 0 && <><form onSubmit={submit}><label className="field">Nueva subactividad<input value={name} onChange={(event) => setName(event.target.value)} required /></label><button className="primary" disabled={create.isPending}>Crear subactividad</button></form><label className="field">Responsable<select value={responsible} onChange={(event) => setResponsible(Number(event.target.value))}><option value="0">Selecciona un responsable</option>{users.data?.map((user: GridRow) => <option value={Number(user.id)} key={String(user.id)}>{String(user.nombre ?? '')} {String(user.apellido ?? '')}</option>)}</select></label><button className="secondary" onClick={() => assign.mutate()} disabled={!responsible || assign.isPending}>Asignar responsable</button></>}{message && <p className="muted" role="status">{message}</p>}</div><div className="panel"><h2>Subactividades</h2><div className="table-wrap"><table><thead><tr><th>ID</th><th>Nombre</th><th>Estado</th></tr></thead><tbody>{subactivities.data?.map((item: GridRow, index) => <tr key={String(item.id ?? index)}><td>{String(item.id ?? '-')}</td><td>{String(item.nombre ?? '-')}</td><td>{String(item.estado ?? '-')}</td></tr>)}</tbody></table></div></div></section>
}
