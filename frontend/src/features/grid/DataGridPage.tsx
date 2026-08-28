import { ReactNode, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { assetApi, getGridRows, GridRow } from '../../api'

export type GridConfig = { table: string; title: string; description: string; columns: string[]; filters?: Record<string, unknown>; searchFields?: string[]; action?: (row: GridRow) => ReactNode }

function displayValue(value: unknown) {
  if (value === null || value === undefined || value === '') return '-'
  if (typeof value === 'boolean') return value ? 'Sí' : 'No'
  return String(value)
}

export default function DataGridPage({ config }: { config: GridConfig }) {
  const [search, setSearch] = useState(''); const [message, setMessage] = useState(''); const queryClient = useQueryClient()
  const searchFields = config.searchFields || config.columns.filter((column) => column !== 'id')
  const rows = useQuery({ queryKey: ['grid', config.table, search, config.filters, searchFields], queryFn: () => getGridRows(config.table, search, config.filters, searchFields) })
  const generate = useMutation({ mutationFn: assetApi.generateCode, onSuccess: (data) => { setMessage(data.detail ? `Código generado: ${data.detail}` : 'El activo ya tiene código.'); queryClient.invalidateQueries({ queryKey: ['grid', config.table] }) }, onError: () => setMessage('No fue posible generar el código.') })
  return <section><div className="topbar"><div><h1>{config.title}</h1><p className="muted">{config.description}</p></div><input className="search" placeholder="Buscar" value={search} onChange={(event) => setSearch(event.target.value)} /></div><div className="panel">{message && <p className="muted" role="status">{message}</p>}{rows.isLoading && <p className="muted">Cargando registros...</p>}{rows.isError && <div className="error">No fue posible cargar los registros.</div>}{rows.data && <div className="table-wrap"><table><thead><tr>{config.columns.map((column) => <th key={column}>{column}</th>)}{(config.table === 'activos' || config.action) && <th>Acción</th>}</tr></thead><tbody>{rows.data.map((row: GridRow, index) => <tr key={String(row.id ?? index)}>{config.columns.map((column) => <td key={column}>{displayValue(row[column])}</td>)}{config.action && <td>{config.action(row)}</td>}{config.table === 'activos' && <td><button className="secondary" onClick={() => row.id && generate.mutate(row.id)} disabled={generate.isPending}>Generar código</button></td>}</tr>)}</tbody></table>{rows.data.length === 0 && <p className="muted">No hay registros para mostrar.</p>}</div>}</div></section>
}
