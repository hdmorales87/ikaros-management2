# Backend Laravel 13 - Ikaros Management

Backend de la migración funcional del sistema Ikaros Management hacia Laravel 13, con capa de servicios, JWT, control de permisos y soporte multi-tenant.

## Objetivo

Reemplazar la lógica de negocio del sistema legado con una API moderna, compatible con la base de datos existente, manteniendo la estructura del negocio y evitando cambios destructivos sobre producción.

## Stack

- Laravel 13
- PHP 8.3+
- MySQL
- JWT
- Eloquent + Query Builder
- Servicios y controladores por módulo
- Middleware de autenticación y permisos
- IMAP / SMTP / file handling

## Estado de migración

El backend ya cubre la mayor parte del negocio operativo del sistema, incluyendo:

- autenticación, usuarios, roles, permisos y compañía
- solicitudes, SLA y trazabilidad
- activos, ficha técnica y gestión operativa
- proyectos, actividades, subactividades y riesgos
- capacitación, asistentes y confirmación
- iniciativas, comités y aprobación
- horas por proyecto con validación
- ubicaciones y configuración
- encuestas, terceros, contratos, pagos y adjuntos
- dashboard y reportes
- lista blanca de tablas para acceso controlado a datagrids

## Estructura principal

```text
backend/
├── app/
│   ├── Http/
│   ├── Models/
│   ├── Services/
│   ├── Providers/
│   ├── Policies/
│   └── ...
├── config/
├── database/
├── routes/
├── public/
├── storage/
├── tests/
├── .env.example
├── artisan
├── composer.json
├── phpunit.xml
├── vite.config.js
└── README.md
```

## Instalación

```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan serve
```

Asegúrate de configurar la conexión a MySQL y los parámetros de JWT / correo / IMAP según tu entorno.

## Configuración relevante

- DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD
- JWT secret y configuración de auth
- SMTP y mailer
- IMAP tenant-aware
- storage y archivos adjuntos

## Integraciones implementadas

### IMAP
- conexión por tenant
- sincronización programada
- gestión de mensajes no leídos
- validación de conexión y clasificación de mensajes

### SMTP / correo
- envío de notificaciones de negocio
- alertas por eventos y aprobaciones
- correos transaccionales del sistema

### Datagrid
- lista blanca de tablas permitidas
- acceso controlado para evitar consultas arbitrarias
- separación por negocio y módulo

## API y cobertura funcional

El backend ya está soportando rutas para:

- login, logout, validación de token y usuario
- compañías, organizaciones y módulos
- usuarios, roles y permisos
- gestión de tickets/solicitudes
- activos y fichas técnicas
- terceros y encuestas
- contratos y pagos
- archivos adjuntos y almacenamiento
- dashboards y reportes

## Validación actual

La validación efectiva disponible en esta sesión incluye:

- 71 rutas API registradas
- PHPUnit con 9 pruebas correctas y 11 aserciones
- validación estática del editor sin errores en archivos modificados

Sin embargo, la validación real de entorno aún requiere:

- restaurar dumps MySQL reales
- probar SMTP con entorno real
- probar IMAP con cuenta real o simulador
- ejecutar build/lint/test del proyecto en terminal funcional
- pruebas end-to-end por rol y flujo de negocio

## Pendientes

### Infraestructura / entorno
- validar MySQL real con schemas y tenant reales
- validar IMAP con cuenta real
- validar SMTP y correo transaccional
- revisar cron/scheduler en producción
- pruebas E2E por módulos y permisos

### Producto
- refinamiento UX de formularios complejos
- dashboards analíticos avanzados
- mejoras funcionales de catálogos y notificaciones

## Resumen

La base técnica y la capacidad funcional principal ya están implementadas en Laravel 13, con cobertura de negocio suficiente para sostener la migración del sistema. El trabajo restante se concentra en validación real del entorno y refinamientos de experiencia de usuario y operación.

## Licencia

MIT
