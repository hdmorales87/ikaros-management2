import { FormEvent, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import AttachmentPanel from '../files/AttachmentPanel'
import { GridRow, projectApi, Project } from '../../api'
import TechnicalSheetPanel from '../technical-sheets/TechnicalSheetPanel'

type ActivityForm = { nombre: string; descripcion: string; fechaInicio: string; fechaFinal: string; horaInicio: string; horaFinal: string }
const emptyActivity: ActivityForm = { nombre: '', descripcion: '', fechaInicio: '', fechaFinal: '', horaInicio: '08:00', horaFinal: '17:00' }

type RelatedRow = GridRow

function RelatedTable({ title, resource, projectId, columns }: { title: string; resource: 'activities' | 'risks'; projectId: number; columns: string[] }) {
  const rows = useQuery({
    queryKey: ['v1-project-related', resource, projectId],
    queryFn: () => resource === 'activities' ? projectApi.activities(projectId) : projectApi.risks(projectId),
  })

  return <div className="related-section">
    <h3>{title}</h3>
    <div className="table-wrap"><table><thead><tr>{columns.map((column) => <th key={column}>{column}</th>)}</tr></thead><tbody>
      {rows.data?.map((row: RelatedRow) => <tr key={row.id}>{columns.map((column) => <td key={column}>{String(row[column] ?? '-')}</td>)}</tr>)}
    </tbody></table></div>
  </div>
}

export default function ProjectsPage() {
  const queryClient = useQueryClient()
  const [search, setSearch] = useState('')
  const [page, setPage] = useState(1)
  const [sort, setSort] = useState('-id')
  const [project, setProject] = useState<Project | null>(null)
  const [name, setName] = useState('')
  const [startDate, setStartDate] = useState('')
  const [endDate, setEndDate] = useState('')
  const [activity, setActivity] = useState<ActivityForm>(emptyActivity)
  const [risk, setRisk] = useState('')
  const [message, setMessage] = useState('')
  const projects = useQuery({ queryKey: ['v1-projects', search, page, sort], queryFn: () => projectApi.list({ page, per_page: 25, search, sort }) })

  const saveProject = useMutation({
    mutationFn: () => projectApi.update(Number(project?.id), { nombre: name, fecha_inicio: startDate, fecha_final: endDate }),
    onSuccess: () => {
      setMessage('Proyecto actualizado.')
      queryClient.invalidateQueries({ queryKey: ['v1-projects'] })
    },
    onError: () => setMessage('No fue posible actualizar el proyecto.'),
  })
  const createActivity = useMutation({
    mutationFn: () => projectApi.createActivity(Number(project?.id), { nombre: activity.nombre, descripcion: activity.descripcion, fecha_inicio: activity.fechaInicio, hora_inicio: activity.horaInicio, fecha_final: activity.fechaFinal, hora_final: activity.horaFinal }),
    onSuccess: () => {
      setActivity(emptyActivity)
      setMessage('Actividad creada.')
      queryClient.invalidateQueries({ queryKey: ['v1-project-related', 'activities', Number(project?.id)] })
    },
    onError: () => setMessage('No fue posible crear la actividad. Verifica el periodo del proyecto.'),
  })
  const createRisk = useMutation({
    mutationFn: () => projectApi.createRisk(Number(project?.id), risk),
    onSuccess: () => {
      setRisk('')
      setMessage('Riesgo creado.')
      queryClient.invalidateQueries({ queryKey: ['v1-project-related', 'risks', Number(project?.id)] })
    },
    onError: () => setMessage('No fue posible crear el riesgo.'),
  })

  function selectProject(item: Project) {
    setProject(item)
    setName(item.nombre || '')
    setStartDate(item.fecha_inicio?.slice(0, 10) || '')
    setEndDate(item.fecha_final?.slice(0, 10) || '')
    setActivity(emptyActivity)
    setMessage('')
  }

  function saveDates(event: FormEvent) {
    event.preventDefault()
    if (endDate < startDate) return setMessage('La fecha final debe ser posterior a la inicial.')
    saveProject.mutate()
  }

  function saveActivity(event: FormEvent) {
    event.preventDefault()
    if (!project || activity.fechaInicio < startDate || activity.fechaFinal > endDate || activity.fechaFinal < activity.fechaInicio || (activity.fechaFinal === activity.fechaInicio && activity.horaFinal < activity.horaInicio)) {
      return setMessage('La actividad debe estar dentro del periodo del proyecto.')
    }
    createActivity.mutate()
  }

  function toggleSort(column: string) {
    setSort((current) => current === column ? `-${column}` : column)
    setPage(1)
  }

  return <section>
    <div className="topbar"><div><h1>Proyectos</h1><p className="muted">Gestiona cronogramas, actividades y riesgos.</p></div><input className="search" placeholder="Buscar proyecto" value={search} onChange={(event) => { setSearch(event.target.value); setPage(1) }} /></div>
    <div className="panel">
      {projects.isLoading && <p className="muted">Cargando proyectos...</p>}
      {projects.isError && <div className="error">No fue posible cargar los proyectos.</div>}
      {projects.data && <><div className="table-wrap"><table><thead><tr>{[['codigo', 'Código'], ['nombre', 'Nombre'], ['estado', 'Estado']].map(([column, label]) => <th key={column}><button className="table-sort" type="button" onClick={() => toggleSort(column)}>{label}<span aria-hidden="true">{sort === column ? ' ↑' : sort === `-${column}` ? ' ↓' : ''}</span></button></th>)}<th>Acción</th></tr></thead><tbody>
        {projects.data.data.map((item) => <tr key={item.id}><td>{item.codigo || '-'}</td><td>{item.nombre || '-'}</td><td>{String(item.estado ?? '-')}</td><td><button className="secondary" type="button" onClick={() => selectProject(item)}>Abrir</button></td></tr>)}
      </tbody></table></div><div className="table-pagination"><span>{projects.data.meta.total} proyectos</span><div><button className="secondary" type="button" onClick={() => setPage((current) => current - 1)} disabled={page === 1}>Anterior</button><span>Página {projects.data.meta.current_page} de {projects.data.meta.last_page}</span><button className="secondary" type="button" onClick={() => setPage((current) => current + 1)} disabled={page === projects.data.meta.last_page}>Siguiente</button></div></div></>}
    </div>
    {project && <div className="panel project-detail">
      <h2>Proyecto #{project.id}</h2>
      {message && <p className="muted" role="status">{message}</p>}
      <form onSubmit={saveDates}><div className="form-grid"><label className="field">Nombre<input value={name} onChange={(event) => setName(event.target.value)} required /></label><label className="field">Inicio<input type="date" value={startDate} onChange={(event) => setStartDate(event.target.value)} required /></label><label className="field">Fin<input type="date" min={startDate} value={endDate} onChange={(event) => setEndDate(event.target.value)} required /></label></div><button className="primary" disabled={saveProject.isPending}>Guardar proyecto</button></form>
      <form onSubmit={saveActivity}><h3>Nueva actividad</h3><div className="form-grid"><label className="field">Nombre<input value={activity.nombre} onChange={(event) => setActivity((current) => ({ ...current, nombre: event.target.value }))} required /></label><label className="field">Descripción<textarea value={activity.descripcion} onChange={(event) => setActivity((current) => ({ ...current, descripcion: event.target.value }))} required /></label><label className="field">Inicio<input type="date" min={startDate} max={endDate} value={activity.fechaInicio} onChange={(event) => setActivity((current) => ({ ...current, fechaInicio: event.target.value }))} required /></label><label className="field">Hora inicio<input type="time" value={activity.horaInicio} onChange={(event) => setActivity((current) => ({ ...current, horaInicio: event.target.value }))} required /></label><label className="field">Fin<input type="date" min={startDate} max={endDate} value={activity.fechaFinal} onChange={(event) => setActivity((current) => ({ ...current, fechaFinal: event.target.value }))} required /></label><label className="field">Hora fin<input type="time" value={activity.horaFinal} onChange={(event) => setActivity((current) => ({ ...current, horaFinal: event.target.value }))} required /></label></div><button className="primary" disabled={createActivity.isPending}>Crear actividad</button></form>
      <form onSubmit={(event) => { event.preventDefault(); if (risk.trim()) createRisk.mutate() }}><h3>Nuevo riesgo</h3><label className="field">Riesgo<input value={risk} onChange={(event) => setRisk(event.target.value)} required /></label><button className="primary" disabled={createRisk.isPending}>Crear riesgo</button></form>
      <RelatedTable title="Actividades" resource="activities" projectId={project.id} columns={['nombre', 'fecha_inicio', 'fecha_final', 'estado']} />
      <RelatedTable title="Riesgos" resource="risks" projectId={project.id} columns={['nombre', 'descripcion', 'mitigacion', 'estado']} />
    </div>}
    {project && <><TechnicalSheetPanel tabla="proyectos" idMaestro={project.id} /><AttachmentPanel table="proyectos" id={project.id} /></>}
  </section>
}
