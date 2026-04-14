---
name: botica-api-service
description: >
  sistema-botica front-end API service contract: getData (list/pagination), get (single), save (create/update), delete. Axios instance from boot, response shape for lists.
  Trigger: When creating or modifying a service that consumes the backend API (CRUD, list, or custom endpoints).
license: MIT
metadata:
  scope: [root, front]
  project: sistema-botica
  auto_invoke: "Creating or modifying a front API service (XxxService.js)"
allowed-tools: Read, Edit, Write, Glob, Grep, Bash, WebFetch, WebSearch, Task
---

## Overview

In sistema-botica, the front uses **service classes** (or modules) in `src/services/` to talk to the Laravel API. Each resource has a `XxxService.js` that uses the shared **api** instance from `src/boot/axios`. The backend is documented in **laravel-api** and back AGENTS.md; this skill documents the **front** contract so list pages, forms, and **selectGeneralAsyncrono** work consistently.

---

## When to use this skill

- New resource: create a new `XxxService.js` with getData, get, save (and delete if needed).
- Modifying an existing service: add a method or change params/response handling while keeping the contract.
- Ensuring a service works with **botica-list-table** (getData) and **botica-select-async** (getData, get, optional save).

---

## Axios instance

Always use the same instance from boot so auth interceptors and base URL apply:

```javascript
import { api } from 'src/boot/axios'
```

Do not create a new axios instance in the service.

---

## Standard methods

### getData (list with pagination, search, sort)

Used by list pages and by **selectGeneralAsyncrono** for options.

**Signature:** `getData(config)` where `config` is the axios request config (usually `{ params: { ... } }`).

**Typical params** (align with backend `generateViewSetList` and getPageSize):

- **rowsPerPage** (or per_page / page_size if the backend accepts them)
- **page**
- **search** (search term)
- **order_by** (e.g. `id` or `-id` for descending)
- Any **filter** params the backend expects

**Return:** the backend response body. For list endpoints the backend returns an object with at least **data** (array) and **total** (or equivalent for total count). Destructure in the caller: `const { data, total = 0 } = await XxxService.getData({ params: { ... } })`.

**Example (flat resource):**

```javascript
static async getData(params) {
  return (await api.get('/api/categorias', params)).data
}
```

Caller passes `getData({ params: { rowsPerPage, page, search, order_by } })` and uses `result.data` and `result.total` (or the shape the backend actually returns).

**Example (nested in config):**

```javascript
static async getData({ params } = {}) {
  const res = await api.get('/api/categorias', { params })
  return res.data
}
```

Ensure the backend response includes the structure the list page expects (e.g. `data` and `total`).

---

### get(id) (single resource)

Used when loading one record (e.g. for edit form, or for **selectGeneralAsyncrono** when initializing the selected option).

**Signature:** `get(id)`.

**Return:** the backend response body (the resource object). If the API wraps it, return the full response and let the caller or select component use the right key (e.g. custonGet).

**Example:**

```javascript
static async get(id) {
  return (await api.get(`/api/categorias/${id}`)).data
}
```

---

### save (create or update)

Used by forms and sometimes by select "add" flow. The backend expects a nested payload for Precognitive forms (e.g. `{ categoria: { ... } }`); for flat resources the payload may be the object itself.

**Signature:** `save(payload)`.

**Behavior:** if the payload indicates an existing record (e.g. `payload.id` or `payload.categoria.id`), use PUT to the resource URL; otherwise POST to the collection URL. Return the backend response body.

**Example (flat resource):**

```javascript
static async save(reg) {
  if (reg.id === undefined || reg.id === null) {
    return (await api.post('api/categorias', reg)).data
  }
  return (await api.put(`api/categorias/${reg.id}`, reg)).data
}
```

**Example (nested resource):**

```javascript
static async save(reg) {
  if (reg.categoria.id === undefined || reg.categoria.id === null || reg.categoria.id === '') {
    return (await api.post('api/categorias', reg)).data
  }
  return (await api.put(`api/categorias/${reg.categoria.id}`, reg)).data
}
```

Align the payload shape with the backend FormRequest and controller (see **botica-precognitive-forms**).

---

### delete(id)

Used when the list or detail page deletes a record.

**Signature:** `delete(id)`.

**Example:**

```javascript
static async delete(id) {
  return await api.delete(`/api/categorias/${id}`)
}
```

The backend typically returns 204; the caller usually refreshes the list or navigates after delete.

---

## Optional methods

Resources may expose extra endpoints. Keep the same style: use `api` from boot, accept params as needed, return the response body (or `.data`) so callers get a consistent shape.

---

## File location and naming

- **Path:** `src/services/XxxService.js`.
- **Name:** PascalCase resource name + `Service`, e.g. `CategoriaService`, `LaboratorioService`, `ProveedorService`.
- **Export:** `export default XxxService` (class or object with static methods).

---

## NEVER

- Do not create a new axios instance in the service; use `import { api } from 'src/boot/axios'`.
- Do not change the meaning of getData params (rowsPerPage, page, search, order_by) without aligning with the backend and with **botica-list-table** / **botica-select-async**.
- Do not return a different shape for list responses (e.g. missing `data` or `total`) if the list page or select expects the standard shape; fix the backend or the caller instead.
