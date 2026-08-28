import { Link } from 'react-router-dom'
import DataGridPage from '../grid/DataGridPage'
import { ModuleDefinition } from '../modules/module-config'

export default function AssetIndexPage({ definition }: { definition: ModuleDefinition }) {
  return <DataGridPage config={{ ...definition, action: (row) => <Link className="secondary" to={`/activos/${String(row.id)}`}>Abrir</Link> }} />
}
