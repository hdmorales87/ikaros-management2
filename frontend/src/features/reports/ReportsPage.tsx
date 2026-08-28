import { useQuery } from '@tanstack/react-query'
import { getGridRows, GridRow } from '../../api'

type ReportRow = GridRow & { tipo?: string; asunto?: string; estado?: number; prioridad?: number; fecha?: string }

function exportCsv(rows: ReportRow[]) {
  const header = ['Tipo', 'ID', 'Asunto', 'Estado', 'Prioridad', 'Fecha']
  const escape = (value: unknown) => `"${String(value ?? '').replaceAll('"', '""')}"`
  const body = rows.map((row) => [row.tipo, row.id, row.asunto, row.estado, row.prioridad, row.fecha].map(escape).join(','))
  const url = URL.createObjectURL(new Blob([[header.join(','), ...body].join('\n')], { type: 'text/csv;charset=utf-8' }))
  const link = document.createElement('a'); link.href = url; link.download = 'reporte-solicitudes.csv'; link.click(); URL.revokeObjectURL(url)
}

export default function ReportsPage() {
  const incidents = useQuery({ queryKey: ['report-incidents'], queryFn: () => getGridRows('incidencias', '', {}, ['asunto'], ['id', 'asunto', 'estado', 'prioridad', 'fecha', 'problema']) })
  const services = useQuery({ queryKey: ['report-services'], queryFn: () => getGridRows('servicios', '', {}, ['asunto'], ['id', 'asunto', 'estado', 'prioridad', 'fecha']) })
  const rows: ReportRow[] = [...(incidents.data ?? []).map((row) => ({ ...row, tipo: String(row.problema) === '1' || String(row.problema) === 'true' ? 'Problema' : 'Incidencia' })), ...(services.data ?? []).map((row) => ({ ...row, tipo: 'Servicio' }))]
  const open = rows.filter((row) => Number(row.estado) < 3).length
  const solved = rows.filter((row) => Number(row.estado) === 3).length
  const critical = rows.filter((row) => Number(row.prioridad) >= 4).length
  return <section><div className="topbar"><div><h1>Reportes operativos</h1><p className="muted">Resumen de solicitudes activas y resueltas.</p></div><button className="secondary" onClick={() => exportCsv(rows)} disabled={!rows.length}>Exportar CSV</button></div><div className="form-grid"><div className="panel"><h2>{rows.length}</h2><p className="muted">Solicitudes registradas</p></div><div className="panel"><h2>{open}</h2><p className="muted">En gestión</p></div><div className="panel"><h2>{solved}</h2><p className="muted">Solucionadas</p></div><div className="panel"><h2>{critical}</h2><p className="muted">Prioridad alta o crítica</p></div></div><div className="panel"><h2>Detalle</h2>{(incidents.isLoading || services.isLoading) && <p className="muted">Cargando reporte...</p>}<div className="table-wrap"><table><thead><tr><th>Tipo</th><th>ID</th><th>Asunto</th><th>Estado</th><th>Prioridad</th><th>Fecha</th></tr></thead><tbody>{rows.map((row, index) => <tr key={`${row.tipo}-${String(row.id ?? index)}`}><td>{String(row.tipo ?? '-')}</td><td>{String(row.id ?? '-')}</td><td>{String(row.asunto ?? '-')}</td><td>{String(row.estado ?? '-')}</td><td>{String(row.prioridad ?? '-')}</td><td>{String(row.fecha ?? '-')}</td></tr>)}</tbody></table></div></div></section>
}