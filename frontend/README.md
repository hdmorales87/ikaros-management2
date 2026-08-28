# Frontend React - Ikaros Management

Frontend moderno del sistema Ikaros Management, construido con React 19, TypeScript, Vite y TanStack Query.

## Objetivo

Reemplazar la interfaz heredada con una experiencia operativa más clara, modular y mantenible, manteniendo el soporte de la lógica de negocio del sistema actual y la compatibilidad con el backend migrado.

## Stack

- React 19
- TypeScript
- Vite
- TanStack Query
- Axios
- React Router
- CSS y componentes específicos por módulo

## Estado actual

El frontend ya incluye la navegación y la interfaz de múltiples módulos del sistema, entre otros:

- autenticación y sesión
- usuarios y roles
- compañías y configuración
- solicitudes y trazabilidad
- activos y fichas técnicas
- proyectos y actividades
- capacitación
- iniciativas y comités
- horas y validación
- ubicaciones
- encuestas
- terceros y contratos
- dashboard operativo
- administración y módulos configurables

## Estructura principal

```text
frontend/
├── src/
│   ├── api/
│   ├── components/
│   ├── features/
│   ├── hooks/
│   ├── pages/
│   ├── services/
│   ├── App.tsx
│   └── main.tsx
├── public/
├── .env.example
├── package.json
├── vite.config.ts
├── tsconfig.json
├── eslint.config.js
└── README.md
```

## Instalación

```bash
cd frontend
npm install
cp .env.example .env
npm run dev
```

Configura las variables del entorno, por ejemplo:

```env
VITE_API_URL=http://localhost:8000
VITE_COMPANY_UUID=tu-uuid
```

## Scripts

```bash
npm run dev
npm run build
npm run lint
```

## Cobertura funcional visible

La interfaz ya está preparada para la operación de los principales procesos del negocio, incluyendo:

- gestión de usuarios, roles y permisos
- navegación por módulos y configuración
- registro y consulta de solicitudes
- detalle de activos y documentación
- gestión de terceros, contratos y pagos
- aprobación de iniciativas y comités
- reportes y dashboard operativo
- carga y descarga de archivos adjuntos

## Validación actual

Se validó de forma estática dentro del editor que los archivos modificados del frontend no presentan errores de TypeScript o análisis del workspace.

Sin embargo, la validación real del frontend en entorno de ejecución requiere:

- build real del proyecto
- lint real
- pruebas E2E
- validación con backend Laravel real
- comprobación de flows por rol

## Pendientes

- mejorar UX en formularios complejos
- optimizar flujo de validación en pantallas largas
- enriquecer dashboards analíticos
- revisar accesibilidad y consistencia visual
- pruebas de integración reales con datos del sistema

## Resumen

El frontend de Ikaros Management 2 ya refleja la mayor parte de la migración funcional del negocio y está preparado para seguir creciendo con una base moderna en React, manteniendo compatibilidad con el backend Laravel 13 y la lógica operativa del sistema actual.

## Licencia

MIT
