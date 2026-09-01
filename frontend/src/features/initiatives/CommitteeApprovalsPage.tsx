import { useQuery } from '@tanstack/react-query'
import { initiativeApprovalApi } from '../../api'

export default function CommitteeApprovalsPage() {
  const approvals = useQuery({ queryKey: ['v1-initiative-approvals'], queryFn: initiativeApprovalApi.trace })

  return <section>
    <div className="topbar"><div><h1>Trazabilidad de aprobadores</h1><p className="muted">Consulta el estado de validación por comité y responsable.</p></div></div>
    <div className="panel">
      <div className="table-wrap">
        <table>
          <thead>
            <tr><th>Comité</th><th>Aprobador</th><th>Estado</th></tr>
          </thead>
          <tbody>
            {(approvals.data ?? []).map((item) => <tr key={item.id}>
              <td>{item.comite}</td>
              <td>{`${item.nombre_usuario} ${item.apellido_usuario}`.trim()}</td>
              <td>{item.estado_validacion}</td>
            </tr>)}
          </tbody>
        </table>
      </div>
    </div>
  </section>
}
