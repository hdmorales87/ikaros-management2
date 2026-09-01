import { useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { ArrowLeft } from 'lucide-react'
import { Link, Navigate, useParams } from 'react-router-dom'
import { trainingApi, TrainingAttendee, userApi } from '../../api'

export default function TrainingAttendeesPage() {
  const { id = '' } = useParams()
  const trainingId = Number(id)
  const queryClient = useQueryClient()
  const [userId, setUserId] = useState(0)
  const [message, setMessage] = useState('')
  const training = useQuery({ queryKey: ['v1-training', trainingId], queryFn: () => trainingApi.find(trainingId), enabled: trainingId > 0 })
  const users = useQuery({ queryKey: ['training-users'], queryFn: userApi.list })
  const attendees = useQuery({ queryKey: ['v1-training-attendees', trainingId], queryFn: () => trainingApi.attendees(trainingId), enabled: trainingId > 0 })
  const refresh = () => queryClient.invalidateQueries({ queryKey: ['v1-training-attendees', trainingId] })
  const add = useMutation({ mutationFn: () => trainingApi.addAttendee(trainingId, userId), onSuccess: () => { setUserId(0); setMessage('Asistente inscrito.'); refresh() }, onError: () => setMessage('No fue posible inscribir al asistente.') })
  const attendance = useMutation({ mutationFn: ({ attendeeId, attended }: { attendeeId: number; attended: boolean }) => trainingApi.updateAttendance(trainingId, attendeeId, !attended), onSuccess: () => { setMessage('Asistencia actualizada.'); refresh() } })
  const remove = useMutation({ mutationFn: (attendeeId: number) => trainingApi.removeAttendee(trainingId, attendeeId), onSuccess: () => { setMessage('Asistente retirado.'); refresh() }, onError: () => setMessage('No fue posible retirar al asistente.') })

  if (!Number.isInteger(trainingId) || trainingId < 1) return <Navigate to="/capacitaciones" replace />

  return <section><div className="context-back"><Link to="/capacitaciones"><ArrowLeft size={17} aria-hidden="true" />Volver a Capacitaciones</Link></div><div className="topbar"><div><h1>Asistentes</h1><p className="muted">{training.data?.nombre || 'Gestiona los usuarios inscritos en esta capacitación.'}</p></div></div><div className="panel"><label className="field">Usuario<select value={userId} onChange={(event) => setUserId(Number(event.target.value))}><option value="0">Selecciona un usuario</option>{users.data?.map((item) => <option value={item.id} key={item.id}>{item.nombre} {item.apellido}</option>)}</select></label><button className="primary" type="button" onClick={() => add.mutate()} disabled={!userId || add.isPending}>Inscribir asistente</button>{message && <p className="muted" role="status">{message}</p>}</div><div className="panel"><h2>Inscritos</h2><div className="table-wrap"><table><thead><tr><th>Usuario</th><th>Asistencia</th><th>Acciones</th></tr></thead><tbody>{attendees.data?.map((item: TrainingAttendee) => { const attended = String(item.asistencia) === 'true' || String(item.asistencia) === '1'; const user = users.data?.find((candidate) => candidate.id === item.id_usuario); return <tr key={item.id}><td>{user ? `${user.nombre} ${user.apellido}` : String(item.id_usuario)}</td><td>{attended ? 'Confirmada' : 'Pendiente'}</td><td><button className="secondary" type="button" onClick={() => attendance.mutate({ attendeeId: item.id, attended })} disabled={attendance.isPending}>{attended ? 'Marcar pendiente' : 'Confirmar'}</button><button className="secondary" type="button" onClick={() => remove.mutate(item.id)} disabled={remove.isPending}>Retirar</button></td></tr>})}</tbody></table></div></div></section>
}
