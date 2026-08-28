import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { getGridRows, GridRow, knowledgeApi } from '../../api'

export default function KnowledgePage() {
  const [search, setSearch] = useState(''); const [selectedId, setSelectedId] = useState(0); const rows = useQuery({ queryKey: ['knowledge', search], queryFn: () => getGridRows('conocimiento', search, {}, ['tema']) }); const detail = useQuery({ queryKey: ['knowledge-detail', selectedId], queryFn: () => knowledgeApi.find(selectedId), enabled: selectedId > 0 })
  return <section><div className="topbar"><div><h1>Conocimiento</h1><p className="muted">Soluciones disponibles para consulta.</p></div><input className="search" placeholder="Buscar por tema" value={search} onChange={(event) => setSearch(event.target.value)} /></div><div className="panel"><div className="table-wrap"><table><thead><tr><th>Tema</th><th>Acción</th></tr></thead><tbody>{rows.data?.map((row: GridRow, index) => <tr key={String(row.id ?? index)}><td>{String(row.tema ?? '-')}</td><td><button className="secondary" onClick={() => row.id && setSelectedId(row.id)}>Ver solución</button></td></tr>)}</tbody></table></div></div>{detail.data && <article className="panel knowledge-detail"><h2>{detail.data.tema}</h2><p>{detail.data.solucion}</p></article>}</section>
}