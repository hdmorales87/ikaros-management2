import { useQueries } from '@tanstack/react-query'
import { getGridTotal } from '../../api'
import { useAuth } from '../../auth'

const cards = [
  { table: 'incidencias', label: 'Incidencias', filter: {}, tone: 'blue' },
  { table: 'servicios', label: 'Servicios', filter: {}, tone: 'teal' },
  { table: 'activos', label: 'Activos', filter: {}, tone: 'green' },
  { table: 'proyectos', label: 'Proyectos', filter: {}, tone: 'violet' },
  { table: 'capacitaciones', label: 'Capacitaciones', filter: {}, tone: 'amber' },
] as const

export default function DashboardPage() {
  const { session } = useAuth()
  const results = useQueries({
    queries: cards.map((card) => ({
      queryKey: ['dashboard', card.table, card.filter],
      queryFn: () => getGridTotal(card.table, card.filter),
    })),
  })

  const total = results.reduce((sum, item) => sum + Number(item.data ?? 0), 0)

  return <section>
    <div className="topbar"><div><h1>Resumen operativo</h1><p className="muted">{session?.companyData?.razon_social || 'Tu organización'}</p></div></div>

    <div className="dashboard-grid">
      {cards.map((card, index) => <article className={`metric metric-${card.tone}`} key={card.label}>
        <span className="metric-label">{card.label}</span>
        <strong>{results[index].isLoading ? '...' : results[index].data ?? 0}</strong>
        <span className="muted">registros activos</span>
      </article>)}
    </div>

    <div className="panel dashboard-note">
      <h2>Snapshot de operación</h2>
      <p className="muted">Total de registros disponibles en el sistema: <strong>{total}</strong></p>
      <ul className="muted">
        <li>Incidencias, servicios, activos, proyectos y capacitaciones están visibles desde el panel principal.</li>
        <li>Los módulos de terceros, contratos, comités e iniciativas se mantienen accesibles desde la navegación lateral.</li>
        <li>El panel central sirve como resumen de actividad para coordinar la operación diaria.</li>
      </ul>
    </div>
  </section>
}
