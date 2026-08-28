import { useMemo } from 'react'
import { useQuery } from '@tanstack/react-query'
import { getGridRows, GridRow } from '../../api'

export default function CommitteeApprovalsPage() {
  const committees = useQuery({ queryKey: ['committee-trace-committees'], queryFn: () => getGridRows('iniciativas_comites', '', {}, ['nombre']) })
  const users = useQuery({ queryKey: ['committee-trace-users'], queryFn: () => getGridRows('users', '', { activo: true }, ['nombre', 'apellido']) })
  const approvals = useQuery({ queryKey: ['committee-trace'], queryFn: () => getGridRows('iniciativas_comites_aprobadores', '', {}, ['id_comite', 'id_user', 'estado_validacion']) })

  const userMap = useMemo(() => Object.fromEntries((users.data ?? []).map((item) => [String(item.id), `${String(item.nombre ?? '')} ${String(item.apellido ?? '')}`.trim()])), [users.data])
  const committeeMap = useMemo(() => Object.fromEntries((committees.data ?? []).map((item) => [String(item.id), String(item.nombre ?? '-')])), [committees.data])

  return <section>
    <div className="topbar"><div><h1>Trazabilidad de aprobadores</h1><p className="muted">Consulta el estado de validación por comité y responsable.</p></div></div>
    <div className="panel">
      <div className="table-wrap">
        <table>
          <thead>
            <tr><th>Comité</th><th>Aprobador</th><th>Estado</th></tr>
          </thead>
          <tbody>
            {(approvals.data ?? []).map((item: GridRow, index) => <tr key={String(item.id ?? index)}>
              <td>{committeeMap[String(item.id_comite)] ?? String(item.id_comite ?? '-')}</td>
              <td>{userMap[String(item.id_user)] ?? String(item.id_user ?? '-')}</td>
              <td>{String(item.estado_validacion ?? '-')}</td>
            </tr>)}
          </tbody>
        </table>
      </div>
    </div>
  </section>
}
