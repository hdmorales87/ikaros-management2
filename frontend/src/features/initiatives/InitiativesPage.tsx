import { useState } from 'react'
import { useMutation, useQuery } from '@tanstack/react-query'
import { getGridRows, notificationApi, GridRow } from '../../api'

export default function InitiativesPage() {
  const [message, setMessage] = useState(''); const rows = useQuery({ queryKey: ['initiatives'], queryFn: () => getGridRows('iniciativas', '', {}, ['codigo', 'nombre']) }); const notify = useMutation({ mutationFn: (id: number) => notificationApi.initiative(id), onSuccess: () => setMessage('Notificación de validación enviada.'), onError: () => setMessage('No fue posible enviar la notificación.') })
  return <section><div className="topbar"><div><h1>Iniciativas</h1><p className="muted">Consulta iniciativas y solicita su validación.</p></div></div><div className="panel">{message && <p className="muted" role="status">{message}</p>}<div className="table-wrap"><table><thead><tr><th>Código</th><th>Nombre</th><th>Estado</th><th>Acción</th></tr></thead><tbody>{rows.data?.map((row: GridRow, index) => <tr key={String(row.id ?? index)}><td>{String(row.codigo ?? '-')}</td><td>{String(row.nombre ?? '-')}</td><td>{String(row.estado ?? '-')}</td><td><button className="secondary" onClick={() => row.id && notify.mutate(row.id)} disabled={notify.isPending}>Solicitar validación</button></td></tr>)}</tbody></table></div></div></section>
}
