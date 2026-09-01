import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { CalendarDays } from 'lucide-react'
import { calendarApi, CalendarEvent } from '../../api'

type CalendarItem = CalendarEvent

export default function CalendarPage() {
  const [month, setMonth] = useState(() => new Date().toISOString().slice(0, 7))
  const events = useQuery({ queryKey: ['v1-calendar-events', month], queryFn: () => calendarApi.events(month) })
  const items: CalendarItem[] = events.data ?? []

  return <section className="calendar-page"><div className="topbar"><div><h1>Calendario</h1><p className="muted">Fechas de proyectos y capacitaciones de la organización.</p></div><label className="calendar-month">Mes<input type="month" value={month} onChange={(event) => setMonth(event.target.value)} /></label></div><div className="calendar-list panel">{events.isLoading && <p className="muted">Cargando agenda...</p>}{!events.isLoading && items.length === 0 && <p className="muted">No hay eventos programados para este mes.</p>}{items.map((item, index) => <article className="calendar-item" key={`${item.date}-${item.title}-${index}`}><time dateTime={item.date}>{new Date(`${item.date}T12:00:00`).toLocaleDateString('es-CO', { day: '2-digit', month: 'short' })}</time><span className={`calendar-marker calendar-marker-${item.tone}`}><CalendarDays size={17} aria-hidden="true" /></span><div><strong>{item.title}</strong><p>{item.kind}</p></div></article>)}</div></section>
}