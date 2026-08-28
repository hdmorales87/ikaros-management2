# Estado de migración Ikaros Management

## Backend Laravel 13

Los endpoints funcionales del backend2 están disponibles en `backend/routes/api.php`, organizados en controladores y servicios. Las rutas antiguas de caché (`clear-cache`, `optimize`, `route-clear`, `route-cache`, `view-clear`, `config-clear`, `config-cache`) no se exponen por HTTP por seguridad; deben ejecutarse como comandos Artisan.

Cubierto:

- autenticación JWT, recuperación y activación
- compañías, módulos y permisos
- usuarios y roles
- solicitudes, estados, asignación y SLA
- encuestas y terceros
- conocimiento
- activos y fichas técnicas
- archivos e importación Excel
- datagrid con lista blanca
- SMTP y notificaciones principales
- IMAP tenant-aware: prueba de conexión, sincronización de mensajes no leídos, reglas, creación de solicitudes y adjuntos
- autorización backend por permisos para usuarios, roles y módulos de empresa

## Frontend React 19

El frontend independiente está en `frontend/` y usa React 19, TypeScript, Vite, React Query, Axios y React Router.

Cubierto funcionalmente:

- login y recuperación de contraseña
- sesión, permisos y módulos habilitados
- dashboard operativo
- usuarios, roles y permisos
- solicitudes, catálogos, asignación y gestión
- detalle, trazabilidad y adjuntos
- conocimiento
- activos
- proyectos, actividades y riesgos
- capacitaciones
- horas
- empresas, SMTP y políticas
- configuración IMAP: cuentas, prueba de conexión y CRUD de reglas sin exponer contraseñas
- catálogos administrativos
- iniciativas, comités y validación de iniciativas
- configuración runtime desde `configuration.json`
- manejo global de errores y navegación protegida

## Estado actual

- Backend: 71 rutas API registradas y sin endpoints funcionales ausentes respecto a `backend2`. La suite Feature tiene 6 pruebas correctas.
- IMAP: `imap:sync` procesa `INBOX` no leído por tenant cada cinco minutos, valida `documento:palabra_clave:asunto`, aplica reglas, crea y asigna solicitudes, y marca cada correo como leído solamente después de procesarlo correctamente. Los adjuntos se almacenan por tenant con límite, extensiones permitidas y cuota configurables.
- Scheduler: `CACHE_STORE=file` permite ejecutar los mutex internos locales sin requerir la base MySQL. El scheduler se validó con `php artisan schedule:list`.
- Esquema: los dumps de referencia están en `ikaros-management/ikarosof_management_acceso.sql` e `ikaros-management/ikarosof_cliente.sql`. Se añadieron líneas base Laravel no destructivas para registrar y verificar el esquema existente; no se han aplicado sobre producción.
- Despliegue: `composer setup` y `post-create-project-cmd` no ejecutan migraciones, para impedir cambios automáticos sobre las bases existentes.
- Frontend: las features principales están conectadas al backend Laravel. La compilación y el lint global son correctos.
- Las rutas administrativas de caché del legado no se exponen por HTTP y deben ejecutarse con Artisan.

## Trabajo pendiente

Las siguientes pantallas del frontend legacy aún están representadas por vistas genéricas o requieren una migración visual especializada:

- dashboards analíticos completos por módulo
- edición avanzada de proyectos, actividades y subactividades
- Gantt y líneas base
- formularios completos de activos y fichas técnicas
- gestión visual avanzada de capacitaciones
- comités e iniciativas con sus formularios y aprobaciones
- notificaciones avanzadas, encuestas administrativas y ubicaciones
- reportes especializados y exportaciones
- pruebas de integración IMAP con cuentas y bases tenant reales
- pruebas end-to-end contra bases tenant reales

## Próximo bloque recomendado

El siguiente bloque de migración profunda es proyectos: actividades y subactividades con formularios dependientes, responsables, adjuntos y restricciones de fechas respecto al proyecto. Después deben migrarse Gantt y reportes especializados con endpoints dedicados, porque una grilla genérica no conserva esas reglas visuales. Antes de una validación funcional de IMAP se deben crear pruebas con mensajes simulados y cuentas tenant de prueba.

## Limitaciones conocidas

- Las vistas de proyectos, actividades, riesgos, capacitaciones y algunos catálogos usan componentes reutilizables de consulta/CRUD; todavía no sustituyen todos los formularios y ventanas del legado.
- La selección de permisos y módulos carga la configuración actual, pero requiere pruebas con usuarios de cada rol para verificar matrices reales de autorización.
- Los correos se envían con configuración SMTP tenant, aunque las plantillas de negocio avanzadas aún deben validarse con usuarios destinatarios reales.
- La sincronización IMAP tiene una prueba unitaria para regla, remitente, creación y asignación. Aún no hay pruebas con un servidor IMAP simulado ni cuentas tenant reales. Los mensajes que no se puedan procesar permanecen no leídos y el error se registra para reintento.
- Los adjuntos IMAP se guardan en `storage/app/public/imap/<uuid>/`; se debe ejecutar `php artisan storage:link` donde corresponda exponerlos públicamente.
- El esquema tenant no está representado por migraciones locales; las pruebas de integración necesitan bases MySQL de prueba con el esquema de Ikaros.
- Las migraciones de línea base validan la presencia de tablas, pero no sustituyen los dumps ni deben aplicarse de forma automática a producción existente.
- Hay 7 pruebas automatizadas para autenticación, validación, protección de API y procesamiento IMAP.

## Validación

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
