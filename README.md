# Martina

Aplicación de gestión de proyectos desarrollada con Laravel, Inertia.js y Vue 3.

## Requisitos

- PHP 8.4+
- Composer
- Node.js 18+ / Bun
- Base de datos (SQLite por defecto)

## Instalación

1. Clonar el repositorio:
```bash
git clone <repository-url>
cd php-martina-app
```

2. Instalar dependencias de PHP:
```bash
composer install
```

3. Instalar dependencias de JavaScript:
```bash
bun install
# o npm install
```

4. Configurar el entorno:
```bash
cp .env.example .env
php artisan key:generate
```

5. Configurar la base de datos en `.env`:
```env
DB_CONNECTION=sqlite
# o tu configuración de base de datos preferida
```

6. Ejecutar migraciones y seeders:
```bash
php artisan migrate --seed
```

## Desarrollo

Iniciar el servidor de desarrollo:

```bash
# Terminal 1: Servidor Laravel
php artisan serve

# Terminal 2: Compilador de assets
bun run dev
```

La aplicación estará disponible en `http://localhost:8000`

## Construcción para Producción

```bash
bun run build
```

## Características Principales

- Gestión de proyectos multi-tenant
- Sistema de invitaciones por email
- Roles y permisos por proyecto
- Autenticación con Laravel Fortify
- Interfaz moderna con Tailwind CSS v4
- Componentes reactivos con Vue 3

## Stack Tecnológico

- Laravel 12
- Inertia.js v2
- Vue 3
- Tailwind CSS v4
- Laravel Fortify
- Spatie Laravel Permission
- Pest para testing

## Testing

Ejecutar la suite de tests:

```bash
php artisan test
```

Ejecutar tests específicos:

```bash
php artisan test --filter=ProjectTest
```

## Formato de Código

Laravel Pint para PHP:
```bash
vendor/bin/pint
```

Prettier para JavaScript/Vue:
```bash
bun run format
```

## Comandos Útiles

Limpiar cache:
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

Generar tipos TypeScript para Wayfinder:
```bash
php artisan wayfinder:generate
```

## Estructura del Proyecto

- `app/` - Código de la aplicación Laravel
- `resources/js/` - Componentes Vue y TypeScript
- `resources/views/` - Plantillas Blade
- `routes/` - Definición de rutas
- `database/` - Migraciones y seeders
- `tests/` - Tests con Pest


