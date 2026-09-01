import { useState } from 'react'
import { useMutation, useQuery } from '@tanstack/react-query'
import { FileText } from 'lucide-react'
import { Link } from 'react-router-dom'
import { notificationApi, thirdPartyApi } from '../../api'

export default function ThirdPartiesPage({ kind }: { kind: 'cliente' | 'proveedor' }) {
  const [search, setSearch] = useState('')
  const [page, setPage] = useState(1)
  const [sort, setSort] = useState('razon_social')
  const [message, setMessage] = useState('')
  const isClient = kind === 'cliente'
  const title = isClient ? 'Clientes' : 'Proveedores'
  const scoreColumn = isClient ? 'puntaje_cliente' : 'puntaje_proveedor'
  const rows = useQuery({
    queryKey: ['v1-third-parties', kind, search, page, sort],
    queryFn: () => (isClient ? thirdPartyApi.listClients : thirdPartyApi.listProviders)({ page, per_page: 25, search, sort }),
  })
  const invite = useMutation({
    mutationFn: (id: number) => notificationApi.clientSurvey(id),
    onSuccess: () => setMessage('Invitación enviada correctamente.'),
    onError: () => setMessage('No fue posible enviar la invitación.'),
  })

  function toggleSort(column: string) {
    setSort((current) => current === column ? `-${column}` : column)
    setPage(1)
  }

  return <section>
    <div className="topbar"><div><h1>{title}</h1><p className="muted">Consulta terceros y su calificación de satisfacción.</p></div><input className="search" placeholder="Buscar tercero" value={search} onChange={(event) => { setSearch(event.target.value); setPage(1) }} /></div>
    <div className="panel">
      {message && <p className="muted" role="status">{message}</p>}
      {rows.isLoading && <p className="muted">Cargando {title.toLowerCase()}...</p>}
      {rows.isError && <div className="error">No fue posible cargar los registros.</div>}
      {rows.data && <>
        <div className="table-wrap"><table><thead><tr>
          {[['documento', 'Documento'], ['razon_social', 'Nombre'], ['email', 'Correo'], [scoreColumn, 'Puntaje']].map(([column, label]) => <th key={column}><button className="table-sort" type="button" onClick={() => toggleSort(column)}>{label}<span aria-hidden="true">{sort === column ? ' ↑' : sort === `-${column}` ? ' ↓' : ''}</span></button></th>)}
          <th>Acciones</th>
        </tr></thead><tbody>
          {rows.data.data.map((row) => <tr key={row.id}>
            <td>{row.documento || '-'}</td>
            <td>{row.razon_social || row.nombre_comercial || '-'}</td>
            <td>{row.email || '-'}</td>
            <td>{String(row[scoreColumn] ?? '-')}</td>
            <td className="third-party-actions"><Link className="contracts-link" to={`/${isClient ? 'clientes' : 'proveedores'}/${row.id}/contratos`}><FileText size={17} aria-hidden="true" />Contratos</Link>{isClient && <button className="secondary" type="button" onClick={() => invite.mutate(row.id)} disabled={invite.isPending}>Enviar encuesta</button>}</td>
          </tr>)}
        </tbody></table></div>
        {rows.data.data.length === 0 && <p className="muted">No hay registros para mostrar.</p>}
        <div className="table-pagination"><span>{rows.data.meta.total} {title.toLowerCase()}</span><div><button className="secondary" type="button" onClick={() => setPage((current) => current - 1)} disabled={page === 1}>Anterior</button><span>Página {rows.data.meta.current_page} de {rows.data.meta.last_page}</span><button className="secondary" type="button" onClick={() => setPage((current) => current + 1)} disabled={page === rows.data.meta.last_page}>Siguiente</button></div></div>
      </>}
    </div>
  </section>
}
