import { Boxes, Building2, Cable, ClipboardList, Cog, FileText, KeyRound, Landmark, Mail, MapPinned, Network, ShieldCheck, UsersRound } from 'lucide-react'
import { Link, Navigate } from 'react-router-dom'
import { useAuth } from '../../auth'
import { ModuleDefinition } from '../modules/module-config'

type AdministrationPageProps = {
  definitions: ModuleDefinition[]
  canManageUsers: boolean
}

type AdministrationItem = Pick<ModuleDefinition, 'path' | 'title'>

type Group = {
  title: string
  description: string
  paths: string[]
  icon: typeof Cog
}

const groups: Group[] = [
  { title: 'Organización', description: 'Empresa, áreas y estructura operativa.', paths: ['config/empresa', 'config/empresa/modulos', 'config/areas', 'config/departamentos', 'config/ubicaciones'], icon: Building2 },
  { title: 'Seguridad', description: 'Roles, acceso y políticas de seguridad.', paths: ['config/roles', 'config/politicas'], icon: ShieldCheck },
  { title: 'Solicitudes', description: 'Catálogos y encuestas para la operación.', paths: ['config/categorias', 'config/subcategorias', 'config/encuesta-satisfaccion', 'config/encuesta-terceros', 'config/dias-festivos'], icon: ClipboardList },
  { title: 'Activos y archivos', description: 'Tipos, documentos y extensiones admitidas.', paths: ['config/tipos-activo', 'config/tipos-documentacion', 'config/extensiones'], icon: FileText },
  { title: 'Integraciones', description: 'Correo saliente y correo entrante.', paths: ['config/smtp', 'config/imap'], icon: Mail },
  { title: 'Terceros y contratos', description: 'Monedas, estados, planes y avisos.', paths: ['config/monedas', 'config/contratos-estados', 'config/contratos-planes-pago', 'config/contratos-notificaciones'], icon: Landmark },
  { title: 'Riesgos', description: 'Escalas para análisis de riesgos de proyectos.', paths: ['config/riesgos/probabilidad', 'config/riesgos/impacto'], icon: Network },
]

const userGroup: Group = { title: 'Usuarios', description: 'Personas con acceso a la organización.', paths: ['usuarios'], icon: UsersRound }

const fallbackIcons = [Cog, KeyRound, MapPinned, Boxes, Cable]

export default function AdministrationPage({ definitions, canManageUsers }: AdministrationPageProps) {
  const { session } = useAuth()
  const modules = new Set(String(session?.modulos || '').split(',').filter(Boolean).map(Number))
  const canAccessAdministration = session?.is_superuser === true || modules.has(9)
  const definitionsByPath = new Map<string, AdministrationItem>(definitions.map((definition) => [definition.path, definition]))
  if (canManageUsers) definitionsByPath.set('usuarios', { path: 'usuarios', title: 'Usuarios' })
  const visibleGroups = [...(canManageUsers ? [userGroup] : []), ...groups]
    .map((group, groupIndex) => ({ ...group, items: group.paths.map((path) => definitionsByPath.get(path)).filter((item): item is AdministrationItem => Boolean(item)), groupIndex }))
    .filter((group) => group.items.length > 0)

  if (!canAccessAdministration) return <Navigate to="/" replace />

  return <section className="administration-page">
    <div className="topbar administration-header"><div><p className="eyebrow">Panel de control</p><h1>Administración</h1><p className="muted">Configura la organización y sus servicios sin perderte entre opciones.</p></div></div>
    <div className="administration-groups">
      {visibleGroups.map((group) => {
        const GroupIcon = group.icon
        return <section className="administration-group" key={group.title}>
          <div className="administration-group-heading"><span className="administration-group-icon"><GroupIcon size={20} strokeWidth={2.2} /></span><div><h2>{group.title}</h2><p>{group.description}</p></div></div>
          <div className="administration-links">
            {group.items.map((item, itemIndex) => {
              const ItemIcon = fallbackIcons[(group.groupIndex + itemIndex) % fallbackIcons.length]
              return <Link className="administration-link" to={`/${item.path}`} key={item.path}><span><ItemIcon size={18} strokeWidth={2.2} /></span><strong>{item.title}</strong></Link>
            })}
          </div>
        </section>
      })}
    </div>
  </section>
}