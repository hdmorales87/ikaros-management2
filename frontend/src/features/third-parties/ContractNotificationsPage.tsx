import { useQuery } from '@tanstack/react-query'
import { contractApi } from '../../api'

export default function ContractNotificationsPage() {
  const notifications = useQuery({ queryKey: ['v1-contract-notifications'], queryFn: contractApi.notifications })

  return <section>
    <div className="topbar"><div><h1>Notificaciones de contratos</h1><p className="muted">Consulta el estado de avisos por vencimiento, renovación y pagos.</p></div></div>
    <div className="panel">
      <div className="table-wrap">
        <table>
          <thead>
            <tr><th>Contrato</th><th>Vencimiento</th><th>Renovación</th><th>Pagos</th><th>Activo</th></tr>
          </thead>
          <tbody>
            {(notifications.data ?? []).map((item) => <tr key={item.id}>
              <td>{item.contrato}</td>
              <td>{item.primera_notificacion_vencimiento || '-'}</td>
              <td>{item.primera_notificacion_renovacion || '-'}</td>
              <td>{item.primera_notificacion_pagos || '-'}</td>
              <td>{String(item.activo)}</td>
            </tr>)}
          </tbody>
        </table>
      </div>
    </div>
  </section>
}
