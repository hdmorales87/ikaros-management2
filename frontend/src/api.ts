import axios from 'axios'
import { getConfig } from './config'

export function apiErrorMessage(error: unknown, fallback: string): string {
  if (error && typeof error === 'object' && 'response' in error) {
    const response = (error as { response?: { data?: { detail?: string; message?: string; msg?: string } } }).response
    return response?.data?.detail || response?.data?.message || response?.data?.msg || fallback
  }
  return error instanceof Error ? error.message : fallback
}

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
})

api.interceptors.request.use(async (config) => {
  const runtimeConfig = await getConfig()
  const token = localStorage.getItem('ikaros.token')
  const uuid = localStorage.getItem('ikaros.uuid') || import.meta.env.VITE_COMPANY_UUID || runtimeConfig.uuidIMA
  if (!import.meta.env.VITE_API_URL && runtimeConfig.apiRoute) config.baseURL = runtimeConfig.apiRoute
  if (token && !config.headers.Authorization) config.headers.Authorization = `Bearer ${token}`
  if (uuid) config.headers['X-UUID'] = uuid
  const applicationId = import.meta.env.VITE_APPLICATION_ID || runtimeConfig.applicationID
  const applicationKey = import.meta.env.VITE_APPLICATION_KEY || runtimeConfig.applicationKEY
  if (applicationId) config.headers['X-Application-Id'] = applicationId
  if (applicationKey) config.headers['X-Application-Key'] = applicationKey
  return config
})

api.interceptors.response.use(undefined, (error) => {
  if (error.response?.status === 401) {
    localStorage.removeItem('ikaros.token')
    localStorage.removeItem('ikaros.session')
  }
  return Promise.reject(error)
})

export type Session = {
  token: string
  permisos?: string
  modulos?: string
  is_superuser?: boolean
  userData?: { id?: number; nombre?: string; apellido?: string; primer_nombre?: string; primer_apellido?: string; email?: string; imagen_usuario?: string; rol?: string; id_rol?: number }
  companyData?: { razon_social?: string; uuid?: string }
}

export type User = {
  id: number
  nombre: string
  apellido: string
  email: string
  id_rol: number
  activo: boolean
  acceso_sistema: boolean
}

export async function login(username: string, password: string): Promise<Session> {
  const credentials = btoa(`${username}:${password}`)
  const { data } = await api.post<Session>('/login', { username, password }, {
    headers: { Authorization: `Basic ${credentials}` },
  })
  localStorage.setItem('ikaros.token', data.token)
  localStorage.setItem('ikaros.session', JSON.stringify(data))
  return data
}

export async function checkCompany(documento: string): Promise<{ uuid: string; razon_social: string }> {
  const { data } = await api.post('/checkCompany', { documento })
  if (!data?.uuid) throw new Error('Empresa no encontrada.')
  localStorage.setItem('ikaros.uuid', data.uuid)
  return data
}

export async function checkUsername(username: string): Promise<{ name_user?: string; msg: string }> {
  const { data } = await api.get<{ name_user?: string; msg: string }>(`/checkUsername/${encodeURIComponent(username)}`)
  return data
}

export async function getUsers(): Promise<User[]> {
  const { data } = await api.get<User[]>('/users')
  return data
}

export const userApi = {
  list: getUsers,
  create: (payload: Record<string, unknown>) => api.post<User>('/users', payload).then(({ data }) => data),
  update: (id: number, payload: Record<string, unknown>) => api.put<User>(`/users/${id}`, payload).then(({ data }) => data),
  remove: (id: number) => api.delete(`/users/${id}`).then(({ data }) => data),
  sendActivation: (idUser: number) => api.post('/enviarMailActivacion', { idUser }).then(({ data }) => data),
}

export const roleApi = {
  permissions: (roleId: number) => api.get<number[]>(`/roles/${roleId}/permisos`).then(({ data }) => data),
  savePermissions: (idRol: number, arrayPermisos: number[]) => api.post('/guardaPermisos', { idRol, arrayPermisos }).then(({ data }) => data),
}

export type Asset = { id: number; codigo: string | null; nombre: string; marca: string | null; activo: number; id_tipo?: number | null; id_departamento?: number | null; id_proveedor?: number | null; estado?: number | null; id_asignado?: number | null; precio_compra?: number | null; fecha_compra?: string | null; numero_factura?: string | null; id_ubicacion?: number | null }
export type PaginatedResponse<T> = { data: T[]; meta: { current_page: number; per_page: number; total: number; last_page: number } }
export type AssetFormOption = { id: number; nombre: string; apellido?: string | null }
export type AssetFormOptions = { types: AssetFormOption[]; departments: AssetFormOption[]; providers: AssetFormOption[]; states: AssetFormOption[]; users: AssetFormOption[] }

export const assetApi = {
  list: (params: { page: number; per_page: number; search?: string; sort?: string }) => api.get<PaginatedResponse<Asset>>('/v1/assets', { params }).then(({ data }) => data),
  find: (id: number) => api.get<Asset>(`/v1/assets/${id}`).then(({ data }) => data),
  formOptions: () => api.get<AssetFormOptions>('/v1/assets/form-options').then(({ data }) => data),
  update: (id: number, payload: Partial<Asset>) => api.put<Asset>(`/v1/assets/${id}`, payload).then(({ data }) => data),
  generateCode: (idActivo: number) => api.post('/generarCodigoActivo', { idActivo }).then(({ data }) => data),
}

export type TechnicalField = { id: number; nombre: string; tipo: string; validacion?: string; longitud?: number; valor?: string | null }

export const technicalSheetApi = {
  fields: (tabla: string, idMaestro: number) => api.get<TechnicalField[]>('/camposFicha', { params: { tabla, idMaestro } }).then(({ data }) => data),
  values: (tabla: string, idCampo: number) => api.get<{ id: number; valor: string }[]>('/valoresFicha', { params: { tabla, idCampo } }).then(({ data }) => data),
  save: (tabla: string, idMaestro: number, arrayCampos: Record<string, string>) => api.post('/guardarCamposFicha', { tabla, idMaestro, arrayCampos }).then(({ data }) => data),
}

export const fileApi = {
  upload: (file: File, folder: string, id: number) => { const form = new FormData(); form.append('file', file); form.append('folder', folder); form.append('id', String(id)); return api.post('/uploaderFile', form, { headers: { 'Content-Type': 'multipart/form-data' } }).then(({ data }) => data) },
  list: (folder: string) => api.get<string[]>('/files', { params: { folder } }).then(({ data }) => data),
  download: (uuid: string, folder: string, file: string) => api.get(`/downloadFile/${encodeURIComponent(uuid)}/${encodeURIComponent(folder)}/${encodeURIComponent(file)}`, { responseType: 'blob' }).then(({ data }) => data as Blob),
}

export type RequestCatalog = { id: number; nombre: string }
export type Incident = { id: number; asunto: string; estado: number; prioridad: number | null }
export type ThirdParty = { id: number; documento: string | null; razon_social: string | null; nombre_comercial: string | null; email: string | null; puntaje_cliente?: number | null; puntaje_proveedor?: number | null }
export type ContractFormOption = { id: number; nombre: string; apellido?: string | null }
export type ContractFormOptions = { third_party: { id: number; razon_social: string | null; nombre_comercial: string | null }; currencies: ContractFormOption[]; states: ContractFormOption[]; payment_plans: ContractFormOption[]; users: ContractFormOption[] }
export type ContractAttachment = { id: number; nombre_archivo: string; fecha: string; id_usuario: number }
export type ContractNotification = { id: number; id_contrato: number; contrato: string; tipo: 'cliente' | 'proveedor'; primera_notificacion_vencimiento: string | null; primera_notificacion_renovacion: string | null; primera_notificacion_pagos: string | null; activo: number }
export type DashboardSummary = { incidents: number; services: number; assets: number; projects: number; trainings: number }
export type CalendarEvent = { date: string; title: string; kind: string; tone: 'blue' | 'violet' | 'teal' | 'amber' }
export type ThirdPartySurveyQuestion = { id: number; tipo: 'cliente' | 'proveedor'; nombre: string }
export type Location = { id: number; id_departamento: number; nombre: string }
export type ImapRule = { id: number; palabra_clave: string; tipo: 'incidencia' | 'problema' | 'servicio'; impacto: number; urgencia: number; id_area: number; id_categoria: number; id_subcategoria: number; asunto_default: string }
export type Training = { id: number; nombre: string; instructor: string | null; intensidad: number | null; fecha_inicio: string | null; hora_inicio: string | null; fecha_final: string | null; hora_final: string | null; lugar: string | null; observaciones: string | null }
export type TrainingAttendee = { id: number; id_usuario: number; asistencia: string | boolean }
export type OperationalReportRow = { id: number; asunto: string | null; estado: number | null; prioridad: number | null; fecha: string | null; tipo: 'Incidencia' | 'Problema' | 'Servicio' }
export type RoleManagementData = { roles: { id: number; nombre: string }[]; permissions: { id: number; nombre: string; descripcion: string | null }[] }
export type Project = { id: number; codigo: string | null; nombre: string; estado: number | null; fecha_inicio: string | null; fecha_final: string | null }
export type RequestDetail = Record<string, unknown> & { id: number; asunto?: string; descripcion?: string; estado?: number; prioridad?: number | null }
export type RequestFollowup = { id: number; estado: string; observacion: string; id_usuario: number; fecha: string }

export const requestApi = {
  listIncidents: (params: { page: number; per_page: number; search?: string; sort?: string }) => api.get<PaginatedResponse<Incident>>('/v1/incidents', { params }).then(({ data }) => data),
  listProblems: (params: { page: number; per_page: number; search?: string; sort?: string }) => api.get<PaginatedResponse<Incident>>('/v1/problems', { params }).then(({ data }) => data),
  listServices: (params: { page: number; per_page: number; search?: string; sort?: string }) => api.get<PaginatedResponse<Incident>>('/v1/services', { params }).then(({ data }) => data),
  findIncident: (id: number) => api.get<RequestDetail>(`/v1/incidents/${id}`).then(({ data }) => data),
  incidentFollowups: (id: number) => api.get<RequestFollowup[]>(`/v1/incidents/${id}/followups`).then(({ data }) => data),
  findService: (id: number) => api.get<RequestDetail>(`/v1/services/${id}`).then(({ data }) => data),
  serviceFollowups: (id: number) => api.get<RequestFollowup[]>(`/v1/services/${id}/followups`).then(({ data }) => data),
  assignIncident: (id: number) => api.post(`/v1/incidents/${id}/assignments`).then(({ data }) => data),
  startIncident: (id: number) => api.post(`/v1/incidents/${id}/start-processing`).then(({ data }) => data),
  assignProblem: (id: number) => api.post(`/v1/problems/${id}/assignments`).then(({ data }) => data),
  startProblem: (id: number) => api.post(`/v1/problems/${id}/start-processing`).then(({ data }) => data),
  assignService: (id: number) => api.post(`/v1/services/${id}/assignments`).then(({ data }) => data),
  startService: (id: number) => api.post(`/v1/services/${id}/start-processing`).then(({ data }) => data),
  urgencies: () => api.get<RequestCatalog[]>('/getSolicitudesUrgencias').then(({ data }) => data),
  impacts: () => api.get<RequestCatalog[]>('/getSolicitudesImpactos').then(({ data }) => data),
  areas: (module: string) => api.get<RequestCatalog[]>(`/getAreasServicioByModulo/${module}`).then(({ data }) => data),
  categories: (area: number) => api.get<RequestCatalog[]>(`/getCategoriasByAreaServicio/${area}`).then(({ data }) => data),
  subcategories: (category: number) => api.get<RequestCatalog[]>(`/getSubcategoriasByCategoria/${category}`).then(({ data }) => data),
  create: (payload: Record<string, unknown>) => api.post('/guardarSolicitud', payload).then(({ data }) => data),
  assign: (idRow: number, tipo: string) => api.post('/asignarSolicitud', { idRow, tipo }).then(({ data }) => data),
  manage: (payload: Record<string, unknown>) => api.post('/procesoGestion', payload).then(({ data }) => data),
}

export const hoursApi = {
  list: () => api.get<GridRow[]>('/v1/project-hours').then(({ data }) => data),
  notifyValidation: (id: number, tipo: string) => api.post('/notificarValidacionHoras', { id, tipo }).then(({ data }) => data),
  notifyConfirmation: (ids: number[], tipo: string) => api.post('/notificarConfirmacionHoras', { ids: JSON.stringify(ids), tipo }).then(({ data }) => data),
}

export type GridRow = Record<string, unknown> & { id?: number }
export type CatalogResource = 'service-areas' | 'departments' | 'service-categories' | 'service-subcategories' | 'asset-types' | 'currencies' | 'documentation-types' | 'file-extensions' | 'satisfaction-questions' | 'contract-states' | 'payment-plans' | 'holidays' | 'risk-probabilities' | 'risk-impacts'
  | 'imap-accounts'

function encodePayload(value: unknown): string {
  const bytes = new TextEncoder().encode(JSON.stringify(value))
  let binary = ''
  bytes.forEach((byte) => { binary += String.fromCharCode(byte) })
  return btoa(binary)
}

export async function getGridRows(table: string, searchWord = '', filters: Record<string, unknown> = {}, searchFields: ReadonlyArray<string> = ['nombre'], columns: ReadonlyArray<string> = []): Promise<GridRow[]> {
  const sqlParams = { fieldSearch: searchFields, ...(columns.length > 0 ? { sqlCols: columns } : {}) }
  const payload = encodePayload({ searchWord, filters, showRecords: 100, offsetRecord: 0, date1: '', date2: '', sqlParams, tabla: table, mode: 'rows' })
  const { data } = await api.post<GridRow[]>('/getDataGrid', { payload })
  return data
}

export async function getGridTotal(table: string, filters: Record<string, unknown> = {}): Promise<number> {
  const payload = encodePayload({ filters, searchWord: '', showRecords: 'todos', offsetRecord: 0, date1: '', date2: '', sqlParams: {}, tabla: table, mode: 'total' })
  const { data } = await api.post<{ total: number }[]>('/getDataGrid', { payload })
  return Number(data[0]?.total || 0)
}

export const gridApi = {
  insert: (table: string, data: Record<string, unknown>) => api.post('/dataGrid', { payload: encodePayload({ tabla: table, arrayData: data }) }).then(({ data: response }) => response),
  update: (table: string, data: Record<string, unknown>) => api.put('/dataGrid', { payload: encodePayload({ tabla: table, arrayData: data }) }).then(({ data: response }) => response),
  deactivate: (table: string, id: number) => api.delete('/dataGrid', { data: { payload: encodePayload({ tabla: table, id, actionDelete: 'deactivate' }) } }).then(({ data: response }) => response),
  remove: (table: string, id: number) => api.delete('/dataGrid', { data: { payload: encodePayload({ tabla: table, id, actionDelete: 'delete' }) } }).then(({ data: response }) => response),
}

export const catalogApi = {
  list: (resource: CatalogResource) => api.get<GridRow[]>(`/v1/configuration/${resource}`).then(({ data }) => data),
  create: (resource: CatalogResource, payload: Record<string, unknown>) => api.post<GridRow>(`/v1/configuration/${resource}`, payload).then(({ data }) => data),
  update: (resource: CatalogResource, id: number, payload: Record<string, unknown>) => api.put<GridRow>(`/v1/configuration/${resource}/${id}`, payload).then(({ data }) => data),
  deactivate: (resource: CatalogResource, id: number) => api.delete(`/v1/configuration/${resource}/${id}`).then(({ data }) => data),
}

export const publicApi = {
  requestPasswordReset: (email: string, opcion: 'reset' | 'force', uuid: string) => api.post('/emailPassword', { email, opcion }, { headers: { 'X-UUID': uuid } }).then(({ data }) => data),
  updatePassword: (payload: Record<string, string>, uuid: string) => api.post('/updatePassword', payload, { headers: { 'X-UUID': uuid } }).then(({ data }) => data),
  surveyStatus: (tabla: string, idSolicitud: number, uuid: string) => api.get('/cargarEncuesta', { params: { tabla, idSolicitud }, headers: { 'X-UUID': uuid } }).then(({ data }) => data),
  saveSurvey: (payload: Record<string, unknown>, uuid: string) => api.post('/guardarEncuesta', payload, { headers: { 'X-UUID': uuid } }).then(({ data }) => data),
  requestStatus: (tabla: string, id: number, uuid: string) => api.get('/verificarEstadoSolicitud', { params: { tabla, id }, headers: { 'X-UUID': uuid } }).then(({ data }) => data),
  reject: (payload: Record<string, unknown>, uuid: string) => api.post('/rechazarSolucion', payload, { headers: { 'X-UUID': uuid } }).then(({ data }) => data),
  clientQuestions: (uuid: string) => api.get('/getPreguntasEncuestaTercero', { headers: { 'X-UUID': uuid } }).then(({ data }) => data),
  saveClientSurvey: (payload: Record<string, unknown>, uuid: string) => api.post('/guardarEncuestaTercero', payload, { headers: { 'X-UUID': uuid } }).then(({ data }) => data),
}

export const knowledgeApi = {
  list: (topic = '') => api.get<{ id: number; tema: string }[]>('/getConocimientosByDescripcion', { params: { tema: topic } }).then(({ data }) => data),
  find: (id: number) => api.get<{ tema: string; solucion: string }[]>(`/getConocimientoById/${id}`).then(({ data }) => data[0]),
}

export type CompanyData = { id?: number; razon_social?: string; documento?: string; tipo_licencia?: string; fecha_vencimiento_licencia?: string; maximo_usuarios?: number; cuota_almacenamiento?: number; uuid?: string }

export const companyApi = {
  current: () => api.get<CompanyData>('/getCompanyData').then(({ data }) => data),
  modules: () => api.get<{ id_modulo: number }[]>('/getCompanyModules').then(({ data }) => data.map((item) => item.id_modulo)),
  saveModules: (arrayModulos: number[], idRow: number) => api.post('/guardaModulos', { arrayModulos, idRow }).then(({ data }) => data),
}

export const mailApi = {
  checkSmtp: (email: string) => api.post('/checkSMTP', { email }).then(({ data }) => data),
  checkImap: () => api.post('/checkIMAP').then(({ data }) => data as { folders: number }),
}

export const notificationApi = {
  initiative: (id: number) => api.post('/notificarValidacionIniciativa', { id }).then(({ data }) => data),
  committee: (id: number, idUser: number) => api.post('/notificarComite', { id, idUser }).then(({ data }) => data),
  clientSurvey: (idCliente: number) => api.post('/linkEncuestaCliente', { idCliente }).then(({ data }) => data),
}

export const thirdPartyApi = {
  listClients: (params: { page: number; per_page: number; search?: string; sort?: string }) => api.get<PaginatedResponse<ThirdParty>>('/v1/clients', { params }).then(({ data }) => data),
  listProviders: (params: { page: number; per_page: number; search?: string; sort?: string }) => api.get<PaginatedResponse<ThirdParty>>('/v1/providers', { params }).then(({ data }) => data),
}

export const contractApi = {
  notifications: () => api.get<ContractNotification[]>('/v1/contract-notifications').then(({ data }) => data),
  formOptionsForClient: (id: number) => api.get<ContractFormOptions>(`/v1/clients/${id}/contract-form-options`).then(({ data }) => data),
  formOptionsForProvider: (id: number) => api.get<ContractFormOptions>(`/v1/providers/${id}/contract-form-options`).then(({ data }) => data),
  listForClient: (id: number, params: { page: number; per_page: number; search?: string; sort?: string }) => api.get<PaginatedResponse<GridRow>>(`/v1/clients/${id}/contracts`, { params }).then(({ data }) => data),
  listForProvider: (id: number, params: { page: number; per_page: number; search?: string; sort?: string }) => api.get<PaginatedResponse<GridRow>>(`/v1/providers/${id}/contracts`, { params }).then(({ data }) => data),
  createForClient: (id: number, payload: Record<string, unknown>) => api.post<GridRow>(`/v1/clients/${id}/contracts`, payload).then(({ data }) => data),
  createForProvider: (id: number, payload: Record<string, unknown>) => api.post<GridRow>(`/v1/providers/${id}/contracts`, payload).then(({ data }) => data),
  updateForClient: (clientId: number, contractId: number, payload: Record<string, unknown>) => api.put<GridRow>(`/v1/clients/${clientId}/contracts/${contractId}`, payload).then(({ data }) => data),
  updateForProvider: (providerId: number, contractId: number, payload: Record<string, unknown>) => api.put<GridRow>(`/v1/providers/${providerId}/contracts/${contractId}`, payload).then(({ data }) => data),
  deactivateForClient: (clientId: number, contractId: number) => api.delete(`/v1/clients/${clientId}/contracts/${contractId}`).then(({ data }) => data),
  deactivateForProvider: (providerId: number, contractId: number) => api.delete(`/v1/providers/${providerId}/contracts/${contractId}`).then(({ data }) => data),
  paymentsForClient: (clientId: number, contractId: number) => api.get<GridRow[]>(`/v1/clients/${clientId}/contracts/${contractId}/payments`).then(({ data }) => data),
  paymentsForProvider: (providerId: number, contractId: number) => api.get<GridRow[]>(`/v1/providers/${providerId}/contracts/${contractId}/payments`).then(({ data }) => data),
  createPaymentForClient: (clientId: number, contractId: number, payload: Record<string, unknown>) => api.post<GridRow>(`/v1/clients/${clientId}/contracts/${contractId}/payments`, payload).then(({ data }) => data),
  createPaymentForProvider: (providerId: number, contractId: number, payload: Record<string, unknown>) => api.post<GridRow>(`/v1/providers/${providerId}/contracts/${contractId}/payments`, payload).then(({ data }) => data),
  deactivatePaymentForClient: (clientId: number, contractId: number, paymentId: number) => api.delete(`/v1/clients/${clientId}/contracts/${contractId}/payments/${paymentId}`).then(({ data }) => data),
  deactivatePaymentForProvider: (providerId: number, contractId: number, paymentId: number) => api.delete(`/v1/providers/${providerId}/contracts/${contractId}/payments/${paymentId}`).then(({ data }) => data),
  attachmentsForClient: (clientId: number, contractId: number) => api.get<ContractAttachment[]>(`/v1/clients/${clientId}/contracts/${contractId}/attachments`).then(({ data }) => data),
  attachmentsForProvider: (providerId: number, contractId: number) => api.get<ContractAttachment[]>(`/v1/providers/${providerId}/contracts/${contractId}/attachments`).then(({ data }) => data),
  uploadAttachmentForClient: (clientId: number, contractId: number, file: File) => { const body = new FormData(); body.append('file', file); return api.post<ContractAttachment>(`/v1/clients/${clientId}/contracts/${contractId}/attachments`, body, { headers: { 'Content-Type': 'multipart/form-data' } }).then(({ data }) => data) },
  uploadAttachmentForProvider: (providerId: number, contractId: number, file: File) => { const body = new FormData(); body.append('file', file); return api.post<ContractAttachment>(`/v1/providers/${providerId}/contracts/${contractId}/attachments`, body, { headers: { 'Content-Type': 'multipart/form-data' } }).then(({ data }) => data) },
  downloadAttachmentForClient: (clientId: number, contractId: number, attachmentId: number) => api.get(`/v1/clients/${clientId}/contracts/${contractId}/attachments/${attachmentId}/download`, { responseType: 'blob' }).then(({ data }) => data as Blob),
  downloadAttachmentForProvider: (providerId: number, contractId: number, attachmentId: number) => api.get(`/v1/providers/${providerId}/contracts/${contractId}/attachments/${attachmentId}/download`, { responseType: 'blob' }).then(({ data }) => data as Blob),
}

export const dashboardApi = {
  summary: () => api.get<DashboardSummary>('/v1/dashboard/summary').then(({ data }) => data),
}

export const calendarApi = {
  events: (month: string) => api.get<CalendarEvent[]>('/v1/calendar/events', { params: { month } }).then(({ data }) => data),
}

export const thirdPartySurveyQuestionApi = {
  list: (type: ThirdPartySurveyQuestion['tipo']) => api.get<ThirdPartySurveyQuestion[]>('/v1/third-party-survey-questions', { params: { type } }).then(({ data }) => data),
  create: (payload: Omit<ThirdPartySurveyQuestion, 'id'>) => api.post<ThirdPartySurveyQuestion>('/v1/third-party-survey-questions', payload).then(({ data }) => data),
  update: (id: number, payload: Omit<ThirdPartySurveyQuestion, 'id'>) => api.put<ThirdPartySurveyQuestion>(`/v1/third-party-survey-questions/${id}`, payload).then(({ data }) => data),
  deactivate: (id: number) => api.delete(`/v1/third-party-survey-questions/${id}`).then(({ data }) => data),
}

export const locationApi = {
  list: () => api.get<Location[]>('/v1/locations').then(({ data }) => data),
  create: (payload: Omit<Location, 'id'>) => api.post<Location>('/v1/locations', payload).then(({ data }) => data),
  update: (id: number, payload: Omit<Location, 'id'>) => api.put<Location>(`/v1/locations/${id}`, payload).then(({ data }) => data),
  deactivate: (id: number) => api.delete(`/v1/locations/${id}`).then(({ data }) => data),
}

export const imapApi = {
  rules: (accountId: number) => api.get<ImapRule[]>(`/v1/imap/accounts/${accountId}/rules`).then(({ data }) => data),
  createRule: (accountId: number, payload: Omit<ImapRule, 'id'>) => api.post<ImapRule>(`/v1/imap/accounts/${accountId}/rules`, payload).then(({ data }) => data),
  updateRule: (accountId: number, ruleId: number, payload: Omit<ImapRule, 'id'>) => api.put<ImapRule>(`/v1/imap/accounts/${accountId}/rules/${ruleId}`, payload).then(({ data }) => data),
  deactivateRule: (accountId: number, ruleId: number) => api.delete(`/v1/imap/accounts/${accountId}/rules/${ruleId}`).then(({ data }) => data),
}

export const trainingApi = {
  list: () => api.get<Training[]>('/v1/trainings').then(({ data }) => data),
  find: (id: number) => api.get<Training>(`/v1/trainings/${id}`).then(({ data }) => data),
  create: (payload: Omit<Training, 'id'>) => api.post<Training>('/v1/trainings', payload).then(({ data }) => data),
  update: (id: number, payload: Partial<Omit<Training, 'id'>>) => api.put<Training>(`/v1/trainings/${id}`, payload).then(({ data }) => data),
  deactivate: (id: number) => api.delete(`/v1/trainings/${id}`).then(({ data }) => data),
  attendees: (id: number) => api.get<TrainingAttendee[]>(`/v1/trainings/${id}/attendees`).then(({ data }) => data),
  addAttendee: (id: number, userId: number) => api.post<TrainingAttendee>(`/v1/trainings/${id}/attendees`, { id_usuario: userId }).then(({ data }) => data),
  updateAttendance: (id: number, attendeeId: number, attended: boolean) => api.put<TrainingAttendee>(`/v1/trainings/${id}/attendees/${attendeeId}`, { asistencia: attended }).then(({ data }) => data),
  removeAttendee: (id: number, attendeeId: number) => api.delete(`/v1/trainings/${id}/attendees/${attendeeId}`).then(({ data }) => data),
}

export const operationalReportApi = {
  requests: () => api.get<OperationalReportRow[]>('/v1/operational-reports/requests').then(({ data }) => data),
}

export const roleManagementApi = {
  data: () => api.get<RoleManagementData>('/v1/role-management').then(({ data }) => data),
}

export const projectApi = {
  list: (params: { page: number; per_page: number; search?: string; sort?: string }) => api.get<PaginatedResponse<Project>>('/v1/projects', { params }).then(({ data }) => data),
  update: (id: number, payload: Pick<Project, 'nombre' | 'fecha_inicio' | 'fecha_final'>) => api.put<Project>(`/v1/projects/${id}`, payload).then(({ data }) => data),
  activities: (id: number) => api.get<GridRow[]>(`/v1/projects/${id}/activities`).then(({ data }) => data),
  createActivity: (projectId: number, payload: { nombre: string; descripcion: string; fecha_inicio: string; hora_inicio: string; fecha_final: string; hora_final: string }) => api.post(`/v1/projects/${projectId}/activities`, payload).then(({ data }) => data),
  risks: (id: number) => api.get<GridRow[]>(`/v1/projects/${id}/risks`).then(({ data }) => data),
  createRisk: (projectId: number, nombre: string) => api.post(`/v1/projects/${projectId}/risks`, { nombre }).then(({ data }) => data),
}

export const initiativeApi = {
  status: (id: number, uuid: string) => api.get('/getEstadoSolicitudValidacion', { params: { id }, headers: { 'X-UUID': uuid } }).then(({ data }) => data),
  saveValidation: (id: number, data: Record<string, unknown>, uuid: string) => api.post('/guardarValidacionIniciativa', { id, data }, { headers: { 'X-UUID': uuid } }).then(({ data: response }) => response),
  saveFollowup: (data: Record<string, unknown>, uuid: string) => api.post('/guardarValidacionIniciativaSeguimiento', { data }, { headers: { 'X-UUID': uuid } }).then(({ data: response }) => response),
}

export const policyApi = {
  current: () => api.get<Record<string, unknown>[]>('/getPoliticasSeguridad').then(({ data }) => data),
}

export function logout() {
  localStorage.removeItem('ikaros.token')
  localStorage.removeItem('ikaros.session')
}

export default api
