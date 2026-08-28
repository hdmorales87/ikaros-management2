import { useQueries } from '@tanstack/react-query'
import { getGridTotal } from '../../api'
import { useAuth } from '../../auth'

const cards = [
  { table: 'incidencias', label: 'Incidencias', filter: {}, fields: ['asunto', 'descripcion'], tone: 'blue' },
  { table: 'incidencias', label: 'Problemas', filter: { problema: true }, fields: ['asunto', 'descripcion'], tone: 'amber' },
  { table: 'servicios', label: 'Servicios', filter: {}, fields: ['asunto', 'descripcion'], tone: 'teal' },
  { table: 'activos', label: 'Activos', filter: {}, fields: ['nombre', 'codigo', 'marca'], tone: 'green' },
  { table: 'proyectos', label: 'Proyectos', filter: {}, fields: ['nombre', 'codigo'], tone: 'violet' },
] as const

export default function DashboardPage() {
  const { session } = useAuth()
  const results = useQueries({ queries: cards.map((card) => ({ queryKey: ['dashboard', card.table, card.filter], queryFn: () => getGridTotal(card.table, card.filter) })) })
  return <section><div className="topbar"><div><h1>Resumen operativo</h1><p className="muted">{session?.companyData?.razon_social || 'Tu organización'}</p></div></div><div className="dashboard-grid">{cards.map((card, index) => <article className={`metric metric-${card.tone}`} key={card.label}><span className="metric-label">{card.label}</span><strong>{results[index].isLoading ? '...' : results[index].data ?? 0}</strong><span className="muted">registros activos</span></article>)}</div><div className="panel dashboard-note"><h2>Centro de trabajo</h2><p className="muted">Usa la navegación para consultar y gestionar la información de cada módulo.</p></div></section>
}
