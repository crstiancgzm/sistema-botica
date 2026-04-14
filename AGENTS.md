# Repository Guidelines (sistema-botica)

## How to Use This Guide

- **Start here** for norms that apply to the whole repo. This project is a monorepo with front and back.
- Each component has its own **AGENTS.md** with specific rules:
  - **Front:** [front-botica/AGENTS.md](front-botica/AGENTS.md)
  - **Back:** [back-botica/AGENTS.md](back-botica/AGENTS.md)
- When in conflict, the component guide overrides this file.

## Available Skills

Use these skills when you need detailed patterns:

### Project Skills (skills/)

| Skill | Description | Path |
|-------|-------------|------|
| `quasar` | Quasar UI (QPage, QBtn, layout), useQuasar, Vite | [skills/quasar/SKILL.md](skills/quasar/SKILL.md) |
| `vue-3` | Composition API, script setup, ref/reactive, composables, Pinia | [skills/vue-3/SKILL.md](skills/vue-3/SKILL.md) |
| `laravel-api` | Eloquent, controllers, FormRequest, API routes, JSON responses | [skills/laravel-api/SKILL.md](skills/laravel-api/SKILL.md) |
| `botica-precognitive-forms` | Formularios con validación Precognitive (front + back) | [skills/botica-precognitive-forms/SKILL.md](skills/botica-precognitive-forms/SKILL.md) |
| `botica-select-async` | Cuándo usar QSelect vs selectGeneralAsyncrono; uso del select asíncrono | [skills/botica-select-async/SKILL.md](skills/botica-select-async/SKILL.md) |
| `botica-list-table` | Listas con QTable y paginación servidor | [skills/botica-list-table/SKILL.md](skills/botica-list-table/SKILL.md) |
| `botica-list-dialog-crud` | Lista + diálogo crear/editar + refresco tabla | [skills/botica-list-dialog-crud/SKILL.md](skills/botica-list-dialog-crud/SKILL.md) |
| `botica-api-service` | Contrato de servicios front que consumen la API | [skills/botica-api-service/SKILL.md](skills/botica-api-service/SKILL.md) |
| `botica-file-upload` | Subida y guardado de archivos en formularios (ArchivosService, sync, rollback, FormArchivos) | [skills/botica-file-upload/SKILL.md](skills/botica-file-upload/SKILL.md) |

### Auto-invoke by Action

When performing these actions, ALWAYS invoke the corresponding skill **first**:

| Action | Skill |
|--------|-------|
| Working on **front** (Vue, Quasar, Pinia, API services) | See [front-botica/AGENTS.md](front-botica/AGENTS.md) |
| Working on **back** (Laravel, endpoints, models, FormRequest) | See [back-botica/AGENTS.md](back-botica/AGENTS.md) |
| Creating/modifying Vue components, layouts, Quasar pages | `vue-3`, `quasar` |
| Creating/modifying services that consume the API | `laravel-api` (contract with back) |
| Creating/modifying API endpoints, models, migrations, FormRequest | `laravel-api` |
| Changes affecting **front and back** (new resource, API contract) | `laravel-api` + front and back guides |
| Forms with Precognitive validation (new or existing) | `botica-precognitive-forms` |
| Adding or changing a select/dropdown (form or page) | `botica-select-async` (or ask user which select to use) |
| List page with QTable / server-side table | `botica-list-table` |
| List page with create/edit in dialog | `botica-list-dialog-crud` |
| Creating or modifying front API service (XxxService.js) | `botica-api-service` |
| Adding or modifying file upload in forms (archivos, attachments) | `botica-file-upload` |

---

## Project Overview

Sistema de Botica — Gestión de inventarios, categorías, laboratorios, proveedores, presentaciones, áreas y subcategorías.

| Component | Location | Stack |
|-----------|----------|--------|
| **Front** | `front-botica/` | Quasar 2, Vue 3, Vite, Pinia, Vue Router, Axios, laravel-precognition-vue |
| **Back** | `back-botica/` | Laravel (API), Eloquent, FormRequest, Spatie Permissions, PHPUnit |

Communication is **REST JSON**; the front consumes `/api/*` exposed by the back.

---

## Commands by Component

### Front (front-botica)

```bash
cd front-botica
npm install
npm run dev
npm run build
npm run lint
npm run format
```

### Back (back-botica)

```bash
cd back-botica
php artisan serve
php artisan migrate
php artisan db:seed
php artisan test
```

---

## Commit and PR

- Clear commit messages; if using a convention: `<type>[scope]: <description>` (e.g. `feat(front): lista de inventarios`).
- Before opening a PR: run lint and tests for the component you changed.
- Changes that touch the API (back) and its consumption (front): review both AGENTS.md to keep response contract and format consistent.
