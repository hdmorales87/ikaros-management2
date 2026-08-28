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
  { path: 'proyectos/gantt', title: 'Gantt de proyectos', permission: 88, module: 14, table: 'proyectos_actividades', description: 'Planificación visual de actividades.', columns: ['id', 'id_proyecto', 'nombre', 'fecha_inicio', 'fecha_final'] },
  { path: 'iniciativas', title: 'Iniciativas', permission: 104, module: 16, table: 'iniciativas', description: 'Iniciativas de mejora.', columns: ['id', 'codigo', 'nombre', 'estado'] },
  { path: 'iniciativas/comites', title: 'Comités de iniciativas', permission: 104, module: 16, table: 'iniciativas_comites', description: 'Comités de aprobación.', columns: ['id', 'id_iniciativa', 'nombre', 'estado_validacion'] },
  { path: 'iniciativas/trazabilidad', title: 'Trazabilidad de aprobadores', permission: 104, module: 16, table: 'iniciativas_comites_aprobadores', description: 'Seguimiento del estado de cada aprobador.', columns: ['id', 'id_comite', 'id_user', 'estado_validacion'] },
  { path: 'capacitaciones', title: 'Capacitaciones', permission: 72, module: 13, table: 'capacitaciones', description: 'Actividades de formación.', columns: ['id', 'nombre', 'instructor', 'lugar', 'fecha_inicio'] },
  { path: 'capacitaciones/asistentes', title: 'Asistentes de capacitación', permission: 72, module: 13, table: 'capacitaciones_usuarios', description: 'Usuarios inscritos en capacitaciones.', columns: ['id', 'id_capacitacion', 'id_usuario'] },
  { path: 'horas', title: 'Registro de horas', permission: 91, module: 15, table: 'proyectos_horas_registro', description: 'Horas planeadas y ejecutadas.', columns: ['id', 'id_colaborador', 'id_proyecto'] },
  { path: 'reportes', title: 'Reportes', permission: 18, module: 8, table: 'log', description: 'Actividad registrada del sistema.', columns: ['id', 'tabla', 'accion', 'fecha', 'id_usuario'] },
  { path: 'config/roles', title: 'Roles', permission: 32, table: 'roles', description: 'Roles configurados.', columns: ['id', 'nombre', 'descripcion', 'activo'] },
  { path: 'config/areas', title: 'Áreas de servicio', permission: 34, table: 'areas_servicio', description: 'Áreas que atienden solicitudes.', columns: ['id', 'nombre', 'capacidad_atencion', 'activo'] },
  { path: 'config/departamentos', title: 'Departamentos', permission: 55, table: 'departamentos', description: 'Departamentos de la organización.', columns: ['id', 'nombre', 'activo'] },
  { path: 'config/ubicaciones', title: 'Ubicaciones', permission: 55, table: 'departamentos_ubicaciones', description: 'Ubicaciones por departamento.', columns: ['id', 'id_departamento', 'nombre', 'activo'] },
  { path: 'config/categorias', title: 'Categorías de servicio', permission: 34, table: 'areas_servicio_categorias', description: 'Categorías de servicio.', columns: ['id', 'id_area', 'nombre', 'activo'] },
  { path: 'config/subcategorias', title: 'Subcategorías de servicio', permission: 34, table: 'areas_servicio_subcategorias', description: 'Subcategorías de servicio.', columns: ['id', 'id_categoria', 'nombre', 'activo'] },
  { path: 'config/tipos-activo', title: 'Tipos de activo', permission: 33, module: 6, table: 'activos_tipos', description: 'Tipos de activos del inventario.', columns: ['id', 'nombre', 'activo'] },
  { path: 'config/monedas', title: 'Monedas', permission: 57, table: 'monedas', description: 'Monedas disponibles.', columns: ['id', 'nombre', 'activo'] },
  { path: 'config/tipos-documentacion', title: 'Tipos de documentación', permission: 56, table: 'tipos_documentacion', description: 'Tipos de documentos.', columns: ['id', 'nombre', 'activo'] },
  { path: 'config/extensiones', title: 'Extensiones', permission: 87, table: 'extensiones', description: 'Extensiones de archivo permitidas.', columns: ['id', 'nombre', 'activo'] },
  { path: 'clientes', title: 'Clientes', permission: 62, table: 'terceros', description: 'Clientes de la organización.', columns: ['id', 'documento', 'razon_social', 'email'] },
  { path: 'clientes/contratos', title: 'Contratos de clientes', permission: 62, table: 'terceros_contratos', description: 'Contratos asociados a clientes.', columns: ['id', 'nombre', 'objeto_contrato', 'monto', 'fecha_inicio', 'fecha_vencimiento'], filters: { tipo: 'cliente' } },
  { path: 'proveedores', title: 'Proveedores', permission: 14, table: 'terceros', description: 'Proveedores de la organización.', columns: ['id', 'documento', 'razon_social', 'email'] },
  { path: 'proveedores/contratos', title: 'Contratos de proveedores', permission: 14, table: 'terceros_contratos', description: 'Contratos asociados a proveedores.', columns: ['id', 'nombre', 'objeto_contrato', 'monto', 'fecha_inicio', 'fecha_vencimiento'], filters: { tipo: 'proveedor' } },
  { path: 'config/empresa', title: 'Empresa', permission: 19, table: 'companies', description: 'Información de la empresa activa.', columns: ['razon_social', 'documento', 'tipo_licencia'] },
  { path: 'config/empresa/modulos', title: 'Módulos de empresa', permission: 19, table: 'companies_modulos', description: 'Funcionalidades activas para la empresa.', columns: ['id_modulo'] },
  { path: 'config/smtp', title: 'SMTP', permission: 21, table: 'smtp', description: 'Prueba de configuración de correo.', columns: ['servidor', 'puerto', 'seguridad_smtp', 'correo'] },
  { path: 'config/imap', title: 'IMAP', permission: 51, table: 'imap', description: 'Configuración de correo entrante.', columns: ['id', 'servidor', 'correo', 'puerto', 'tls'] },
  { path: 'config/politicas', title: 'Políticas de seguridad', permission: 36, table: 'politicas_seguridad', description: 'Reglas activas de seguridad.', columns: ['id', 'password_vencimiento', 'intentos_login', 'activo'] },
  { path: 'config/encuesta-satisfaccion', title: 'Encuesta de satisfacción', permission: 36, table: 'encuesta_satisfaccion_preguntas', description: 'Preguntas activas para las encuestas de solicitudes.', columns: ['id', 'nombre', 'activo'] },
  { path: 'config/encuesta-terceros', title: 'Encuesta de terceros', permission: 36, table: 'encuesta_terceros_preguntas', description: 'Preguntas para encuestas de clientes y proveedores.', columns: ['id', 'tipo', 'nombre', 'activo'] },
  { path: 'config/contratos-estados', title: 'Estados de contratos', permission: 36, table: 'terceros_contratos_estados', description: 'Estados disponibles para contratos de terceros.', columns: ['id', 'nombre', 'activo'] },
  { path: 'config/contratos-planes-pago', title: 'Planes de pago', permission: 36, table: 'terceros_contratos_planes_pagos', description: 'Planes de pago disponibles para contratos de terceros.', columns: ['id', 'nombre', 'dias', 'activo'] },
  { path: 'config/contratos-notificaciones', title: 'Notificaciones de contratos', permission: 36, table: 'terceros_contratos_notificaciones', description: 'Seguimiento de avisos por vencimiento, renovación y pagos.', columns: ['id', 'id_contrato', 'primera_notificacion_vencimiento', 'primera_notificacion_renovacion', 'primera_notificacion_pagos', 'activo'] },
  { path: 'config/dias-festivos', title: 'Días festivos', permission: 96, table: 'dias_festivos', description: 'Calendario de días no laborables.', columns: ['id', 'fecha', 'descripcion', 'activo'] },
  { path: 'config/riesgos/probabilidad', title: 'Probabilidad de riesgos', permission: 102, table: 'proyectos_riesgos_probabilidad', description: 'Escala de probabilidad de riesgos.', columns: ['id', 'nombre', 'activo'] },
  { path: 'config/riesgos/impacto', title: 'Impacto de riesgos', permission: 103, table: 'proyectos_riesgos_impacto', description: 'Escala de impacto de riesgos.', columns: ['id', 'nombre', 'activo'] },
]
