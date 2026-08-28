import { useMemo } from 'react'
import { useQuery } from '@tanstack/react-query'
import { getGridRows, GridRow } from '../../api'

export default function ContractNotificationsPage() {
  const contracts = useQuery({ queryKey: ['contract-notifications-contracts'], queryFn: () => getGridRows('terceros_contratos', '', {}, ['nombre', 'tipo']) })
  const notifications = useQuery({ queryKey: ['contract-notifications'], queryFn: () => getGridRows('terceros_contratos_notificaciones', '', {}, ['id_contrato', 'primera_notificacion_vencimiento', 'primera_notificacion_renovacion', 'primera_notificacion_pagos']) })

  const contractMap = useMemo(() => Object.fromEntries((contracts.data ?? []).map((item) => [String(item.id), String(item.nombre ?? '-') ])), [contracts.data])

  return <section>
    <div className="topbar"><div><h1>Notificaciones de contratos</h1><p className="muted">Consulta el estado de avisos por vencimiento, renovación y pagos.</p></div></div>
    <div className="panel">
      <div className="table-wrap">
        <table>
          <thead>
            <tr><th>Contrato</th><th>Vencimiento</th><th>Renovación</th><th>Pagos</th><th>Activo</th></tr>
          </thead>
          <tbody>
            {(notifications.data ?? []).map((item: GridRow, index) => <tr key={String(item.id ?? index)}>
              <td>{contractMap[String(item.id_contrato)] ?? String(item.id_contrato ?? '-')}</td>
              <td>{String(item.primera_notificacion_vencimiento ?? '-')}</td>
              <td>{String(item.primera_notificacion_renovacion ?? '-')}</td>
              <td>{String(item.primera_notificacion_pagos ?? '-')}</td>
              <td>{String(item.activo ?? '-')}</td>
            </tr>)}
          </tbody>
        </table>
      </div>
    </div>
  </section>
}
