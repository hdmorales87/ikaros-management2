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
- Activos con relaciones operativas, ficha técnica y adjuntos.
- Proyectos, actividades, subactividades, riesgos, Gantt editable y captura de línea base con histórico.
- Capacitaciones, adjuntos, inscripción y confirmación de asistencia.
- Iniciativas, comités, formularios de planificación y validaciones.
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

- Dashboards analíticos completos por módulo y reportes adicionales con filtros de fecha, SLA, área y responsable. Confirmar si siguen siendo necesarios formatos XLSX/PDF.
- Gestión de aprobadores de comités, edición completa de iniciativas/comités y trazabilidad visual de validaciones.
- Formularios especializados para los catálogos administrativos que aún usan CRUD genérico y tienen reglas de negocio propias.
- Ubicaciones, encuestas administrativas y plantillas avanzadas de notificaciones.

## Limitaciones conocidas

- Las migraciones de línea base solo verifican tablas existentes y no sustituyen los dumps ni deben ejecutarse automáticamente en producción.
- IMAP tiene prueba unitaria de regla, remitente, creación y asignación; falta cobertura contra servidor IMAP simulado o real.
- Las pruebas de integración requieren una copia MySQL del esquema Ikaros para comprobar relaciones, triggers y datos de tenant reales.

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