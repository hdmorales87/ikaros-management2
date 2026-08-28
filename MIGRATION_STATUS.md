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
- catálogos administrativos
- iniciativas, comités y validación de iniciativas
- configuración runtime desde `configuration.json`
- manejo global de errores y navegación protegida

## Estado actual

- Backend: 71 rutas API registradas y sin endpoints funcionales ausentes respecto a `backend2`.
- Frontend: compilación y lint correctos; las features principales están conectadas al backend Laravel.
- Las rutas administrativas de caché del legado no se exponen por HTTP y deben ejecutarse con Artisan.

## Trabajo pendiente

Las siguientes pantallas del frontend legacy aún están representadas por vistas genéricas o requieren una migración visual especializada:

- dashboards analíticos completos por módulo
- edición avanzada de proyectos, actividades y subactividades
- Gantt y líneas base
- formularios completos de activos y fichas técnicas
- gestión visual avanzada de capacitaciones
- comités e iniciativas con sus formularios y aprobaciones
- configuración IMAP, notificaciones avanzadas, encuestas administrativas y ubicaciones
- reportes especializados y exportaciones
- pruebas end-to-end contra bases tenant reales

## Próximo bloque recomendado

El siguiente bloque de migración profunda es proyectos: actividades y subactividades con formularios dependientes, responsables, adjuntos y restricciones de fechas respecto al proyecto. Después deben migrarse Gantt y reportes especializados con endpoints dedicados, porque una grilla genérica no conserva esas reglas visuales.

## Limitaciones conocidas

- Las vistas de proyectos, actividades, riesgos, capacitaciones y algunos catálogos usan componentes reutilizables de consulta/CRUD; todavía no sustituyen todos los formularios y ventanas del legado.
- La selección de permisos y módulos carga la configuración actual, pero requiere pruebas con usuarios de cada rol para verificar matrices reales de autorización.
- Los correos se envían con configuración SMTP tenant, aunque las plantillas de negocio avanzadas aún deben validarse con usuarios destinatarios reales.
- El esquema tenant no está representado por migraciones locales; las pruebas de integración necesitan bases MySQL de prueba con el esquema de Ikaros.
- Hay 6 pruebas Feature automatizadas para autenticación, validación y protección de API.

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
```
