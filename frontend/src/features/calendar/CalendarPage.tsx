import { useMemo, useState } from 'react'
import { useQueries } from '@tanstack/react-query'
import { CalendarDays } from 'lucide-react'
import { getGridRows, GridRow } from '../../api'

type CalendarItem = { date: string; title: string; kind: string; tone: string }

function toDate(value: unknown): string {
  return String(value ?? '').slice(0, 10)
}

export default function CalendarPage() {
  const [month, setMonth] = useState(() => new Date().toISOString().slice(0, 7))
  const results = useQueries({ queries: [
    { queryKey: ['calendar-activities'], queryFn: () => getGridRows('proyectos_actividades', '', {}, ['nombre', 'fecha_inicio', 'fecha_final']) },
    { queryKey: ['calendar-trainings'], queryFn: () => getGridRows('capacitaciones', '', {}, ['nombre', 'fecha_inicio', 'fecha_final']) },
  ] })
  const items = useMemo<CalendarItem[]>(() => [
    ...(results[0].data ?? []).flatMap((item: GridRow) => [{ date: toDate(item.fecha_inicio), title: String(item.nombre ?? 'Actividad de proyecto'), kind: 'Inicio de actividad', tone: 'blue' }, { date: toDate(item.fecha_final), title: String(item.nombre ?? 'Actividad de proyecto'), kind: 'Fin de actividad', tone: 'violet' }]),
    ...(results[1].data ?? []).flatMap((item: GridRow) => [{ date: toDate(item.fecha_inicio), title: String(item.nombre ?? 'Capacitación'), kind: 'Inicio de capacitación', tone: 'teal' }, { date: toDate(item.fecha_final), title: String(item.nombre ?? 'Capacitación'), kind: 'Fin de capacitación', tone: 'amber' }]),
  ].filter((item) => item.date.startsWith(month)).sort((first, second) => first.date.localeCompare(second.date)), [month, results])

  return <section className="calendar-page"><div className="topbar"><div><h1>Calendario</h1><p className="muted">Fechas de proyectos y capacitaciones de la organización.</p></div><label className="calendar-month">Mes<input type="month" value={month} onChange={(event) => setMonth(event.target.value)} /></label></div><div className="calendar-list panel">{results.some((result) => result.isLoading) && <p className="muted">Cargando agenda...</p>}{!results.some((result) => result.isLoading) && items.length === 0 && <p className="muted">No hay eventos programados para este mes.</p>}{items.map((item, index) => <article className="calendar-item" key={`${item.date}-${item.title}-${index}`}><time dateTime={item.date}>{new Date(`${item.date}T12:00:00`).toLocaleDateString('es-CO', { day: '2-digit', month: 'short' })}</time><span className={`calendar-marker calendar-marker-${item.tone}`}><CalendarDays size={17} aria-hidden="true" /></span><div><strong>{item.title}</strong><p>{item.kind}</p></div></article>)}</div></section>
}