import { useMemo, useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { getGridRows, GridRow } from '../../api'

type Activity = GridRow & { fecha_inicio?: string; fecha_final?: string }

function day(value: unknown) { return value ? new Date(String(value)) : null }
function format(value: unknown) { const date = day(value); return date && !Number.isNaN(date.valueOf()) ? date.toLocaleDateString('es-CO') : 'Sin fecha' }

export default function GanttPage() {
  const [projectId, setProjectId] = useState(0)
  const projects = useQuery({ queryKey: ['gantt-projects'], queryFn: () => getGridRows('proyectos', '', {}, ['codigo', 'nombre']) })
  const activities = useQuery({ queryKey: ['gantt-activities', projectId], queryFn: () => getGridRows('proyectos_actividades', '', { id_proyecto: projectId }, ['nombre']), enabled: projectId > 0 })
  const range = useMemo(() => { const dates = (activities.data || []).flatMap((item) => [day(item.fecha_inicio), day(item.fecha_final)]).filter((date): date is Date => Boolean(date && !Number.isNaN(date.valueOf()))); if (!dates.length) return null; const min = new Date(Math.min(...dates.map((date) => date.valueOf()))); const max = new Date(Math.max(...dates.map((date) => date.valueOf()))); const total = Math.max(1, Math.ceil((max.valueOf() - min.valueOf()) / 86400000)); return { min, total } }, [activities.data])
  function position(item: Activity) { if (!range) return { left: '0%', width: '100%' }; const start = day(item.fecha_inicio) || range.min; const end = day(item.fecha_final) || start; const left = Math.max(0, (start.valueOf() - range.min.valueOf()) / 86400000); const width = Math.max(1, (end.valueOf() - start.valueOf()) / 86400000); return { left: `${Math.min(100, left / range.total * 100)}%`, width: `${Math.min(100, width / range.total * 100)}%` } }
  return <section><div className="topbar"><div><h1>Gantt de proyectos</h1><p className="muted">Visualiza la planificación de actividades.</p></div></div><div className="panel"><label className="field">Proyecto<select value={projectId} onChange={(event) => setProjectId(Number(event.target.value))}><option value="0">Selecciona un proyecto</option>{projects.data?.map((project) => <option value={Number(project.id)} key={String(project.id)}>{String(project.codigo || '')} {String(project.nombre || '')}</option>)}</select></label></div>{projectId > 0 && <div className="panel gantt"><div className="gantt-header">{range && <><span>{format(range.min)}</span><span>Periodo: {range.total} días</span></>}</div>{activities.isLoading && <p className="muted">Cargando actividades...</p>}{activities.data?.map((item, index) => <div className="gantt-row" key={String(item.id ?? index)}><div className="gantt-label">{String(item.nombre ?? `Actividad ${index + 1}`)}<small>{format(item.fecha_inicio)} - {format(item.fecha_final)}</small></div><div className="gantt-track"><span className="gantt-bar" style={position(item as Activity)} /></div></div>)}</div>}</section>
}
