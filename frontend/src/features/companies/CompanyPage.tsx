import { useQuery } from '@tanstack/react-query'
import { companyApi } from '../../api'

export default function CompanyPage() {
  const company = useQuery({ queryKey: ['company'], queryFn: companyApi.current })
  return <section><div className="topbar"><div><h1>Empresa</h1><p className="muted">Información de la organización activa.</p></div></div><div className="panel company-card">{company.isLoading && <p className="muted">Cargando información...</p>}{company.isError && <div className="error">No fue posible cargar la empresa.</div>}{company.data && <div className="detail-grid">{Object.entries(company.data).map(([key, value]) => <div className="detail-item" key={key}><span>{key}</span><strong>{String(value ?? '-')}</strong></div>)}</div>}</div></section>
}