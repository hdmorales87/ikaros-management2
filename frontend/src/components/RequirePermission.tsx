import { ReactNode } from 'react'
import { Navigate } from 'react-router-dom'
import { useAuth } from '../auth'

export default function RequirePermission({ permission, children }: { permission: number; children: ReactNode }) {
  const { session } = useAuth()
  const permissions = new Set(String(session?.permisos || '').split(',').filter(Boolean).map(Number))
  const isSuperUser = session?.is_superuser === true
  return isSuperUser || permissions.has(1) || permissions.has(permission) ? <>{children}</> : <Navigate to="/" replace />
}
