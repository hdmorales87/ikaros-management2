import { useQuery } from '@tanstack/react-query'
import { policyApi } from '../../api'

export default function SecurityPolicyPage() {
  const policies = useQuery({ queryKey: ['security-policy'], queryFn: policyApi.current })
  return <section><div className="topbar"><div><h1>Políticas de seguridad</h1><p className="muted">Reglas activas de seguridad de la organización.</p></div></div><div className="panel">{policies.isLoading && <p className="muted">Cargando políticas...</p>}{policies.isError && <div className="error">No fue posible cargar las políticas.</div>}{policies.data?.map((policy, index) => <div className="detail-grid" key={index}>{Object.entries(policy).map(([key, value]) => <div className="detail-item" key={key}><span>{key}</span><strong>{String(value ?? '-')}</strong></div>)}</div>)}</div></section>
}