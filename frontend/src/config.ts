export type ModuleConfig = {
  tab: string
  titulo: string
  subtitulo: string
  icono: string
  componente: string
  permiso: number
  modulo?: number
}

export type AppConfig = {
  mainPath: string
  instalacion: string
  uuidIMA?: string
  applicationID?: string
  applicationKEY?: string
  apiRoute?: string
  cloudRoute?: string
  modulos: ModuleConfig[]
  control_panel: ModuleConfig[]
}

const fallback: AppConfig = {
  mainPath: '',
  instalacion: 'local',
  modulos: [],
  control_panel: [],
}

let cachedConfig: AppConfig | null = null

export async function getConfig(): Promise<AppConfig> {
  if (cachedConfig) return cachedConfig
  try {
    const response = await fetch(`${import.meta.env.BASE_URL}configuration.json`)
    if (!response.ok) throw new Error(`configuration.json: ${response.status}`)
    cachedConfig = { ...fallback, ...await response.json() }
  } catch {
    cachedConfig = fallback
  }
  return cachedConfig ?? fallback
}
