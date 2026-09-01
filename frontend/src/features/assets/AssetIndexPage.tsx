import { Link } from 'react-router-dom'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { assetApi } from '../../api'
import { ModuleDefinition } from '../modules/module-config'
import { useState } from 'react'

export default function AssetIndexPage({ definition }: { definition: ModuleDefinition }) {
  const [search, setSearch] = useState('')
  const [page, setPage] = useState(1)
  const [sort, setSort] = useState<string[]>(['-id'])
  const [message, setMessage] = useState('')
  const queryClient = useQueryClient()
  const assets = useQuery({ queryKey: ['v1-assets', page, search, sort], queryFn: () => assetApi.list({ page, per_page: 25, search, sort: sort.join(',') }) })
  const generate = useMutation({ mutationFn: assetApi.generateCode, onSuccess: (data) => { setMessage(data.detail ? `Código generado: ${data.detail}` : 'El activo ya tiene código.'); queryClient.invalidateQueries({ queryKey: ['v1-assets'] }) }, onError: () => setMessage('No fue posible generar el código.') })

  function updateSearch(value: string) { setSearch(value); setPage(1) }
  function toggleSort(column: string) {
    setPage(1)
    setSort((current) => {
      const ascending = current.includes(column)
      const descending = current.includes(`-${column}`)
      if (!ascending && !descending) return [...current, column]
      if (ascending) return current.map((item) => item === column ? `-${column}` : item)
      return current.filter((item) => item !== `-${column}`)
    })
  }
  function sortLabel(column: string) { return sort.includes(column) ? ' ascendente' : sort.includes(`-${column}`) ? ' descendente' : '' }

  const response = assets.data
  return <section><div className="topbar"><div><h1>{definition.title}</h1><p className="muted">{definition.description}</p></div><input className="search" placeholder="Buscar por código, nombre o marca" value={search} onChange={(event) => updateSearch(event.target.value)} /></div><div className="panel">{message && <p className="muted" role="status">{message}</p>}{assets.isLoading && <p className="muted">Cargando activos...</p>}{assets.isError && <div className="error">No fue posible cargar los activos.</div>}{response && <><div className="table-wrap"><table><thead><tr>{['codigo', 'nombre', 'marca'].map((column) => <th key={column}><button className="table-sort" type="button" onClick={() => toggleSort(column)} aria-label={`Ordenar por ${column}${sortLabel(column)}`}>{column}<span aria-hidden="true">{sort.includes(column) ? ' ↑' : sort.includes(`-${column}`) ? ' ↓' : ''}</span></button></th>)}<th>Acción</th></tr></thead><tbody>{response.data.map((asset) => <tr key={asset.id}><td>{asset.codigo || '-'}</td><td>{asset.nombre || '-'}</td><td>{asset.marca || '-'}</td><td className="asset-actions"><Link className="secondary" to={`/activos/${asset.id}`}>Abrir</Link><button className="secondary" type="button" onClick={() => generate.mutate(asset.id)} disabled={generate.isPending}>Generar código</button></td></tr>)}</tbody></table>{response.data.length === 0 && <p className="muted">No hay activos para mostrar.</p>}</div><div className="table-pagination"><span>{response.meta.total} activos</span><div><button className="secondary" type="button" onClick={() => setPage((current) => current - 1)} disabled={page === 1}>Anterior</button><span>Página {response.meta.current_page} de {response.meta.last_page}</span><button className="secondary" type="button" onClick={() => setPage((current) => current + 1)} disabled={page === response.meta.last_page}>Siguiente</button></div></div></>}</div></section>
}
