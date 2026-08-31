import { Building2, Mail, ShieldCheck, UserRound } from 'lucide-react'
import { useState } from 'react'
import { useAuth } from '../../auth'

function displayName(user: { primer_nombre?: string; primer_apellido?: string; nombre?: string; apellido?: string } | undefined) {
  return [user?.primer_nombre || user?.nombre, user?.primer_apellido || user?.apellido].filter(Boolean).join(' ') || 'Usuario'
}

export default function ProfilePage() {
  const { session } = useAuth()
  const [imageFailed, setImageFailed] = useState(false)
  const user = session?.userData
  const name = displayName(user)
  const image = user?.imagen_usuario && session?.companyData?.uuid ? `http://localhost:8000/storage/${session.companyData.uuid}/${user.imagen_usuario}` : ''

  return <section className="profile-page"><div className="topbar"><div><p className="eyebrow">Cuenta</p><h1>Mi perfil</h1><p className="muted">Información asociada a tu sesión actual.</p></div></div><div className="profile-summary panel"><div className="profile-avatar profile-avatar-large">{image && !imageFailed ? <img src={image} alt={`Foto de ${name}`} onError={() => setImageFailed(true)} /> : <span>{name.slice(0, 1).toUpperCase()}</span>}</div><div><h2>{name}</h2><p>{user?.email || 'Sin correo registrado'}</p><span className="profile-role"><ShieldCheck size={16} aria-hidden="true" />{session?.is_superuser ? 'Superusuario' : 'Usuario del sistema'}</span></div></div><div className="profile-details"><article className="profile-detail"><Mail size={20} aria-hidden="true" /><div><span>Correo electrónico</span><strong>{user?.email || '-'}</strong></div></article><article className="profile-detail"><UserRound size={20} aria-hidden="true" /><div><span>Rol</span><strong>{user?.rol || (session?.is_superuser ? 'Superusuario' : 'Usuario')}</strong></div></article><article className="profile-detail"><Building2 size={20} aria-hidden="true" /><div><span>Organización</span><strong>{session?.companyData?.razon_social || '-'}</strong></div></article></div></section>
}