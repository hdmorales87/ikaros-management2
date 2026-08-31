import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { X } from 'lucide-react'
import { knowledgeApi } from '../../api'

export default function KnowledgePage() {
  const [search, setSearch] = useState('')
  const [selectedId, setSelectedId] = useState<number | null>(null)
  const rows = useQuery({ queryKey: ['knowledge', search], queryFn: () => knowledgeApi.list(search) })
  const detail = useQuery({ queryKey: ['knowledge-detail', selectedId], queryFn: () => knowledgeApi.find(selectedId ?? 0), enabled: selectedId !== null })
  const closeDetail = () => setSelectedId(null)

  return <section><div className="topbar"><div><h1>Conocimiento</h1><p className="muted">Soluciones disponibles para consulta.</p></div><input className="search" placeholder="Buscar por tema" value={search} onChange={(event) => setSearch(event.target.value)} /></div><div className="panel"><div className="table-wrap"><table><thead><tr><th>Tema</th><th>Acción</th></tr></thead><tbody>{rows.data?.map((row) => <tr key={row.id}><td>{row.tema}</td><td><button className="knowledge-action" type="button" onClick={() => setSelectedId(row.id)}>Ver solución</button></td></tr>)}</tbody></table></div>{rows.isLoading && <p className="muted">Cargando conocimiento...</p>}{!rows.isLoading && !rows.isError && rows.data?.length === 0 && <p className="muted">No hay soluciones disponibles para esta búsqueda.</p>}{rows.isError && <p className="error">No fue posible cargar los artículos.</p>}</div>{selectedId !== null && <div className="knowledge-dialog-backdrop" role="presentation" onMouseDown={closeDetail}><section className="knowledge-dialog" role="dialog" aria-modal="true" aria-labelledby="knowledge-dialog-title" onMouseDown={(event) => event.stopPropagation()}><div className="knowledge-dialog-header"><div><p className="eyebrow">Base de conocimiento</p><h2 id="knowledge-dialog-title">{detail.data?.tema || 'Cargando solución'}</h2></div><button className="icon-button" type="button" aria-label="Cerrar solución" onClick={closeDetail}><X size={20} /></button></div>{detail.isLoading && <p className="muted">Cargando solución...</p>}{detail.isError && <p className="error">No fue posible cargar esta solución.</p>}{detail.data && <div className="knowledge-solution">{detail.data.solucion}</div>}</section></div>}</section>
}