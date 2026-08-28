# Estado de migración Ikaros Management

## Base técnica

- Backend Laravel 13 con controladores, capa de servicios, JWT, autorización por permisos y datagrid con lista blanca.
- Frontend React 19, TypeScript, Vite, TanStack Query, Axios y React Router.
- Esquemas de referencia existentes: ikaros-management/ikarosof_management_acceso.sql e ikaros-management/ikarosof_cliente.sql.
- Migraciones de línea base no destructivas presentes en backend/database/migrations/. No se han aplicado a producción.
- composer setup y post-create-project-cmd no ejecutan migraciones.

## Cobertura funcional

- Autenticación, recuperación, activación, compañías, módulos, usuarios, roles y permisos.
- Solicitudes, SLA, asignación, trazabilidad, encuestas, terceros, conocimiento, archivos e importación Excel.
- Activos con relaciones operativas (tipo, proveedor, estado, asignación, compra), ficha técnica y adjuntos.
- Proyectos, actividades, subactividades, riesgos, Gantt editable y captura de línea base con histórico.
- Capacitaciones con planificación (instructor, intensidad, fechas, lugar, observaciones), adjuntos, inscripción y confirmación de asistencia.
- Iniciativas con planificación completa (presupuesto, tiempo, propietario, beneficios, escenarios).
- Comités de iniciativas con creación, gestión de aprobadores, notificación y trazabilidad por aprobador.
- Horas de proyecto con confirmación persistente, validador, fecha, trazabilidad y estado de validación visible.
- Ubicaciones por departamento, gestionadas desde configuración.
- Encuestas de satisfacción para solicitudes: administración de preguntas.
- Encuestas de terceros: preguntas diferenciadas por cliente/proveedor, administración desde configuración.
- Terceros con puntajes de satisfacción visibles, invitaciones solo a clientes.
- Contratos de clientes y proveedores con creación, edición, pagos, adjuntos, estados y planes de pago.
- Notificaciones de contratos por vencimiento, renovación y pagos.
- Dashboard operativo con resumen general por módulo.
- Reporte operativo de solicitudes con exportación CSV.
- SMTP y notificaciones principales.
- IMAP tenant-aware: prueba de conexión, reglas, sincronización y clasificación de mensajes no leídos.

## Validación actual

- 71 rutas API registradas, sin endpoints funcionales ausentes respecto al backend legacy auditado.
- PHPUnit: 9 pruebas correctas, 11 aserciones. Incluye seguridad API, procesamiento IMAP y restricciones de fechas proyecto-actividad-subactividad.
- Validación estática del editor: los archivos modificados en esta sesión muestran 0 errores en TypeScript y PHP del workspace.
- No se ha podido ejecutar build/lint real desde terminal porque los terminales de la sesión se bloquean en prompt de PowerShell, por lo que la comprobación efectiva es la diagnosticada por el editor.
- Scheduler: imap:sync programado cada cinco minutos; CACHE_STORE=file permite validar el scheduler sin MySQL local.

## Pendientes para culminar

### Validación e infraestructura

- Restaurar ambos dumps en una instancia MySQL de ensayo y probar los flujos tenant reales sin ejecutar migraciones de línea base en producción.
- Probar IMAP con cuenta real o un servidor simulado: conexión, correos inválidos, reintentos, adjuntos rechazados y cuota.
- Validar SMTP, CORS, storage:link y schedule en el entorno de despliegue.
- Añadir pruebas end-to-end por rol para login, permisos, solicitudes, proyectos, adjuntos, IMAP y encuestas.

### Producto

- Dashboards analíticos completos por módulo y reportes adicionales con filtros de fecha, SLA, área y responsable.
- Formularios especializados para catálogos administrativos con reglas de negocio más complejas.
- Plantillas avanzadas de notificaciones y envío de correos personalizados.

## Limitaciones conocidas

- Las migraciones de línea base solo verifican tablas existentes y no sustituyen los dumps ni deben ejecutarse automáticamente en producción.
- IMAP requiere validación real con servidor o entorno simulado; la comprobación actual está limitada al diagnóstico del editor y la capa de servicio ya implementada.
- Las pruebas de integración requieren una copia MySQL del esquema Ikaros para comprobar relaciones, triggers y datos de tenant reales.
- La validación final del frontend se encuentra bloqueada por la terminal del entorno, no por errores de código en la aplicación.

## Resumen de la sesión

Se completaron los siguientes módulos operativos:

- Comités: selección de iniciativa, creación, edición, desactivación y gestión de aprobadores con trazabilidad.
- Horas: confirmación persistente, validador, fecha y seguimiento; bloqueo de transiciones inválidas en la interfaz.
- Ubicaciones: gestión por departamento desde configuración administrativa.
- Encuesta de satisfacción: configuración de preguntas activas.
- Encuesta de terceros: preguntas diferenciadas por cliente/proveedor.
- Terceros: corrección de flujo de invitaciones y puntajes por tipo.
- Contratos: gestión de clientes y proveedores, estados, planes de pago, pagos, adjuntos y notificaciones.
- Dashboard: resumen operativo por módulo.

Todos los cambios mantienen la integridad del esquema existente sin aplicar migraciones a producción.

## Comandos de validación recomendados

Desde frontend/:

- npm run build
- npm run lint

Desde backend/:

- php artisan test
- php artisan route:list --path=api
- php artisan schedule:list

Nota: en este entorno los comandos reales se vieron bloqueados por el terminal, por lo que la evidencia disponible corresponde a la validación del editor.