import { useQuery } from '@tanstack/react-query'
import { operationalReportApi, OperationalReportRow } from '../../api'

type ReportRow = OperationalReportRow

function exportCsv(rows: ReportRow[]) {
  const header = ['Tipo', 'ID', 'Asunto', 'Estado', 'Prioridad', 'Fecha']
  const escape = (value: unknown) => `"${String(value ?? '').replaceAll('"', '""')}"`
  const body = rows.map((row) => [row.tipo, row.id, row.asunto, row.estado, row.prioridad, row.fecha].map(escape).join(','))
  const url = URL.createObjectURL(new Blob([[header.join(','), ...body].join('\n')], { type: 'text/csv;charset=utf-8' }))
  const link = document.createElement('a'); link.href = url; link.download = 'reporte-solicitudes.csv'; link.click(); URL.revokeObjectURL(url)
}

export default function ReportsPage() {
  const report = useQuery({ queryKey: ['v1-operational-report-requests'], queryFn: operationalReportApi.requests })
  const rows: ReportRow[] = report.data ?? []
  const open = rows.filter((row) => Number(row.estado) < 3).length
  const solved = rows.filter((row) => Number(row.estado) === 3).length
  const critical = rows.filter((row) => Number(row.prioridad) >= 4).length
  return <section><div className="topbar"><div><h1>Reportes operativos</h1><p className="muted">Resumen de solicitudes activas y resueltas.</p></div><button className="secondary" onClick={() => exportCsv(rows)} disabled={!rows.length}>Exportar CSV</button></div><div className="form-grid"><div className="panel"><h2>{rows.length}</h2><p className="muted">Solicitudes registradas</p></div><div className="panel"><h2>{open}</h2><p className="muted">En gestión</p></div><div className="panel"><h2>{solved}</h2><p className="muted">Solucionadas</p></div><div className="panel"><h2>{critical}</h2><p className="muted">Prioridad alta o crítica</p></div></div><div className="panel"><h2>Detalle</h2>{report.isLoading && <p className="muted">Cargando reporte...</p>}<div className="table-wrap"><table><thead><tr><th>Tipo</th><th>ID</th><th>Asunto</th><th>Estado</th><th>Prioridad</th><th>Fecha</th></tr></thead><tbody>{rows.map((row, index) => <tr key={`${row.tipo}-${String(row.id ?? index)}`}><td>{row.tipo}</td><td>{String(row.id)}</td><td>{row.asunto || '-'}</td><td>{String(row.estado ?? '-')}</td><td>{String(row.prioridad ?? '-')}</td><td>{row.fecha || '-'}</td></tr>)}</tbody></table></div></div></section>
}