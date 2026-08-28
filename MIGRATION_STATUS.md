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

## Trabajo pendiente

Las siguientes pantallas del frontend legacy aún están representadas por vistas genéricas o requieren una migración visual especializada:

- dashboards analíticos completos
- edición avanzada de proyectos, actividades y subactividades
- Gantt y líneas base
- formularios completos de activos y fichas técnicas
- gestión visual avanzada de capacitaciones
- comités e iniciativas con sus formularios y aprobaciones
- configuración IMAP, notificaciones, encuestas y ubicaciones
- reportes especializados y exportaciones
- pruebas end-to-end contra bases tenant reales

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
