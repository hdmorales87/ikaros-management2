import { Link, useParams } from 'react-router-dom'
import { useQuery } from '@tanstack/react-query'
import { requestApi } from '../../api'
import AttachmentPanel from '../files/AttachmentPanel'
import { useAuth } from '../../auth'

export default function RequestDetailPage() {
  const { table = '', id = '0' } = useParams(); const { session } = useAuth(); const isService = table === 'servicios'; const record = useQuery({ queryKey: ['v1-request-detail', table, id], queryFn: () => isService ? requestApi.findService(Number(id)) : requestApi.findIncident(Number(id)), enabled: Boolean(table && id) }); const followups = useQuery({ queryKey: ['v1-request-followups', table, id], queryFn: () => isService ? requestApi.serviceFollowups(Number(id)) : requestApi.incidentFollowups(Number(id)), enabled: Boolean(table && id) })
  const row = record.data
  const uuid = localStorage.getItem('ikaros.uuid') || ''; return <section><div className="topbar"><div><Link className="back-link" to={`/${table === 'servicios' ? 'servicios' : table === 'incidencias' ? 'incidencias' : 'problemas'}`}>Volver</Link><h1>Solicitud #{id}</h1><p className="muted">Detalle y trazabilidad.</p></div></div><div className="panel detail-panel">{record.isLoading && <p className="muted">Cargando solicitud...</p>}{record.isError && <div className="error">No fue posible cargar la solicitud.</div>}{row && <div className="detail-grid">{Object.entries(row).map(([key, value]) => <div className="detail-item" key={key}><span>{key}</span><strong>{String(value ?? '-')}</strong></div>)}</div>}<Link className="secondary" to={`/rechazoSolucion/${id}/${table}/${session?.userData?.id || 0}/${uuid}`}>Rechazar solución</Link></div><div className="panel"><h2>Trazabilidad</h2>{followups.isLoading && <p className="muted">Cargando seguimiento...</p>}{followups.data?.map((item, index) => <article className="timeline-item" key={String(item.id ?? index)}><strong>{item.estado || 'Evento'}</strong><p>{item.observacion || '-'}</p><span className="muted">{item.fecha || ''}</span></article>)}</div><AttachmentPanel table={table} id={Number(id)} /></section>
}
