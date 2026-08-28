# Estado de migración del backend

Este backend Laravel 13 está migrando progresivamente la lógica de `ikaros-management/backend2`.

- Usuarios y permisos de roles.
- Catálogos y creación de solicitudes.

Datagrid, activos, ficha técnica, correo, terceros y operaciones avanzadas de solicitudes aún requieren completar el contrato de sus tablas tenant. En el legado, varias de estas operaciones reciben nombres de tabla y columnas directamente desde el cliente; antes de migrarlas se debe definir una lista blanca por operación para evitar consultas arbitrarias.
Backend API del sistema Ikaros Management migrado a Laravel 13 con PHP 8.3.

- **Autenticación**: JWT (Firebase JWT v7.1.0)
- **Base de datos**: MySQL con soporte multi-tenant
- **Arquitectura**: Service Layer Pattern
- **Validación**: Form Request Validation
- PHP >= 8.3
- Composer

## Instalación
2. Instalar dependencias:
```bash
cp .env.example .env
php artisan key:generate
```

DB_HOST=127.0.0.1
DB_PORT=3306

5. Configurar clave JWT:

6. Las conexiones multitenant ya están configuradas en `config/database.php`:
   - `servicedesk0` - Base de datos principal de service desk
   - `ikarosof_management0` - Base de datos de gestión
   - `ikarosof_management_acceso` - Base de datos global (default)

7. Iniciar servidor de desarrollo:
```bash
php artisan serve
```

## Estructura del Proyecto

```
app/
├── Http/
│   ├── Controllers/      # Controladores API
│   └── Middleware/       # Middleware personalizados
├── Models/              # Modelos Eloquent
├── Services/            # Lógica de negocio
└── Helpers/             # Helpers (JWT, etc.)
```

## Mejoras Implementadas

### Seguridad
- ✅ Reemplazo de MD5 por bcrypt para contraseñas
- ✅ JWT key en .env en lugar de hardcoded
- ✅ Headers de seguridad (CORS, XSS, etc.)
- ✅ Validación de requests con Form Request Validation

### Arquitectura
- ✅ Service Layer Pattern para separar lógica de negocio
- ✅ Type hints y return types
- ✅ Relaciones Eloquent en modelos
- ✅ Uso de Query Builder en lugar de SQL raw

### Calidad de Código
- ✅ Nombres de rutas descriptivos
- ✅ Manejo específico de excepciones
- ✅ Logging estructurado
- ✅ Configuración centralizada

### Laravel 13
- ✅ Migración de Laravel 6.2 a 13.29.0
- ✅ PHP 8.3 compatible
- ✅ Dependencias actualizadas
- ✅ Estructura de configuración moderna

## IMAP moderno

La integración usa `webklex/laravel-imap` 6.x y clientes creados en runtime por tenant. La conexión se puede comprobar con `POST /api/checkIMAP` o con `php artisan imap:sync --uuid=<uuid>`. El scheduler ejecuta `imap:sync` cada cinco minutos. En producción, ejecuta el scheduler en un único worker para evitar ejecuciones simultáneas.

## API Endpoints

### Autenticación (Públicos)
- `POST /api/login` - Iniciar sesión
- `GET /api/checkUsername/{username}` - Verificar usuario
- `POST /api/updatePassword` - Actualizar contraseña
- `GET /api/checkUserToken` - Verificar token

### Empresa (Públicos)
- `POST /api/checkCompany` - Verificar empresa
- `GET /api/getPoliticasSeguridad` - Obtener políticas
- `GET /api/getIdioma` - Obtener idioma

### Empresa (Protegidos)
- `GET /api/getCompanyData` - Obtener datos empresa
- `GET /api/getCompanyModules` - Obtener módulos

### Usuarios (Protegidos)
- `GET /api/users` - Listar usuarios
- `GET /api/users/{id}` - Obtener usuario
- `POST /api/users` - Crear usuario
- `PUT /api/users/{id}` - Actualizar usuario
- `DELETE /api/users/{id}` - Eliminar usuario

## Malas Prácticas Corregidas

### Seguridad Crítica
- ❌ **MD5 para contraseñas** → ✅ bcrypt/Hash
- ❌ **JWT key en código** → ✅ .env
- ❌ **Credenciales visibles** → ✅ .env

### Arquitectura
- ❌ **Lógica en modelos** → ✅ Service Layer
- ❌ **Controladores passthrough** → ✅ Validación y servicios
- ❌ **SQL raw queries** → ✅ Query Builder/Eloquent
- ❌ **ConfigHelper personalizado** → ✅ Config de Laravel

### Calidad
- ❌ **Sin type hints** → ✅ Tipado completo
- ❌ **Sin validación** → ✅ Form Request Validation
- ❌ **Rutas sin nombres** → ✅ Rutas nombradas
- ❌ **Catch genérico** → ✅ Excepciones específicas

## Licencia

MIT License
