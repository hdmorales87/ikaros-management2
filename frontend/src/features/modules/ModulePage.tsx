import DataGridPage from '../grid/DataGridPage'
import { ModuleDefinition } from './module-config'
import CatalogPage from '../catalogs/CatalogPage'
import RolesPage from '../roles/RolesPage'
import RequestBoardPage from '../requests/RequestBoardPage'
import HoursPage from '../hours/HoursPage'
import KnowledgePage from '../knowledge/KnowledgePage'
import ProjectsPage from '../projects/ProjectsPage'
import ActivitiesPage from '../projects/ActivitiesPage'
import GanttPage from '../projects/GanttPage'
import TrainingAttendeesPage from '../training/TrainingAttendeesPage'
import AssetIndexPage from '../assets/AssetIndexPage'
import CompanyPage from '../companies/CompanyPage'
import SmtpPage from '../settings/SmtpPage'
import ImapPage from '../settings/ImapPage'
import SecurityPolicyPage from '../settings/SecurityPolicyPage'
import ThirdPartiesPage from '../third-parties/ThirdPartiesPage'
import ThirdPartyContractsPage from '../third-parties/ThirdPartyContractsPage'
import ContractNotificationsPage from '../third-parties/ContractNotificationsPage'
import InitiativesPage from '../initiatives/InitiativesPage'
import CommitteesPage from '../initiatives/CommitteesPage'
import CommitteeApprovalsPage from '../initiatives/CommitteeApprovalsPage'
import CompanyModulesPage from '../companies/CompanyModulesPage'
import ReportsPage from '../reports/ReportsPage'
import TrainingPage from '../training/TrainingPage'
import LocationsPage from '../settings/LocationsPage'
import ThirdPartySurveyQuestionsPage from '../settings/ThirdPartySurveyQuestionsPage'

export default function ModulePage({ definition }: { definition: ModuleDefinition }) {
  if (definition.path === 'proyectos') return <ProjectsPage />
  if (definition.path === 'capacitaciones') return <TrainingPage />
  if (definition.path === 'conocimiento') return <KnowledgePage />
  if (definition.path === 'horas') return <HoursPage />
  if (definition.path === 'config/roles') return <RolesPage />
  if (definition.path === 'config/empresa') return <CompanyPage />
  if (definition.path === 'config/empresa/modulos') return <CompanyModulesPage />
  if (definition.path === 'config/smtp') return <SmtpPage />
  if (definition.path === 'config/imap') return <ImapPage />
  if (definition.path === 'config/politicas') return <SecurityPolicyPage />
  if (definition.path === 'iniciativas') return <InitiativesPage />
  if (definition.path === 'iniciativas/comites') return <CommitteesPage />
  if (definition.path === 'iniciativas/trazabilidad') return <CommitteeApprovalsPage />
  if (definition.path === 'activos') return <AssetIndexPage definition={definition} />
  if (definition.path === 'config/areas') return <CatalogPage table={definition.table} title={definition.title} description={definition.description} fields={['nombre', 'capacidad_atencion']} />
  if (definition.path === 'config/departamentos') return <CatalogPage table={definition.table} title={definition.title} description={definition.description} fields={['nombre']} />
  if (definition.path === 'config/ubicaciones') return <LocationsPage />
  if (definition.path === 'config/encuesta-terceros') return <ThirdPartySurveyQuestionsPage />
  if (definition.path === 'config/contratos-notificaciones') return <ContractNotificationsPage />
  if (definition.path === 'config/categorias') return <CatalogPage table={definition.table} title={definition.title} description={definition.description} fields={['id_area', 'nombre']} />
  if (definition.path === 'config/subcategorias') return <CatalogPage table={definition.table} title={definition.title} description={definition.description} fields={['id_categoria', 'nombre']} />
  if (definition.path.startsWith('config/')) return <CatalogPage table={definition.table} title={definition.title} description={definition.description} fields={['nombre']} />
  if (definition.path === 'incidencias') return <RequestBoardPage table="incidencias" type="incidencia" title={definition.title} />
  if (definition.path === 'problemas') return <RequestBoardPage table="incidencias" type="problema" title={definition.title} filter={{ problema: true }} />
  if (definition.path === 'servicios') return <RequestBoardPage table="servicios" type="servicio" title={definition.title} />
  if (definition.path === 'proyectos/actividades') return <ActivitiesPage />
  if (definition.path === 'proyectos/gantt') return <GanttPage />
  if (definition.path === 'reportes') return <ReportsPage />
  if (definition.path === 'capacitaciones/asistentes') return <TrainingAttendeesPage />
  if (definition.path === 'clientes') return <ThirdPartiesPage kind="cliente" />
  if (definition.path === 'clientes/contratos') return <ThirdPartyContractsPage kind="cliente" />
  if (definition.path === 'proveedores') return <ThirdPartiesPage kind="proveedor" />
  if (definition.path === 'proveedores/contratos') return <ThirdPartyContractsPage kind="proveedor" />
  if (definition.path === 'config/empresa/modulos') return <CompanyModulesPage />
  if (definition.path === 'config/dias-festivos') return <CatalogPage table={definition.table} title={definition.title} description={definition.description} fields={['fecha', 'descripcion']} />
  return <DataGridPage config={definition} />
}