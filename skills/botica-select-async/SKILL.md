---
name: botica-select-async
description: >
  sistema-botica when to use Quasar's standard select vs selectGeneralAsyncrono, and how to use the async select (API, Precognitive, dependent selects).
  Trigger: When adding or changing a select/dropdown field in the front; when unsure which component to use.
license: MIT
metadata:
  scope: [root, front]
  project: sistema-botica
  auto_invoke: "Adding or changing a select/dropdown in forms or pages"
allowed-tools: Read, Edit, Write, Glob, Grep, Bash, WebFetch, WebSearch, Task
---

## Which select to use (MUST decide first)

**Do not always use `selectGeneralAsyncrono`.** Choose by case:

| Case | Component | Example |
|------|------------|---------|
| **Few and fixed** options (short, static list) | **Quasar QSelect** with `:options="options"` | Activo/Inactivo, tipo de presentación, listas de 2–10 items |
| **Many options or from API** (search, pagination, dependent) | **selectGeneralAsyncrono** | Categoría, laboratorio, proveedor, área, etc. |
| **Uncertain** (few or many? static or API?) | **Ask the user** which select they prefer before implementing | — |

Rule of thumb: if options fit in a static array (e.g. 3–10 items) and do not change by filters or search → **standard QSelect**. If options are loaded from API, searchable as the user types, or depend on another field → **selectGeneralAsyncrono**.

---

## Quasar QSelect (simple select)

For small, static lists, use `q-select` directly with in-memory options:

```vue
<template>
  <q-select
    v-model="valor.estado"
    :options="opcionesEstado"
    option-value="value"
    option-label="label"
    label="Estado"
    outlined
    dense
    emit-value
    map-options
  />
</template>

<script setup>
const opcionesEstado = [
  { value: 1, label: 'Activo' },
  { value: 0, label: 'Inactivo' },
]
</script>
```

With Precognitive: same as other fields (`v-model`, `@change="model.validate(propPath + '.field')"`, `:error`, slot `#error` with `model.errors[propPath + '.field']`). See `botica-precognitive-forms` for the full pattern.

---

## selectGeneralAsyncrono (when to use it)

Use **only** when:

- Options come from a **service/API** (paginated list or search), or
- There is a **dependent select** (e.g. subcategorías by categoría), or
- The user needs to **search** among many options by typing.

Component location: `src/components/selectGeneralAsyncrono.vue`.

---

## Service contract (serviceApi)

The component expects an object (class or module) with at least:

| Method | Purpose | Expected shape |
|--------|---------|----------------|
| `getData({ params })` | List for search and scroll | `params`: `{ search, page, sort_by, direction, ... }`. Must return an object with `data` (array of items) and `last_page` (number). |
| `get(id)` | Load selected option when editing (when id is already set) | Returns the item; if the API returns a wrapper, use prop `custonGet`. |
| `save(payload)` | Only when using the add-item option (`add: true`) | Create new item and return it. |

Example of a compatible service (Laravel-style paginated response):

```javascript
// getData receives { params: { search, page, sort_by, direction, ...paramsApi } }
static async getData({ params } = {}) {
  const res = await api.get('/api/laboratorios', { params })
  return res.data  // must have .data (array) and .last_page
}
static async get(id) {
  const res = await api.get(`/api/laboratorios/${id}`)
  return res.data
}
```

---

## Basic usage with Precognitive form

Inside a component that receives the form via `defineModel()` and has `propPath` and computed `valor` (see `botica-precognitive-forms`):

```vue
<selectGeneralAsyncrono
  v-model:id="valor.laboratorio_id"
  label="Seleccionar laboratorio"
  option-label="nombre"
  option-value="id"
  :serviceApi="LaboratorioService"
  :disable="readonlyComputed || disableInputList.includes('laboratorio_id')"
  :error="model.invalid(propPath + '.laboratorio_id')"
  :errorMessages="model.errors[propPath + '.laboratorio_id']"
/>
```

- **v-model:id**: Binds only the id to the form (usual). The field name must match the backend FormRequest (e.g. `laboratorio_id`).
- **error** and **errorMessages**: Always pass them when the form uses Precognitive, from `model.invalid(propPath + '.field_id')` and `model.errors[propPath + '.field_id']`.
- **option-label** / **option-value**: According to the item shape returned by the API (e.g. `nombre`, `codigo`).

---

## Dependent select (filtered by another field)

When options depend on another value (e.g. subcategorías for una categoría):

```vue
<selectGeneralAsyncrono
  v-model:id="valor.subcategoria_id"
  label="Seleccionar subcategoría"
  option-label="nombre"
  option-value="id"
  :serviceApi="SubcategoriaService"
  :paramsApi="subcategoriasParamsApi"
  :disable="!valor.categoria_id || readonlyComputed"
  :error="model.invalid(propPath + '.subcategoria_id')"
  :errorMessages="model.errors[propPath + '.subcategoria_id']"
/>
```

```javascript
const subcategoriasParamsApi = computed(() => ({
  categoria_id: valor.categoria_id,
}))
```

The backend must accept `categoria_id` (or others) in the list params. If there is no parent value, use `:disable="true"` to avoid unnecessary requests.

---

## Useful options

| Prop | Use |
|------|-----|
| `add` | `true` if adding an item from the select is allowed (uses `serviceApi.save` or `@custom-add`). By default the project usually uses `:add="false"` in CRUD forms. |
| `custonGet` | When `get(id)` returns a nested object, specify the key. |
| `filterFields` | Fields to filter the list by (default `['nombre']`). Adjust if the resource uses another (e.g. `codigo`). |
| `@option-selected` | To react to change (e.g. clear a dependent: when categoría changes, set `subcategoria_id = null`). |

---

## When to use v-model in addition to v-model:id

By default in forms **v-model:id** (id only) is enough. Use **v-model** (full object) in addition to **v-model:id** when the screen needs to show more data of the selected item.

---

## NEVER

- Do not use `selectGeneralAsyncrono` for small, static lists; use QSelect with static `:options`.
- Do not implement a select without deciding first (or asking the user) whether it should be simple (Quasar) or async (selectGeneralAsyncrono).
- Do not pass a `serviceApi` that does not fulfill the contract (`getData({ params })` with response `{ data, last_page }`, `get(id)`).
- Do not omit `:error` and `:errorMessages` when the select is inside a Precognitive form.
