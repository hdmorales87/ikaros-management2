import { requestApi } from '../../api'

export type RequestFormValues = {
  modulo: string
  urgencia: number
  impacto: number
  area: number
  categoria: number
  subcategoria: number
  asunto: string
  descripcion: string
}

export type CreateRequestResponse = {
  id: number
  assignment?: { msg?: string }
}

export const requestQueries = {
  areas: (module: string) => requestApi.areas(module),
  categories: (area: number) => requestApi.categories(area),
  subcategories: (category: number) => requestApi.subcategories(category),
  urgencies: () => requestApi.urgencies(),
  impacts: () => requestApi.impacts(),
  create: (values: RequestFormValues) => requestApi.create(values) as Promise<CreateRequestResponse>,
}
