import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { getGridRows, GridRow } from '../../api'

function RelatedTable({ title, table, filter, columns }: { title: string; table: string; filter: Record<string, unknown>; columns: string[] }) {
  const rows = useQuery({ queryKey: ['project-related', table, filter], queryFn: () => getGridRows(table, '', filter, columns.filter((column) => column !== 'id')) })
  return <div className="related-section"><h3>{title}</h3>{rows.isLoading && <p className="muted">Cargando...</p>}<div className="table-wrap"><table><thead><tr>{columns.map((column) => <th key={column}>{column}</th>)}</tr></thead><tbody>{rows.data?.map((row: GridRow, index) => <tr key={String(row.id ?? index)}>{columns.map((column) => <td key={column}>{String(row[column] ?? '-')}</td>)}</tr>)}</tbody></table></div></div>
}

export default function ProjectsPage() {
  const [search, setSearch] = useState(''); const [projectId, setProjectId] = useState(0); const projects = useQuery({ queryKey: ['projects', search], queryFn: () => getGridRows('proyectos', search, {}, ['codigo', 'nombre']) })
  return <section><div className="topbar"><div><h1>Proyectos</h1><p className="muted">Consulta proyectos, actividades y riesgos.</p></div><input className="search" placeholder="Buscar proyecto" value={search} onChange={(event) => setSearch(event.target.value)} /></div><div className="panel"><div className="table-wrap"><table><thead><tr><th>Código</th><th>Nombre</th><th>Estado</th><th>Acción</th></tr></thead><tbody>{projects.data?.map((project: GridRow, index) => <tr key={String(project.id ?? index)}><td>{String(project.codigo ?? '-')}</td><td>{String(project.nombre ?? '-')}</td><td>{String(project.estado ?? '-')}</td><td><button className="secondary" onClick={() => setProjectId(Number(project.id))}>Ver detalle</button></td></tr>)}</tbody></table></div></div>{projectId > 0 && <div className="panel project-detail"><h2>Detalle del proyecto #{projectId}</h2><RelatedTable title="Actividades" table="proyectos_actividades" filter={{ id_proyecto: projectId }} columns={['id', 'nombre', 'fecha_inicio', 'fecha_final']} /><RelatedTable title="Riesgos" table="proyectos_riesgos" filter={{ id_proyecto: projectId }} columns={['id', 'nombre', 'descripcion', 'mitigacion']} /></div>}</section>
}
