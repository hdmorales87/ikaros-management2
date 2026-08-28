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
  if (token) config.headers.Authorization = `Bearer ${token}`
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
  userData?: { id?: number; nombre?: string; apellido?: string; email?: string }
  companyData?: { razon_social?: string }
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

export async function checkCompany(documento: string, instalation: string): Promise<{ uuid: string; razon_social: string }> {
  const { data } = await api.post('/checkCompany', { documento, instalation })
  if (!data?.uuid) throw new Error('Empresa no encontrada.')
  localStorage.setItem('ikaros.uuid', data.uuid)
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

export const assetApi = {
  generateCode: (idActivo: number) => api.post('/generarCodigoActivo', { idActivo }).then(({ data }) => data),
}

export const fileApi = {
  upload: (file: File, folder: string, id: number) => { const form = new FormData(); form.append('file', file); form.append('folder', folder); form.append('id', String(id)); return api.post('/uploaderFile', form, { headers: { 'Content-Type': 'multipart/form-data' } }).then(({ data }) => data) },
  list: (folder: string) => api.get<string[]>('/files', { params: { folder } }).then(({ data }) => data),
  download: (uuid: string, folder: string, file: string) => api.get(`/downloadFile/${encodeURIComponent(uuid)}/${encodeURIComponent(folder)}/${encodeURIComponent(file)}`, { responseType: 'blob' }).then(({ data }) => data as Blob),
}

export type RequestCatalog = { id: number; nombre: string }

export const requestApi = {
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
  notifyValidation: (id: number, tipo: string) => api.post('/notificarValidacionHoras', { id, tipo }).then(({ data }) => data),
  notifyConfirmation: (ids: number[], tipo: string) => api.post('/notificarConfirmacionHoras', { ids: JSON.stringify(ids), tipo }).then(({ data }) => data),
}

export type GridRow = Record<string, unknown> & { id?: number }

function encodePayload(value: unknown): string {
  const bytes = new TextEncoder().encode(JSON.stringify(value))
  let binary = ''
  bytes.forEach((byte) => { binary += String.fromCharCode(byte) })
  return btoa(binary)
}

export async function getGridRows(table: string, searchWord = '', filters: Record<string, unknown> = {}, searchFields: ReadonlyArray<string> = ['nombre']): Promise<GridRow[]> {
  const payload = encodePayload({ searchWord, filters, showRecords: 100, offsetRecord: 0, date1: '', date2: '', sqlParams: { fieldSearch: searchFields }, tabla: table, mode: 'rows' })
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
}

export const notificationApi = {
  initiative: (id: number) => api.post('/notificarValidacionIniciativa', { id }).then(({ data }) => data),
  committee: (id: number, idUser: number) => api.post('/notificarComite', { id, idUser }).then(({ data }) => data),
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
