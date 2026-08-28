import { useState } from 'react'
import { useMutation, useQuery } from '@tanstack/react-query'
import { getGridRows, GridRow, notificationApi } from '../../api'
import { useAuth } from '../../auth'

export default function CommitteesPage() {
  const { session } = useAuth(); const [message, setMessage] = useState(''); const committees = useQuery({ queryKey: ['initiative-committees'], queryFn: () => getGridRows('iniciativas_comites', '', {}, ['nombre']) }); const notify = useMutation({ mutationFn: (id: number) => notificationApi.committee(id, session?.userData?.id || 0), onSuccess: () => setMessage('Solicitud enviada a los aprobadores.'), onError: () => setMessage('No fue posible notificar al comité.') })
  return <section><div className="topbar"><div><h1>Comités de iniciativas</h1><p className="muted">Gestiona solicitudes de aprobación.</p></div></div><div className="panel">{message && <p className="muted" role="status">{message}</p>}<div className="table-wrap"><table><thead><tr><th>ID</th><th>Iniciativa</th><th>Nombre</th><th>Estado</th><th>Acción</th></tr></thead><tbody>{committees.data?.map((committee: GridRow, index) => <tr key={String(committee.id ?? index)}><td>{String(committee.id ?? '-')}</td><td>{String(committee.id_iniciativa ?? '-')}</td><td>{String(committee.nombre ?? '-')}</td><td>{String(committee.estado_validacion ?? '-')}</td><td><button className="secondary" onClick={() => committee.id && notify.mutate(committee.id)} disabled={notify.isPending}>Notificar aprobadores</button></td></tr>)}</tbody></table></div></div></section>
}
