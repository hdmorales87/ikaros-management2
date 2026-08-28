import { FormEvent, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { getGridRows, gridApi, GridRow } from '../../api'
import AttachmentPanel from '../files/AttachmentPanel'

function RelatedTable({ title, table, filter, columns }: { title: string; table: string; filter: Record<string, unknown>; columns: string[] }) {
  const rows = useQuery({ queryKey: ['project-related', table, filter], queryFn: () => getGridRows(table, '', filter, columns.filter((column) => column !== 'id')) })
  return <div className="related-section"><h3>{title}</h3>{rows.isLoading && <p className="muted">Cargando...</p>}<div className="table-wrap"><table><thead><tr>{columns.map((column) => <th key={column}>{column}</th>)}</tr></thead><tbody>{rows.data?.map((row: GridRow, index) => <tr key={String(row.id ?? index)}>{columns.map((column) => <td key={column}>{String(row[column] ?? '-')}</td>)}</tr>)}</tbody></table></div></div>
}

function projectDate(value: unknown) { return String(value ?? '').slice(0, 10) }

export default function ProjectsPage() {
  const queryClient = useQueryClient()
  const [search, setSearch] = useState('')
  const [project, setProject] = useState<GridRow | null>(null)
  const [activity, setActivity] = useState('')
  const [risk, setRisk] = useState('')
  const [name, setName] = useState('')
  const [startDate, setStartDate] = useState('')
  const [endDate, setEndDate] = useState('')
  const [message, setMessage] = useState('')
  const projects = useQuery({ queryKey: ['projects', search], queryFn: () => getGridRows('proyectos', search, {}, ['codigo', 'nombre']) })
  const saveProject = useMutation({ mutationFn: () => gridApi.update('proyectos', { id: project?.id, nombre: name, fecha_inicio: startDate, fecha_final: endDate }), onSuccess: () => { setMessage('Proyecto actualizado.'); queryClient.invalidateQueries({ queryKey: ['projects'] }) }, onError: () => setMessage('No fue posible actualizar el proyecto.') })
  const createActivity = useMutation({ mutationFn: () => gridApi.insert('proyectos_actividades', { id_proyecto: project?.id, nombre: activity, estado: 0 }), onSuccess: () => { setActivity(''); setMessage('Actividad creada.'); queryClient.invalidateQueries({ queryKey: ['project-related', 'proyectos_actividades'] }) }, onError: () => setMessage('No fue posible crear la actividad.') })
  const createRisk = useMutation({ mutationFn: () => gridApi.insert('proyectos_riesgos', { id_proyecto: project?.id, nombre: risk, estado: 0 }), onSuccess: () => { setRisk(''); setMessage('Riesgo creado.'); queryClient.invalidateQueries({ queryKey: ['project-related', 'proyectos_riesgos'] }) }, onError: () => setMessage('No fue posible crear el riesgo.') })

  function selectProject(value: GridRow) { setProject(value); setName(String(value.nombre ?? '')); setStartDate(projectDate(value.fecha_inicio)); setEndDate(projectDate(value.fecha_final)); setMessage('') }
  function submitProject(event: FormEvent) { event.preventDefault(); if (startDate && endDate && endDate < startDate) { setMessage('La fecha final debe ser posterior a la fecha inicial.'); return } saveProject.mutate() }
  function submitActivity(event: FormEvent) { event.preventDefault(); createActivity.mutate() }
  function submitRisk(event: FormEvent) { event.preventDefault(); createRisk.mutate() }

  return <section><div className="topbar"><div><h1>Proyectos</h1><p className="muted">Gestiona proyectos, actividades y riesgos.</p></div><input className="search" placeholder="Buscar proyecto" value={search} onChange={(event) => setSearch(event.target.value)} /></div><div className="panel"><div className="table-wrap"><table><thead><tr><th>Código</th><th>Nombre</th><th>Estado</th><th>Acción</th></tr></thead><tbody>{projects.data?.map((item: GridRow, index) => <tr key={String(item.id ?? index)}><td>{String(item.codigo ?? '-')}</td><td>{String(item.nombre ?? '-')}</td><td>{String(item.estado ?? '-')}</td><td><button className="secondary" onClick={() => selectProject(item)}>Abrir</button></td></tr>)}</tbody></table></div></div>{project && <div className="panel project-detail"><h2>Proyecto #{String(project.id)}</h2>{message && <p className="muted" role="status">{message}</p>}<form onSubmit={submitProject}><div className="form-grid"><label className="field">Nombre<input value={name} onChange={(event) => setName(event.target.value)} required /></label><label className="field">Inicio<input type="date" value={startDate} onChange={(event) => setStartDate(event.target.value)} /></label><label className="field">Fin<input type="date" min={startDate || undefined} value={endDate} onChange={(event) => setEndDate(event.target.value)} /></label></div><button className="primary" disabled={saveProject.isPending}>Guardar proyecto</button></form><div className="form-grid"><form onSubmit={submitActivity}><label className="field">Nueva actividad<input value={activity} onChange={(event) => setActivity(event.target.value)} required /></label><button className="primary" disabled={createActivity.isPending}>Crear actividad</button></form><form onSubmit={submitRisk}><label className="field">Nuevo riesgo<input value={risk} onChange={(event) => setRisk(event.target.value)} required /></label><button className="primary" disabled={createRisk.isPending}>Crear riesgo</button></form></div><RelatedTable title="Actividades" table="proyectos_actividades" filter={{ id_proyecto: project.id }} columns={['id', 'nombre', 'fecha_inicio', 'fecha_final']} /><RelatedTable title="Riesgos" table="proyectos_riesgos" filter={{ id_proyecto: project.id }} columns={['id', 'nombre', 'descripcion', 'mitigacion']} /><AttachmentPanel table="proyectos" id={Number(project.id)} /></div>}</section>
}
