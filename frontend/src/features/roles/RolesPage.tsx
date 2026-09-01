import { useEffect, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { roleApi, roleManagementApi } from '../../api'
import { useAuth } from '../../auth'
import { Navigate } from 'react-router-dom'

export default function RolesPage() {
  const { session } = useAuth()
  const permissions = new Set(String(session?.permisos || '').split(',').filter(Boolean).map(Number))
  const queryClient = useQueryClient()
  const [roleId, setRoleId] = useState(0)
  const [selected, setSelected] = useState<number[]>([])
  const [message, setMessage] = useState('')
  const management = useQuery({ queryKey: ['v1-role-management'], queryFn: roleManagementApi.data })
  const rolePermissions = useQuery({ queryKey: ['role-permissions', roleId], queryFn: () => roleApi.permissions(roleId), enabled: roleId > 0 })
  const save = useMutation({ mutationFn: () => roleApi.savePermissions(roleId, selected), onSuccess: () => { queryClient.invalidateQueries({ queryKey: ['roles'] }); setMessage('Permisos guardados correctamente.') }, onError: () => setMessage('No fue posible guardar los permisos.') })
  useEffect(() => {
    setSelected(rolePermissions.data || [])
  }, [rolePermissions.data])

  if (!permissions.has(1) && !permissions.has(32)) return <Navigate to="/" replace />

  function togglePermission(id: number) {
    setSelected((current) => current.includes(id) ? current.filter((item) => item !== id) : [...current, id])
  }

  return <section><div className="topbar"><div><h1>Roles y permisos</h1><p className="muted">Configura los permisos de acceso por rol.</p></div></div><div className="panel"><label className="field">Rol<select value={roleId} onChange={(event) => { setRoleId(Number(event.target.value)); setSelected([]) }}><option value="0">Selecciona un rol</option>{management.data?.roles.map((role) => <option value={role.id} key={role.id}>{role.nombre || role.id}</option>)}</select></label><fieldset className="permission-list"><legend>Permisos disponibles</legend>{management.isLoading || rolePermissions.isLoading ? <p className="muted">Cargando permisos...</p> : management.data?.permissions.map((permission) => <label className="permission-item" key={permission.id}><input type="checkbox" checked={selected.includes(permission.id)} onChange={() => togglePermission(permission.id)} />{permission.nombre || permission.descripcion || permission.id}</label>)}</fieldset>{message && <p className="muted" role="status">{message}</p>}<button className="primary" onClick={() => save.mutate()} disabled={save.isPending || roleId === 0}>Guardar permisos</button></div></section>
}
