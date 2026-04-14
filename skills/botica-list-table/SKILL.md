---
name: botica-list-table
description: >
  sistema-botica server-side paginated lists with Quasar QTable: onRequest, pagination ref, Service.getData contract, table refresh.
  Trigger: When adding or modifying a list page with a data table (CRUD list, search, sort).
license: MIT
metadata:
  scope: [root, front]
  project: sistema-botica
  auto_invoke: "Adding or modifying a list page with QTable / server-side table"
allowed-tools: Read, Edit, Write, Glob, Grep, Bash, WebFetch, WebSearch, Task
---

## Overview

In sistema-botica, list pages that show tabular data use **Quasar QTable** with **server-side** pagination, sorting, and search. The table ref triggers a request via `@request`; the handler calls the domain **Service.getData** with params; the backend returns `data` and `total`. The same pattern is used in standalone list pages (e.g. CategoriasPage) and in reusable table components.

---

## When to use this skill

- New list page for a resource (e.g. list of categorías, laboratorios, proveedores, inventarios, etc.).
- Modifying an existing list/table (columns, filters, refresh after create/update/delete).
- Reusable table component that receives a service and emits actions (editar, eliminar, ver).

Use **botica-api-service** for the service contract; use **botica-list-dialog-crud** when the list opens a dialog to create/edit.

---

## Backend contract (reminder)

The list expects the API to accept query params: **rowsPerPage** (or per_page/page_size), **page**, **search**, **order_by** (e.g. `id` or `-id` for desc). Response must include **data** (array of rows) and **total** (or equivalent for rowsNumber).

---

## Front: pagination ref and state

```javascript
const tableRef = ref()
const rows = ref([])
const filter = ref('')
const loading = ref(false)
const pagination = ref({
  sortBy: 'id',
  descending: false,
  page: 1,
  rowsPerPage: 9,
  rowsNumber: 10,
})
```

- **rows**: array bound to the table; replaced on each successful request.
- **filter**: search term, usually bound to a top-right input with debounce.
- **pagination**: v-model for QTable; must update after each request so the table stays in sync.

---

## Front: onRequest handler

The table emits `@request` with `props` containing `pagination` and `filter`. Call the service and then update `rows` and `pagination`:

```javascript
async function onRequest(props) {
  const { page, rowsPerPage, sortBy, descending } = props.pagination
  const filter = props.filter
  loading.value = true

  const fetchCount = rowsPerPage === 0 ? 0 : rowsPerPage
  const order_by = descending ? '-' + sortBy : sortBy
  const { data, total = 0 } = await CategoriaService.getData({
    params: { rowsPerPage: fetchCount, page, search: filter, order_by },
  })

  rows.value.splice(0, rows.value.length, ...data)
  pagination.value.rowsNumber = total || data.length
  pagination.value.page = page
  pagination.value.rowsPerPage = rowsPerPage
  pagination.value.sortBy = sortBy
  pagination.value.descending = descending
  loading.value = false
}
```

If the backend returns a different shape (e.g. Laravel paginator with `current_page`, `total`), adapt the destructuring accordingly.

---

## Front: QTable template (minimal)

```vue
<q-table
  ref="tableRef"
  :rows="rows"
  :columns="columns"
  row-key="id"
  v-model:pagination="pagination"
  :loading="loading"
  :filter="filter"
  binary-state-sort
  :rows-per-page-options="[7, 10, 15]"
  flat
  :bordered="!$q.dark.isActive"
  table-header-class="my-custom"
  class="my-sticky-header-table htable q-ma-sm"
  @request="onRequest"
>
  <template v-slot:top-right>
    <q-input
      v-model="filter"
      dense
      debounce="500"
      placeholder="Buscar"
      standout="bg-primary"
      color="white"
    >
      <template v-slot:append>
        <q-icon name="search" />
      </template>
    </q-input>
  </template>
  <template v-slot:header="props">
    <q-tr :props="props">
      <q-th v-for="col in props.cols" :key="col.name" :props="props">{{ col.label }}</q-th>
      <q-th auto-width> Acciones </q-th>
    </q-tr>
  </template>
  <template v-slot:body="props">
    <q-tr :props="props">
      <q-td v-for="col in props.cols" :key="col.name" :props="props">{{ col.value }}</q-td>
      <q-td auto-width>
        <!-- editar / eliminar buttons -->
      </q-td>
    </q-tr>
  </template>
</q-table>
```

- **binary-state-sort**: required for server-side sort so the table does not sort locally.
- **row-key**: must be a unique field (usually `id`).

---

## Triggering the first load and refresh

After mount, trigger the first server request:

```javascript
onMounted(() => {
  tableRef.value.requestServerInteraction()
})
```

After create/update/delete (e.g. after closing a form dialog), refresh the table:

```javascript
tableRef.value.requestServerInteraction()
```

Some reusable table components expose **hidrateTable()** instead; use the method the component provides.

---

## Columns definition

Define **columns** as an array of objects with at least **name**, **label**, **field**, **sortable**:

```javascript
const columns = [
  { name: 'id', label: 'Id', align: 'center', field: (row) => row.id, sortable: true },
  { name: 'nombre', label: 'Nombre', align: 'left', field: (row) => row.nombre, sortable: true },
]
```

Only include **sortable: true** for columns the backend supports in **order_by**.

---

## Reusable table components

Some domains use a shared table component that receives **service** (or dataProvider), **actions** (e.g. `['editar', 'eliminar']`), and optional **clickRowAction**. The component internally holds `rows`, `pagination`, `onRequest`, and exposes **requestServerInteraction()** or **hidrateTable()** for the parent to refresh. Keep the same contract: getData with `params: { rowsPerPage, page, search, order_by }`, update rows and pagination from the response.

---

## NEVER

- Do not use client-side sorting/pagination (rows loaded all at once) when the list can grow; use server-side with `@request` and `binary-state-sort`.
- Do not forget to call `requestServerInteraction()` (or the table's refresh method) after create/update/delete so the list reflects changes.
- Do not use a different pagination param name (e.g. `per_page`) in the front without aligning with the backend; the project standard is **rowsPerPage**.
