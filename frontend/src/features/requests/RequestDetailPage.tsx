import { Link, useParams } from 'react-router-dom'
import { useQuery } from '@tanstack/react-query'
import { getGridRows, GridRow } from '../../api'
import AttachmentPanel from '../files/AttachmentPanel'
import { useAuth } from '../../auth'

export default function RequestDetailPage() {
  const { table = '', id = '0' } = useParams(); const { session } = useAuth(); const record = useQuery({ queryKey: ['request-detail', table, id], queryFn: () => getGridRows(table, '', { id: Number(id) }) }); const followups = useQuery({ queryKey: ['request-followups', table, id], queryFn: () => getGridRows(`${table}_seguimientos`, '', { id_maestro: Number(id) }), enabled: Boolean(table && id) })
  const row = record.data?.[0]
  const uuid = localStorage.getItem('ikaros.uuid') || ''; return <section><div className="topbar"><div><Link className="back-link" to={`/${table === 'servicios' ? 'servicios' : table === 'incidencias' ? 'incidencias' : 'problemas'}`}>Volver</Link><h1>Solicitud #{id}</h1><p className="muted">Detalle y trazabilidad.</p></div></div><div className="panel detail-panel">{record.isLoading && <p className="muted">Cargando solicitud...</p>}{record.isError && <div className="error">No fue posible cargar la solicitud.</div>}{row && <div className="detail-grid">{Object.entries(row).map(([key, value]) => <div className="detail-item" key={key}><span>{key}</span><strong>{String(value ?? '-')}</strong></div>)}</div>}<Link className="secondary" to={`/rechazoSolucion/${id}/${table}/${session?.userData?.id || 0}/${uuid}`}>Rechazar solución</Link></div><div className="panel"><h2>Trazabilidad</h2>{followups.isLoading && <p className="muted">Cargando seguimiento...</p>}{followups.data?.map((item: GridRow, index) => <article className="timeline-item" key={String(item.id ?? index)}><strong>{String(item.estado ?? 'Evento')}</strong><p>{String(item.observacion ?? '-')}</p><span className="muted">{String(item.fecha ?? '')}</span></article>)}</div><AttachmentPanel table={table} id={Number(id)} /></section>
}
