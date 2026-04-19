# Open CRM

## Language Navigation

- [English](#english)
- [Espanol](#espanol)

---

## English

### Project Summary

Open CRM is a multi-account CRM application built with Laravel 13, Livewire 4, and Flux UI.
It includes authentication with Fortify, account-aware authorization with Spatie Permission (teams), activity tracking, demo seed data, and an interactive guided onboarding powered by driver.js.

### Current Progress

| Phase | Scope | Status |
| --- | --- | --- |
| Phase 1 | Foundation: auth, account model, role/permission matrix, base navigation | Completed |
| Phase 2 | Core CRM modules: dashboard, leads, companies, contacts, pipeline, deals, tasks, reports | Completed |
| Phase 3 | Demo environment: idempotent demo seeding and realistic sample data | Completed |
| Phase 4 | Guided onboarding: interactive CRM tutorial with per-user/per-account persisted state | Completed |
| Phase 5 | Hardening and advanced features: automation, integrations, extended analytics, UX refinements | Planned |

### Technical Stack

- Backend: PHP 8.4, Laravel 13
- Auth: Laravel Fortify
- Realtime UI: Livewire 4 (full-page SFC pages)
- UI kit: Livewire Flux UI Free
- Styling: Tailwind CSS v4 + Vite
- Onboarding tour: driver.js
- Permissions: spatie/laravel-permission (teams enabled, tenant key: account_id)
- Activity logs: spatie/laravel-activitylog
- Testing: Pest 4 + PHPUnit 12
- Formatter: Laravel Pint
- Local environment: Laravel Herd

### Architecture

- Multi-account domain:
  - Users belong to one or many accounts.
  - Active context is set by users.current_account_id.
  - Permissions are evaluated in account context via Spatie team id.
- CRM modules are rendered as Livewire full-page components under resources/views/pages/crm.
- Navigation and shell layout live in resources/views/layouts/app/sidebar.blade.php.
- Tutorial state is persisted in crm_tutorial_states and exposed via API endpoints:
  - GET /crm/tutorial/state
  - POST /crm/tutorial/state
  - POST /crm/tutorial/restart

### Implemented CRM Modules

- Dashboard (KPIs, activity timeline, upcoming tasks)
- Leads (listing, filters, conversion flow)
- Companies (listing and detail)
- Contacts (listing and detail)
- Pipeline board (stage-oriented workflow)
- Deals (detail, stage movement, tasks, activity log)
- Tasks (assignment and completion flow)
- Reports (funnel and activity summaries)

### Setup and Configuration

#### Requirements

- PHP 8.4+
- Composer
- Node.js 20+
- npm
- SQLite (default local setup)
- Laravel Herd (recommended for this repository)

#### Initial Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed --no-interaction
npm install
npm run build
```

#### Local Access

- App URL (Herd): https://open-crm.test
- Do not run php artisan serve when using Herd.

### Demo Access

The project ships with DemoSeeder and is currently called by DatabaseSeeder.

- Email: demo@opencrm.test
- Password: password

Reset demo data quickly:

```bash
php artisan migrate:fresh --seed
```

### Development Commands

```bash
npm run dev
php artisan test --compact
vendor/bin/pint --dirty --format agent
```

### Testing and Quality

- Primary test style: Pest feature tests in tests/Feature
- Run focused tests by file for fast feedback:

```bash
php artisan test --compact tests/Feature/CRM/TutorialStateTest.php
```

- Recent validated areas:
  - Tutorial state API behavior
  - Sidebar navigation cleanup
  - Multi-account tutorial state isolation

### Important Paths

- Routes: routes/web.php, routes/crm.php, routes/settings.php
- CRM pages: resources/views/pages/crm
- Sidebar layout: resources/views/layouts/app/sidebar.blade.php
- Tutorial frontend runtime: resources/js/crm-tour.js
- Tutorial backend controller: app/Http/Controllers/CRM/TutorialStateController.php
- Tutorial model: app/Models/CrmTutorialState.php
- Tutorial migration: database/migrations/2026_04_19_081620_create_crm_tutorial_states_table.php
- Demo seeding: database/seeders/DemoSeeder.php

### Troubleshooting

- If tutorial endpoints return 500 locally, verify migrations were executed:

```bash
php artisan migrate --no-interaction
```

- If UI asset changes are not visible:

```bash
npm run dev
# or
npm run build
```

---

## Espanol

### Resumen del Proyecto

Open CRM es una aplicacion CRM multi-cuenta construida con Laravel 13, Livewire 4 y Flux UI.
Incluye autenticacion con Fortify, autorizacion por cuenta con Spatie Permission (teams), registro de actividad, datos demo y onboarding interactivo con driver.js.

### Estado Actual y Fases

| Fase | Alcance | Estado |
| --- | --- | --- |
| Fase 1 | Fundacion: auth, modelo de cuentas, matriz de roles/permisos, navegacion base | Completada |
| Fase 2 | Modulos CRM core: dashboard, leads, empresas, contactos, pipeline, deals, tareas, reportes | Completada |
| Fase 3 | Entorno demo: seeding idempotente y datos de ejemplo realistas | Completada |
| Fase 4 | Onboarding guiado: tutorial interactivo con estado persistente por usuario/cuenta | Completada |
| Fase 5 | Fortalecimiento y funciones avanzadas: automatizaciones, integraciones, analitica extendida y mejoras UX | Planeada |

### Stack Tecnologico

- Backend: PHP 8.4, Laravel 13
- Autenticacion: Laravel Fortify
- UI reactiva: Livewire 4 (paginas SFC)
- UI kit: Livewire Flux UI Free
- Estilos: Tailwind CSS v4 + Vite
- Tutorial guiado: driver.js
- Permisos: spatie/laravel-permission (teams habilitado, clave tenant: account_id)
- Logs de actividad: spatie/laravel-activitylog
- Testing: Pest 4 + PHPUnit 12
- Formateo: Laravel Pint
- Entorno local: Laravel Herd

### Arquitectura

- Dominio multi-cuenta:
  - Un usuario puede pertenecer a una o varias cuentas.
  - El contexto activo se define en users.current_account_id.
  - Los permisos se evalua en contexto de cuenta con team id de Spatie.
- Los modulos CRM se renderizan como componentes Livewire full-page en resources/views/pages/crm.
- La navegacion y layout principal viven en resources/views/layouts/app/sidebar.blade.php.
- El estado del tutorial se persiste en crm_tutorial_states y se expone por API:
  - GET /crm/tutorial/state
  - POST /crm/tutorial/state
  - POST /crm/tutorial/restart

### Modulos CRM Implementados

- Dashboard (KPIs, timeline de actividad, tareas proximas)
- Leads (listado, filtros y conversion)
- Empresas (listado y detalle)
- Contactos (listado y detalle)
- Pipeline (tablero por etapas)
- Deals (detalle, movimiento de etapa, tareas, actividad)
- Tareas (asignacion y finalizacion)
- Reportes (embudo y actividad por usuario)

### Configuracion e Instalacion

#### Requisitos

- PHP 8.4+
- Composer
- Node.js 20+
- npm
- SQLite (setup local por defecto)
- Laravel Herd (recomendado para este repositorio)

#### Configuracion Inicial

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed --no-interaction
npm install
npm run build
```

#### Acceso Local

- URL de la app (Herd): https://open-crm.test
- Con Herd, no usar php artisan serve.

### Acceso Demo

El proyecto incluye DemoSeeder y actualmente se ejecuta desde DatabaseSeeder.

- Email: demo@opencrm.test
- Password: password

Reiniciar datos demo rapido:

```bash
php artisan migrate:fresh --seed
```

### Comandos de Desarrollo

```bash
npm run dev
php artisan test --compact
vendor/bin/pint --dirty --format agent
```

### Testing y Calidad

- Estilo principal: pruebas Feature con Pest en tests/Feature.
- Ejecutar pruebas puntuales por archivo para feedback rapido:

```bash
php artisan test --compact tests/Feature/CRM/TutorialStateTest.php
```

- Areas validadas recientemente:
  - API de estado del tutorial
  - Limpieza de navegacion en sidebar
  - Aislamiento de estado del tutorial por cuenta

### Rutas y Archivos Importantes

- Rutas: routes/web.php, routes/crm.php, routes/settings.php
- Paginas CRM: resources/views/pages/crm
- Layout sidebar: resources/views/layouts/app/sidebar.blade.php
- Runtime frontend del tutorial: resources/js/crm-tour.js
- Controlador backend del tutorial: app/Http/Controllers/CRM/TutorialStateController.php
- Modelo del tutorial: app/Models/CrmTutorialState.php
- Migracion del tutorial: database/migrations/2026_04_19_081620_create_crm_tutorial_states_table.php
- Seeder demo: database/seeders/DemoSeeder.php

### Solucion de Problemas

- Si endpoints del tutorial devuelven 500 en local, validar migraciones:

```bash
php artisan migrate --no-interaction
```

- Si no ves cambios de frontend:

```bash
npm run dev
# o
npm run build
```
