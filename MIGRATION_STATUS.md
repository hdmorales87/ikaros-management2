# Estado de migración Ikaros Management

## Base técnica

- Backend Laravel 13 con controladores, capa de servicios, JWT, autorización por permisos y datagrid con lista blanca.
- Frontend React 19, TypeScript, Vite, TanStack Query, Axios y React Router.
- Esquemas de referencia existentes: `ikaros-management/ikarosof_management_acceso.sql` e `ikaros-management/ikarosof_cliente.sql`.
- Migraciones de línea base no destructivas presentes en `backend/database/migrations/`. No se han aplicado a producción.
- `composer setup` y `post-create-project-cmd` no ejecutan migraciones.

## Cobertura funcional

- Autenticación, recuperación, activación, compañías, módulos, usuarios, roles y permisos.
- Solicitudes, SLA, asignación, trazabilidad, encuestas, terceros, conocimiento, archivos e importación Excel.
- Activos con relaciones operativas (tipo, proveedor, estado, asignación, compra), ficha técnica y adjuntos.
- Proyectos, actividades, subactividades, riesgos, Gantt editable y captura de línea base con histórico.
- Capacitaciones con planificación (instructor, intensidad, fechas, lugar, observaciones), adjuntos, inscripción y confirmación de asistencia.
- Iniciativas con planificación completa (presupuesto, tiempo, propietario, beneficios, escenarios).
- Comités de iniciativas con creación, gestión de aprobadores y notificación a aprobadores activos.
- Horas de proyecto con confirmación persistente, validador, fecha, trazabilidad y estado de validación visible.
- Ubicaciones por departamento, gestionadas desde configuración.
- Encuestas de satisfacción para solicitudes: administración de preguntas.
- Encuestas de terceros: preguntas diferenciadas por cliente/proveedor, administración desde configuración.
- Terceros con puntajes de satisfacción visibles, invitaciones solo a clientes.
- Reporte operativo de solicitudes con exportación CSV.
- SMTP y notificaciones principales.
- IMAP tenant-aware: prueba de conexión, reglas, sincronización de mensajes no leídos, creación/asignación de solicitudes y adjuntos con cuota/extensiones configurables.

## Validación actual

- 71 rutas API registradas, sin endpoints funcionales ausentes respecto al backend legacy auditado.
- PHPUnit: 9 pruebas correctas, 11 aserciones. Incluye seguridad API, procesamiento IMAP y restricciones de fechas proyecto-actividad-subactividad.
- Frontend: `npm run build` y `npm run lint` correctos después de las últimas pantallas.
- Scheduler: `imap:sync` programado cada cinco minutos; `CACHE_STORE=file` permite validar el scheduler sin MySQL local.

## Pendientes para culminar

### Validación e infraestructura

- Restaurar ambos dumps en una instancia MySQL de ensayo y probar los flujos tenant sin ejecutar las migraciones de línea base en producción.
- Probar IMAP con cuenta real o servidor simulado: conexión, correos inválidos, reintentos, adjuntos rechazados y cuota.
- Validar SMTP, CORS, `storage:link` y scheduler en el entorno de despliegue.
- Añadir pruebas end-to-end por rol para login, permisos, solicitudes, proyectos, adjuntos, IMAP y encuestas.

### Producto

- Gestión de contratos de terceros (cliente/proveedor), pagos, planes de pago y plan de notificaciones.
- Dashboards analíticos completos por módulo y reportes adicionales con filtros de fecha, SLA, área y responsable.
- Completar la gestión de aprobadores de comités con trazabilidad visual de validaciones.
- Formularios especializados para catálogos administrativos que tengan reglas de negocio adicionales.
- Plantillas avanzadas de notificaciones y envío de correos personalizados.

## Limitaciones conocidas

- Las migraciones de línea base solo verifican tablas existentes y no sustituyen los dumps ni deben ejecutarse automáticamente en producción.
- IMAP tiene prueba unitaria de regla, remitente, creación y asignación; falta cobertura contra servidor IMAP simulado o real.
- Las pruebas de integración requieren una copia MySQL del esquema Ikaros para comprobar relaciones, triggers y datos de tenant reales.
- Contratos, pagos y planes de pago aún no tienen interfaz administrativa migrante; requieren formularios especializados por el volumen de campos relacionados.

## Resumen de la sesión

Se completaron los siguientes módulos operativos:

- **Comités**: Selección de iniciativa, creación, edición y desactivación; gestión de aprobadores con bloqueo de duplicados.
- **Horas**: Persistencia de confirmación, validador, fecha y seguimiento; bloqueo de duplicadas en interfaz.
- **Ubicaciones**: Gestión de ubicaciones por departamento desde configuración administrativa.
- **Encuesta de satisfacción**: Configuración de preguntas activas desde configuración.
- **Encuesta de terceros**: Configuración diferenciada de preguntas por cliente/proveedor.
- **Terceros**: Corrección para no enviar encuestas de cliente a proveedores; muestra puntajes correspondientes.

Todos los cambios mantienen la integridad del esquema existente sin aplicar migraciones a producción.

## Comandos de validación

Desde `frontend/`:

```bash
npm run build
npm run lint
```

Desde `backend/`:

```bash
php artisan test
php artisan route:list --path=api
php artisan schedule:list
```