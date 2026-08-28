# Ikaros Management Backend

Backend API del sistema Ikaros Management migrado a Laravel 13 con PHP 8.3.

## Características

- **Framework**: Laravel 13.29.0
- **PHP**: 8.3.33
- **Autenticación**: JWT (Firebase JWT v7.1.0)
- **Base de datos**: MySQL con soporte multi-tenant
- **Arquitectura**: Service Layer Pattern
- **Validación**: Form Request Validation
- **Seguridad**: Headers de seguridad, bcrypt para contraseñas

## Requisitos

- PHP >= 8.3
- Composer
- MySQL >= 5.7
- Apache o Nginx
- Bases de datos multitenant ya creadas (servicedesk0, ikarosof_management0, ikarosof_management_acceso)

## Instalación

1. Clonar el repositorio o copiar los archivos al directorio del proyecto
2. Instalar dependencias:
```bash
composer install
```

3. Configurar el archivo `.env`:
```bash
cp .env.example .env
php artisan key:generate
```

4. Configurar conexión a base de datos en `.env` (usar la BD global):
```
DB_CONNECTION=ikarosof_management_acceso
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ikarosof_management_acceso
DB_USERNAME=root
DB_PASSWORD=tu_password_mysql
```

5. Configurar clave JWT:
```
JWT_SECRET=your-super-secret-jwt-key-change-this-in-production
JWT_TTL=28800
```

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
