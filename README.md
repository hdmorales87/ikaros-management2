# Ikaros Management 2

Sistema de gestión operativa y administrativa para la empresa, desarrollado en una arquitectura moderna con Laravel 13 en el backend y React 19 + TypeScript en el frontend. El proyecto busca migrar la lógica del sistema legado sin tocar la estructura de producción, manteniendo compatibilidad con la base de datos actual y ampliando la cobertura funcional de la operación.

## Visión general

Ikaros Management 2 es una evolución del sistema de gestión para cubrir procesos de negocio, administración, trazabilidad, operación y atención. La solución combina:

- backend Laravel 13 para API, autenticación, servicios y reglas de negocio
- frontend React 19 + Vite para la capa de usuario
- soporte multi-tenant y base de datos MySQL
- gestión de usuarios, permisos, solicitudes, terceros, contratos, activos, proyectos y reportes
- integración de mail, IMAP y archivos adjuntos

## Estado de la migración

La migración funcional se encuentra avanzada y cubre la mayor parte del negocio operativo. Actualmente el proyecto incluye soporte para:

### Seguridad y administración
- autenticación con JWT
- usuarios, roles y permisos
- compañías y módulos
- control de acceso por perfil
- configuración general del sistema

### Operación y gestión
- solicitudes con trazabilidad y SLA
- activos y ficha técnica
- proyectos, actividades y riesgos
- capacitación y asistentes
- iniciativas y comités
- horas de proyecto y validación
- ubicaciones y departamentos
- encuestas de satisfacción
- encuestas de terceros
- terceros y relaciones comerciales
- contratos, pagos y adjuntos
- notificaciones de contratos
- dashboard operativo
- reportes y exportación

### Integraciones
- SMTP para notificaciones
- IMAP tenant-aware para correos de negocio
- almacenamiento y gestión de archivos
- datagrid con lista blanca para acceso seguro a tablas

### Mejoras ya incorporadas de producto y UX
- módulos más completos por área funcional
- navegación y configuración centralizada
- dashboard operativo con visión general del sistema
- trazabilidad más clara en aprobaciones y validaciones
- formularios con flujo más ordenado por contexto
- manejo más uniforme de adjuntos y estados de negocio
- validación persistente de fechas y reglas de negocio
- corrección de flujos de terceros, comités y horas

## Alcance actual del proyecto

El sistema ya no se presenta como una migración parcial aislada: está cubriendo un conjunto funcional completo de la operación y administración. La solución ya puede sostener la mayoría de los procesos clave del negocio con una estructura moderna y mantenible.

## Arquitectura

### Backend
- Laravel 13
- PHP 8.3+
- Eloquent y Query Builder
- capa de servicios para negocio
- middleware para autenticación y permisos
- controladores API REST
- configuración multi-tenant y conexiones dinámicas

### Frontend
- React 19
- TypeScript
- Vite
- TanStack Query
- Axios
- React Router
- estructura modular por features y módulos funcionales

## Estructura del repositorio

```text
ikaros-management2/
├── backend/                  # API Laravel 13
│   ├── app/
│   ├── config/
│   ├── database/
│   ├── public/
│   ├── resources/
│   ├── routes/
│   ├── tests/
│   ├── .env.example
│   ├── artisan
│   ├── composer.json
│   ├── package.json
│   ├── phpunit.xml
│   └── README.md
├── frontend/                 # Frontend React + Vite
│   ├── src/
│   ├── public/
│   ├── .env.example
│   ├── package.json
│   ├── vite.config.ts
│   ├── tsconfig.json
│   └── README.md
├── MIGRATION_STATUS.md       # documento de estado de migración
├── LICENSE
├── README.md                 # documentación general del proyecto
├── package.json              # scripts del repositorio (si aplica)
└── ...
```

## Requisitos

- PHP 8.3+
- Composer
- Node.js 18+
- npm
- MySQL
- acceso a entorno SMTP / IMAP para validación de integración

## Instalación rápida

### 1) Backend

```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan serve
```

### 2) Frontend

```bash
cd frontend
cp .env.example .env
npm install
npm run dev
```

### Variables de entorno

Ejemplo de configuración sugerida:

```env
APP_NAME="Ikaros Management"
APP_ENV=local
APP_DEBUG=true
APP_KEY=

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...
```

En frontend:

```env
VITE_API_URL=http://localhost:8000
VITE_COMPANY_UUID=tu-uuid
```

## Módulos principales ya cubiertos

### Seguridad y accesos
- login y auth JWT
- usuarios
- roles y permisos
- compañía / tenant
- módulos y navegación

### Gestión operativa
- solicitudes
- activos
- proyectos
- subactividades
- riesgos
- capacitación
- horas
- ubicaciones
- terceros
- contratos
- pagos
- encuestas
- notificaciones

### Administración y soporte
- dashboard funcional
- reportes
- configuración de catálogos
- gestión de archivos adjuntos
- notificaciones por correo
- sincronización IMAP
- datagrid controlado por whitelist

## Swagger / OpenAPI

La API del backend está preparada para documentarse con Swagger/OpenAPI usando Laravel Swagger. La documentación base ya quedó preparada en los controladores del backend y en la configuración del proyecto.

### Generación del documento

```bash
cd backend
composer require darkaonline/l5-swagger
php artisan vendor:publish --provider="L5Swagger\\L5SwaggerServiceProvider"
php artisan l5-swagger:generate
php artisan serve
```

Luego abre la UI en:

```text
http://localhost:8000/api/documentation
```

### Archivos relacionados

- [backend/config/l5-swagger.php](backend/config/l5-swagger.php)
- [backend/app/Http/Controllers/OpenApiDocsController.php](backend/app/Http/Controllers/OpenApiDocsController.php)
- [backend/routes/api.php](backend/routes/api.php)

> La generación real de la especificación en Laravel no se limita a inspeccionar rutas: requiere anotaciones Swagger en los controladores y la librería de documentación. Esa base ya quedó configurada para el proyecto.

## Validación actual

Se cuenta con evidencia funcional y técnica de la migración en curso:

- 71 rutas API registradas
- PHPUnit con 9 pruebas correctas y 11 aserciones
- validación estática del editor sin errores en archivos modificados
- documento de estado en [MIGRATION_STATUS.md](MIGRATION_STATUS.md)

Sin embargo, la validación real en entorno de producción o de ensayo aún requiere:

- restaurar dumps reales de MySQL
- probar SMTP con entorno real
- validar IMAP con cuenta real o simulador
- ejecutar build/lint/test real desde terminal
- pruebas end-to-end por rol y flujo

## Pendientes relevantes

### Infraestructura y validación
- pruebas con base de datos real del sistema legado
- validación del flujo IMAP y SMTP en entorno real
- verificación de cron/scheduler
- revisión de despliegue y CORS
- pruebas E2E por roles y permisos

### Producto y UX
- refinamiento de formularios complejos
- mejoras en experiencia de usuario y validación visual
- ordenar mejor el flujo de algunos formularios largos
- mejorar mensajes de error y confirmación del usuario
- reforzar estados visibles en pantallas de aprobación y contratos
- dashboards analíticos avanzados
- plantillas más ricas de notificaciones

### Estado de cierre del proyecto
El proyecto está funcionalmente muy avanzado y ya incorpora la mayor parte de la operación. Lo que resta no es un bloque crítico de negocio sino una fase de pulido final: validación real del entorno, afinamiento UX y preparación para despliegue operativo completo.

## Convenciones y consideraciones

- No se aplican migraciones destructivas sobre producción.
- La compatibilidad con la base de datos existente se mantiene a través de adaptaciones funcionales y acceso controlado.
- Se prioriza la migración funcional por módulos antes que la reescritura completa del sistema.

## Comandos útiles

### Backend

```bash
cd backend
composer install
php artisan test
php artisan route:list --path=api
php artisan schedule:list
```

### Frontend

```bash
cd frontend
npm install
npm run build
npm run lint
npm run dev
```

## Resumen ejecutivo

Ikaros Management 2 ha avanzado significativamente en la migración del sistema legacy hacia una plataforma moderna y mantenible. La base técnica está consolidada, la lógica funcional principal ya está implementada, y la experiencia del sistema ha sido mejorada de manera importante en módulos clave.

La prioridad restante es la validación real del entorno, el pulido final UX en algunos formularios y la preparación para despliegue y operación real. En términos de cobertura funcional, el proyecto ya está muy cerca de una entrega sólida y operativa.

## Licencia

MIT
