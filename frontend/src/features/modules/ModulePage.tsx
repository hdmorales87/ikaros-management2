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
import AssetIndexPage from '../assets/AssetIndexPage'
import CompanyPage from '../companies/CompanyPage'
import SmtpPage from '../settings/SmtpPage'
import ImapPage from '../settings/ImapPage'
import SecurityPolicyPage from '../settings/SecurityPolicyPage'
import ThirdPartiesPage from '../third-parties/ThirdPartiesPage'
import ContractNotificationsPage from '../third-parties/ContractNotificationsPage'
import InitiativesPage from '../initiatives/InitiativesPage'
import CommitteesPage from '../initiatives/CommitteesPage'
import CommitteeApprovalsPage from '../initiatives/CommitteeApprovalsPage'
import CompanyModulesPage from '../companies/CompanyModulesPage'
import ReportsPage from '../reports/ReportsPage'
import TrainingPage from '../training/TrainingPage'
import LocationsPage from '../settings/LocationsPage'
import ThirdPartySurveyQuestionsPage from '../settings/ThirdPartySurveyQuestionsPage'
import CalendarPage from '../calendar/CalendarPage'

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
  if (definition.path === 'calendario') return <CalendarPage />
  if (definition.path === 'config/areas') return <CatalogPage resource="service-areas" title={definition.title} description={definition.description} fields={['nombre', 'capacidad_atencion']} />
  if (definition.path === 'config/departamentos') return <CatalogPage resource="departments" title={definition.title} description={definition.description} fields={['nombre']} />
  if (definition.path === 'config/ubicaciones') return <LocationsPage />
  if (definition.path === 'config/encuesta-terceros') return <ThirdPartySurveyQuestionsPage />
  if (definition.path === 'config/contratos-notificaciones') return <ContractNotificationsPage />
  if (definition.path === 'config/categorias') return <CatalogPage resource="service-categories" title={definition.title} description={definition.description} fields={['id_area', 'nombre']} />
  if (definition.path === 'config/subcategorias') return <CatalogPage resource="service-subcategories" title={definition.title} description={definition.description} fields={['id_categoria', 'nombre']} />
  if (definition.path === 'config/tipos-activo') return <CatalogPage resource="asset-types" title={definition.title} description={definition.description} fields={['nombre']} />
  if (definition.path === 'config/monedas') return <CatalogPage resource="currencies" title={definition.title} description={definition.description} fields={['nombre']} />
  if (definition.path === 'config/tipos-documentacion') return <CatalogPage resource="documentation-types" title={definition.title} description={definition.description} fields={['nombre']} />
  if (definition.path === 'config/extensiones') return <CatalogPage resource="file-extensions" title={definition.title} description={definition.description} fields={['nombre']} />
  if (definition.path === 'config/encuesta-satisfaccion') return <CatalogPage resource="satisfaction-questions" title={definition.title} description={definition.description} fields={['nombre']} />
  if (definition.path === 'config/contratos-estados') return <CatalogPage resource="contract-states" title={definition.title} description={definition.description} fields={['nombre']} />
  if (definition.path === 'config/contratos-planes-pago') return <CatalogPage resource="payment-plans" title={definition.title} description={definition.description} fields={['nombre']} />
  if (definition.path === 'config/dias-festivos') return <CatalogPage resource="holidays" title={definition.title} description={definition.description} fields={['fecha', 'descripcion']} />
  if (definition.path === 'config/riesgos/probabilidad') return <CatalogPage resource="risk-probabilities" title={definition.title} description={definition.description} fields={['nombre']} />
  if (definition.path === 'config/riesgos/impacto') return <CatalogPage resource="risk-impacts" title={definition.title} description={definition.description} fields={['nombre']} />
  if (definition.path === 'incidencias') return <RequestBoardPage table="incidencias" type="incidencia" title={definition.title} />
  if (definition.path === 'problemas') return <RequestBoardPage table="incidencias" type="problema" title={definition.title} filter={{ problema: true }} />
  if (definition.path === 'servicios') return <RequestBoardPage table="servicios" type="servicio" title={definition.title} />
  if (definition.path === 'proyectos/actividades') return <ActivitiesPage />
  if (definition.path === 'proyectos/gantt') return <GanttPage />
  if (definition.path === 'reportes') return <ReportsPage />
  if (definition.path === 'clientes') return <ThirdPartiesPage kind="cliente" />
  if (definition.path === 'proveedores') return <ThirdPartiesPage kind="proveedor" />
  if (definition.path === 'config/empresa/modulos') return <CompanyModulesPage />
  return <DataGridPage config={definition} />
}