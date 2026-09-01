import { FormEvent, useEffect, useState } from 'react'
import { ArrowLeft, CheckCircle2, CircleAlert, LoaderCircle, LogOut, Menu, UserRound, X } from 'lucide-react'
import { Navigate, NavLink, Route, Routes, useNavigate } from 'react-router-dom'
import { checkCompany, checkUsername } from './api'
import { AuthProvider, useAuth } from './auth'
import { ClientSurveyPage, PasswordPage, RejectPage, SatisfactionPage } from './public-pages'
import ForgotPasswordPage from './features/auth/ForgotPasswordPage'
import DashboardPage from './features/dashboard/DashboardPage'
import ModulePage from './features/modules/ModulePage'
import { moduleDefinitions } from './features/modules/module-config'
import InitiativeValidationPage from './features/initiatives/InitiativeValidationPage'
import RequestDetailPage from './features/requests/RequestDetailPage'
import RequestPage from './features/requests/RequestPage'
import UsersPage from './features/users/UsersPage'
import AssetDetailPage from './features/assets/AssetDetailPage'
import TrainingAttendeesPage from './features/training/TrainingAttendeesPage'
import ThirdPartyContractsPage from './features/third-parties/ThirdPartyContractsPage'
import ProfilePage from './features/profile/ProfilePage'
import AdministrationPage from './features/settings/AdministrationPage'
import AdministrationBackLink from './components/AdministrationBackLink'
import RequirePermission from './components/RequirePermission'

function LoginPage() {
  const navigate = useNavigate()
  const { login: authenticate } = useAuth()
  const [username, setUsername] = useState('')
  const [password, setPassword] = useState('')
  const [documento, setDocumento] = useState('')
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)
  const [companyName, setCompanyName] = useState('')
  const [companyLookupError, setCompanyLookupError] = useState('')
  const [checkingCompany, setCheckingCompany] = useState(false)
  const [userName, setUserName] = useState('')
  const [checkingUser, setCheckingUser] = useState(false)
  const [hasSelectedCompany, setHasSelectedCompany] = useState(() => Boolean(localStorage.getItem('ikaros.uuid') || import.meta.env.VITE_COMPANY_UUID))
  const usesConfiguredCompany = Boolean(import.meta.env.VITE_COMPANY_UUID)
  const needsCompany = !hasSelectedCompany

  useEffect(() => {
    if (!needsCompany || !documento.trim()) {
      setCompanyName('')
      setCompanyLookupError('')
      setCheckingCompany(false)
      return
    }

    setCheckingCompany(true)
    const timeoutId = window.setTimeout(() => {
      checkCompany(documento.trim())
        .then((company) => { setCompanyName(company.razon_social); setCompanyLookupError('') })
        .catch(() => { setCompanyName(''); setCompanyLookupError('No encontramos una empresa activa con estos datos.') })
        .finally(() => setCheckingCompany(false))
    }, 500)

    return () => window.clearTimeout(timeoutId)
  }, [documento, needsCompany])

  useEffect(() => {
    const email = username.trim()
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email) || (needsCompany && !companyName)) {
      setUserName('')
      setCheckingUser(false)
      return
    }

    setCheckingUser(true)
    const timeoutId = window.setTimeout(() => {
      checkUsername(email)
        .then((user) => setUserName(user.msg === 'true' ? user.name_user || '' : ''))
        .catch(() => setUserName(''))
        .finally(() => setCheckingUser(false))
    }, 500)

    return () => window.clearTimeout(timeoutId)
  }, [companyName, needsCompany, username])

  async function submit(event: FormEvent) {
    event.preventDefault()
    setError('')
    setLoading(true)
    try {
      if (needsCompany) await checkCompany(documento)
      await authenticate(username, password)
      navigate('/', { replace: true })
    } catch (cause: unknown) {
      setError(cause instanceof Error ? cause.message : 'No fue posible iniciar sesión.')
    } finally {
      setLoading(false)
    }
  }

  function changeCompany() {
    localStorage.removeItem('ikaros.uuid')
    setDocumento('')
    setCompanyName('')
    setCompanyLookupError('')
    setUserName('')
    setError('')
    setHasSelectedCompany(false)
  }

  return <main className="auth-shell"><form className="auth-card" onSubmit={submit}>
    <div className="brand">IKAROS / MANAGEMENT</div>
    <h1>Acceso al sistema</h1>
    <p className="muted">Gestiona la operación de tu organización.</p>
    {needsCompany && <><label className="field">Documento de empresa<input value={documento} onChange={(event) => setDocumento(event.target.value)} required /></label>{checkingCompany && <p className="field-feedback field-feedback-pending"><LoaderCircle size={16} aria-hidden="true" />Verificando empresa...</p>}{companyName && <p className="field-feedback field-feedback-success"><CheckCircle2 size={17} aria-hidden="true" />{companyName}</p>}{companyLookupError && <p className="field-feedback field-feedback-error"><CircleAlert size={17} aria-hidden="true" />{companyLookupError}</p>}</>}
    {!needsCompany && !usesConfiguredCompany && <button className="change-company" type="button" onClick={changeCompany}><ArrowLeft size={16} aria-hidden="true" />Cambiar empresa</button>}
    <label className="field">Correo electrónico<input type="email" value={username} onChange={(event) => setUsername(event.target.value)} required autoComplete="username" /></label>{checkingUser && <p className="field-feedback field-feedback-pending"><LoaderCircle size={16} aria-hidden="true" />Buscando usuario...</p>}{userName && <p className="field-feedback field-feedback-success"><CheckCircle2 size={17} aria-hidden="true" />Hola, {userName}</p>}
    <label className="field">Contraseña<input type="password" value={password} onChange={(event) => setPassword(event.target.value)} required autoComplete="current-password" /></label>
    {error && <div className="error" role="alert">{error}</div>}
    <button className="primary" disabled={loading}>{loading ? 'Validando...' : 'Ingresar'}</button>
    <NavLink className="back-link" to="/forgot-password">¿Olvidaste tu contraseña?</NavLink>
  </form></main>
}

function ProtectedLayout() {
  const navigate = useNavigate()
  const { logout, session } = useAuth()
  const [navigationOpen, setNavigationOpen] = useState(false)
  const permissions = new Set(String(session?.permisos || '').split(',').filter(Boolean).map(Number))
  const modules = new Set(String(session?.modulos || '').split(',').filter(Boolean).map(Number))
  const isSuperUser = session?.is_superuser === true
  const definitions = moduleDefinitions.filter((definition) =>
    (definition.permission === undefined || isSuperUser || permissions.has(definition.permission) || permissions.has(1)) &&
    (definition.module === undefined || modules.size === 0 || modules.has(definition.module)),
  )
  const canManageUsers = isSuperUser || permissions.has(1) || permissions.has(22)
  const hasAdministration = isSuperUser || modules.has(9)
  const navigationDefinitions = definitions.filter((definition) => !definition.path.startsWith('config/'))
  const user = session?.userData
  const userName = [user?.primer_nombre || user?.nombre, user?.primer_apellido || user?.apellido].filter(Boolean).join(' ') || 'Mi perfil'
  const avatar = userName.slice(0, 1).toUpperCase()
  const closeNavigation = () => setNavigationOpen(false)
  const signOut = () => { logout(); navigate('/login', { replace: true }) }

  useEffect(() => {
    const closeOnEscape = (event: KeyboardEvent) => { if (event.key === 'Escape') closeNavigation() }
    window.addEventListener('keydown', closeOnEscape)
    return () => window.removeEventListener('keydown', closeOnEscape)
  }, [])

  return <div className="app-shell"><button className="mobile-menu-button" type="button" aria-label={navigationOpen ? 'Cerrar menú' : 'Abrir menú'} aria-expanded={navigationOpen} onClick={() => setNavigationOpen((open) => !open)}>{navigationOpen ? <X size={22} /> : <Menu size={22} />}</button>{navigationOpen && <button className="sidebar-backdrop" type="button" aria-label="Cerrar menú" onClick={closeNavigation} />}<aside className={`sidebar ${navigationOpen ? 'sidebar-open' : ''}`}><div className="sidebar-header"><div className="brand">IKAROS</div><button className="sidebar-close-button" type="button" aria-label="Cerrar menú" onClick={closeNavigation}><X size={20} /></button></div><nav>
    <NavLink className="nav-link" to="/" end onClick={closeNavigation}>Resumen</NavLink>
    <NavLink className="nav-link" to="/solicitudes/nueva" onClick={closeNavigation}>Nueva solicitud</NavLink>
    {navigationDefinitions.map((definition) => <NavLink className="nav-link" to={`/${definition.path}`} key={definition.path} onClick={closeNavigation}>{definition.title}</NavLink>)}
    {hasAdministration && <NavLink className="nav-link nav-link-administration" to="/administracion" onClick={closeNavigation}>Administración</NavLink>}
  </nav><div className="sidebar-profile"><NavLink className="profile-trigger" to="/perfil" onClick={closeNavigation}><span className="profile-avatar">{avatar}</span><span><strong>{userName}</strong><small>{user?.rol || 'Mi cuenta'}</small></span><UserRound size={18} aria-hidden="true" /></NavLink><button className="sidebar-logout" type="button" onClick={signOut}><LogOut size={17} aria-hidden="true" />Cerrar sesión</button></div></aside><main className="content"><Routes>
    <Route index element={<DashboardPage />} />
    <Route path="perfil" element={<ProfilePage />} />
    {canManageUsers && <Route path="usuarios" element={<><AdministrationBackLink /><UsersPage /></>} />}
    <Route path="solicitudes/nueva" element={<RequestPage />} />
    <Route path="solicitudes/:table/:id" element={<RequestDetailPage />} />
    <Route path="capacitaciones/:id/asistentes" element={<TrainingAttendeesPage />} />
    <Route path="clientes/:id/contratos" element={<RequirePermission permission={62}><ThirdPartyContractsPage kind="cliente" /></RequirePermission>} />
    <Route path="proveedores/:id/contratos" element={<RequirePermission permission={14}><ThirdPartyContractsPage kind="proveedor" /></RequirePermission>} />
    <Route path="activos/:id" element={<RequirePermission permission={11}><AssetDetailPage /></RequirePermission>} />
    <Route path="administracion" element={<AdministrationPage definitions={definitions} canManageUsers={canManageUsers} />} />
    {definitions.map((definition) => <Route path={definition.path} element={definition.path.startsWith('config/') ? <><AdministrationBackLink /><ModulePage definition={definition} /></> : <ModulePage definition={definition} />} key={definition.path} />)}
    <Route path="*" element={<Navigate to="/" replace />} />
  </Routes></main></div>
}

function Protected() { return localStorage.getItem('ikaros.token') ? <ProtectedLayout /> : <Navigate to="/login" replace /> }

export default function App() {
  return <AuthProvider><Routes>
    <Route path="/login" element={<LoginPage />} />
    <Route path="/forgot-password" element={<ForgotPasswordPage />} />
    <Route path="/resetPassword/:token/:user/:opcion/:uuid" element={<PasswordPage />} />
    <Route path="/encuestaSatisfaccion/:idSolicitud/:tabla/:uuid" element={<SatisfactionPage />} />
    <Route path="/encuestaCliente/:lastId/:idCliente/:uuid" element={<ClientSurveyPage />} />
    <Route path="/rechazoSolucion/:idSolicitud/:tabla/:idUsuario/:uuid" element={<RejectPage />} />
    <Route path="/validarIniciativa/:opc/:idRow/:idComite/:idUser/:uuid" element={<InitiativeValidationPage />} />
    <Route path="/*" element={<Protected />} />
  </Routes></AuthProvider>
}
