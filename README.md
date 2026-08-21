# Sistema de Gestión de Encuestas - Alcaldía de Sucre

Plataforma diseñada para la creación, recolección y análisis de encuestas municipales, permitiendo la generación de estadísticas medibles y visualización de datos mediante gráficos interactivos.

## Stack Tecnológico

- **Backend:** Laravel 13 (PHP 8.4+)
- **Frontend:** Vue 3 (Composition API + `<script setup>`)
- **Framework Frontend:** Inertia.js
- **Base de Datos:** PostgreSQL
- **Visualización:** vue-chartjs (Chart.js)
- **Estilos:** TailwindCSS

## Requisitos Previos

- PHP 8.4+
- Composer
- Node.js 20+ / npm
- PostgreSQL

## Instalación y Ejecución

1. **Clonar el repositorio:**
   ```bash
   git clone <url-del-repositorio>
   cd <nombre-del-proyecto>
   ```

2. **Configuración de entorno:**
   ```bash
   cp .env.example .env
   # Configura tus credenciales de PostgreSQL en el archivo .env
   ```

3. **Instalación de dependencias:**
   ```bash
   composer install
   npm install
   ```

4. **Generación de claves y base de datos:**
   ```bash
   php artisan key:generate
   php artisan migrate --seed
   ```

5. **Ejecución del entorno de desarrollo:**
   ```bash
   # En una terminal:
   php artisan serve
   
   # En otra terminal:
   npm run dev
   ```

## Usuarios por Defecto

El sistema incluye usuarios precargados mediante los *seeders* definidos en `database/seeders/DatabaseSeeder.php`.

## Estructura del Proyecto

- `app/Http/Controllers/`: Lógica de control.
- `resources/js/Pages/`: Componentes de vistas (Inertia).
- `resources/js/Components/`: Componentes reutilizables.
