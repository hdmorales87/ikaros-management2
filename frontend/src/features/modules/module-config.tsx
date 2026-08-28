export type ModuleDefinition = {
  path: string
  title: string
  table: string
  description: string
  columns: string[]
  filters?: Record<string, unknown>
  permission?: number
  module?: number
}

export const moduleDefinitions: ModuleDefinition[] = [
  { path: 'incidencias', title: 'Incidencias', permission: 2, module: 2, table: 'incidencias', description: 'Solicitudes que requieren atención.', columns: ['id', 'asunto', 'descripcion', 'estado', 'prioridad'] },
  { path: 'problemas', title: 'Problemas', permission: 5, module: 3, table: 'incidencias', description: 'Incidencias convertidas en problemas.', columns: ['id', 'asunto', 'descripcion', 'estado', 'prioridad'], filters: { problema: true } },
  { path: 'servicios', title: 'Servicios', permission: 7, module: 4, table: 'servicios', description: 'Solicitudes de servicio.', columns: ['id', 'asunto', 'descripcion', 'estado', 'prioridad'] },
  { path: 'conocimiento', title: 'Conocimiento', permission: 10, module: 5, table: 'conocimiento', description: 'Soluciones disponibles para consulta.', columns: ['id', 'tema', 'solucion'] },
  { path: 'activos', title: 'Activos', permission: 11, module: 6, table: 'activos', description: 'Inventario de activos.', columns: ['id', 'codigo', 'nombre', 'marca', 'activo'] },
  { path: 'proyectos', title: 'Proyectos', permission: 88, module: 14, table: 'proyectos', description: 'Proyectos de la organización.', columns: ['id', 'codigo', 'nombre', 'estado'] },
  { path: 'proyectos/actividades', title: 'Actividades de proyectos', permission: 88, module: 14, table: 'proyectos_actividades', description: 'Actividades planificadas.', columns: ['id', 'id_proyecto', 'nombre', 'fecha_inicio', 'fecha_final'] },
  { path: 'proyectos/subactividades', title: 'Subactividades', permission: 88, module: 14, table: 'proyectos_subactividades', description: 'Subactividades planificadas.', columns: ['id', 'id_actividad', 'nombre', 'fecha_inicio', 'fecha_final'] },
  { path: 'proyectos/riesgos', title: 'Riesgos de proyectos', permission: 88, module: 14, table: 'proyectos_riesgos', description: 'Riesgos registrados.', columns: ['id', 'id_proyecto', 'nombre', 'descripcion', 'mitigacion'] },
  { path: 'iniciativas', title: 'Iniciativas', permission: 104, module: 16, table: 'iniciativas', description: 'Iniciativas de mejora.', columns: ['id', 'codigo', 'nombre', 'estado'] },
  { path: 'iniciativas/comites', title: 'Comités de iniciativas', permission: 104, module: 16, table: 'iniciativas_comites', description: 'Comités de aprobación.', columns: ['id', 'id_iniciativa', 'nombre', 'estado_validacion'] },
  { path: 'capacitaciones', title: 'Capacitaciones', permission: 72, module: 13, table: 'capacitaciones', description: 'Actividades de formación.', columns: ['id', 'nombre', 'instructor', 'lugar', 'fecha_inicio'] },
  { path: 'horas', title: 'Registro de horas', permission: 91, module: 15, table: 'proyectos_horas_registro', description: 'Horas planeadas y ejecutadas.', columns: ['id', 'id_colaborador', 'id_proyecto'] },
  { path: 'reportes', title: 'Reportes', permission: 18, module: 8, table: 'log', description: 'Actividad registrada del sistema.', columns: ['id', 'tabla', 'accion', 'fecha', 'id_usuario'] },
  { path: 'config/roles', title: 'Roles', permission: 32, table: 'roles', description: 'Roles configurados.', columns: ['id', 'nombre', 'descripcion', 'activo'] },
  { path: 'config/areas', title: 'Áreas de servicio', permission: 34, table: 'areas_servicio', description: 'Áreas que atienden solicitudes.', columns: ['id', 'nombre', 'capacidad_atencion', 'activo'] },
  { path: 'config/departamentos', title: 'Departamentos', permission: 55, table: 'departamentos', description: 'Departamentos de la organización.', columns: ['id', 'nombre', 'activo'] },
  { path: 'config/categorias', title: 'Categorías de servicio', permission: 34, table: 'areas_servicio_categorias', description: 'Categorías de servicio.', columns: ['id', 'id_area', 'nombre', 'activo'] },
  { path: 'config/subcategorias', title: 'Subcategorías de servicio', permission: 34, table: 'areas_servicio_subcategorias', description: 'Subcategorías de servicio.', columns: ['id', 'id_categoria', 'nombre', 'activo'] },
  { path: 'config/tipos-activo', title: 'Tipos de activo', permission: 33, module: 6, table: 'activos_tipos', description: 'Tipos de activos del inventario.', columns: ['id', 'nombre', 'activo'] },
  { path: 'config/monedas', title: 'Monedas', permission: 57, table: 'monedas', description: 'Monedas disponibles.', columns: ['id', 'nombre', 'activo'] },
  { path: 'config/tipos-documentacion', title: 'Tipos de documentación', permission: 56, table: 'tipos_documentacion', description: 'Tipos de documentos.', columns: ['id', 'nombre', 'activo'] },
  { path: 'config/extensiones', title: 'Extensiones', permission: 87, table: 'extensiones', description: 'Extensiones de archivo permitidas.', columns: ['id', 'nombre', 'activo'] },
  { path: 'config/empresa', title: 'Empresa', permission: 19, table: 'companies', description: 'Información de la empresa activa.', columns: ['razon_social', 'documento', 'tipo_licencia'] },
  { path: 'config/empresa/modulos', title: 'Módulos de empresa', permission: 19, table: 'companies_modulos', description: 'Funcionalidades activas para la empresa.', columns: ['id_modulo'] },
  { path: 'config/smtp', title: 'SMTP', permission: 21, table: 'smtp', description: 'Prueba de configuración de correo.', columns: ['servidor', 'puerto', 'seguridad_smtp', 'correo'] },
  { path: 'config/politicas', title: 'Políticas de seguridad', permission: 36, table: 'politicas_seguridad', description: 'Reglas activas de seguridad.', columns: ['id', 'password_vencimiento', 'intentos_login', 'activo'] },
  { path: 'config/dias-festivos', title: 'Días festivos', permission: 96, table: 'dias_festivos', description: 'Calendario de días no laborables.', columns: ['id', 'fecha', 'descripcion', 'activo'] },
  { path: 'config/riesgos/probabilidad', title: 'Probabilidad de riesgos', permission: 102, table: 'proyectos_riesgos_probabilidad', description: 'Escala de probabilidad de riesgos.', columns: ['id', 'nombre', 'activo'] },
  { path: 'config/riesgos/impacto', title: 'Impacto de riesgos', permission: 103, table: 'proyectos_riesgos_impacto', description: 'Escala de impacto de riesgos.', columns: ['id', 'nombre', 'activo'] },
]
