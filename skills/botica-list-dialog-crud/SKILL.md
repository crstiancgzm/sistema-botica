---
name: botica-list-dialog-crud
description: >
  sistema-botica list page + dialog for create/edit + table refresh on save. Pattern: open dialog with id (null = create), form or preview inside, on save close dialog and refresh table.
  Trigger: When adding or modifying a list screen that opens a dialog (or drawer) to create or edit a record.
license: MIT
metadata:
  scope: [root, front]
  project: sistema-botica
  auto_invoke: "List page with create/edit in dialog; dialog + table refresh flow"
allowed-tools: Read, Edit, Write, Glob, Grep, Bash, WebFetch, WebSearch, Task
---

## Overview

In sistema-botica, many list pages follow the same flow: a **list/table** (see **botica-list-table**) plus a **dialog** that opens to create or edit a record. On **save** (or create/update event), the dialog closes and the **table is refreshed** so the list shows the new or updated data. Two common variants: (1) dialog with a **form component** (e.g. CategoriaForm) that receives `id` and `edit`; (2) dialog with a **preview component** that receives an `id` (or null for create) and emits `creado` / `actualizado`.

---

## When to use this skill

- New list page that has an "Agregar" (or similar) button and opens a dialog to create/edit.
- Modifying an existing list + dialog flow (e.g. changing how the table refreshes or how the dialog is opened for edit).

Use **botica-list-table** for the table and **botica-precognitive-forms** for the form inside the dialog when using Precognitive.

---

## Pattern 1: Dialog with form component (id + edit flag)

Used when the dialog content is a single form (e.g. CategoriaForm, LaboratorioForm) that supports create and edit via props.

**State:**

```javascript
const formDialog = ref(false)
const edit = ref(false)
const editId = ref(null)
const title = ref('')
const formRef = ref()
const tableRef = ref()
```

**Open for create:**

```javascript
const crear = () => {
  editId.value = null
  edit.value = false
  title.value = 'Añadir Categoría'
  formDialog.value = true
}
```

**Open for edit:**

```javascript
const editar = async (id) => {
  title.value = 'Editar Categoría'
  editId.value = id
  edit.value = true
  formDialog.value = true
  // Optional: preload form with Service.get(id) and formRef.value.form.setData(...)
}
```

**On save (emitted by form):** close dialog and refresh table; optionally notify.

```javascript
const save = () => {
  formDialog.value = false
  tableRef.value.requestServerInteraction()
  $q.notify({ type: 'positive', message: 'Guardado con Exito.', position: 'top-right', progress: true, timeout: 1000 })
}
```

**Template:**

```vue
<q-dialog v-model="formDialog" persistent>
  <CategoriaForm :title="title" :edit="edit" :id="editId" ref="formRef" @save="save" />
</q-dialog>
<q-page>
  <q-btn outline color="primary" label="Agregar" icon-right="add" @click="crear" />
  <q-card>
    <q-table ref="tableRef" ... @request="onRequest">
      ...
      <q-btn @click="editar(props.row.id)" icon="edit" />
    </q-table>
  </q-card>
</q-page>
```

---

## Pattern 2: Dialog with preview component (id or null)

Used when the dialog content is a preview/create-edit component that handles both create and edit and emits events when done.

**State:**

```javascript
const formDialog = ref(false)
const previewId = ref(null)   // null = create, number = edit
const tableRef = ref()
```

**Open for create:**

```javascript
const crear = () => {
  previewId.value = null
  formDialog.value = true
}
```

**On created / updated (emitted by preview):** close dialog and refresh table.

```javascript
const onCreado = () => {
  formDialog.value = false
  tableRef.value.hidrateTable()
}
const onActualizado = () => {
  formDialog.value = false
  tableRef.value.hidrateTable()
}
```

**Template:**

```vue
<q-dialog v-model="formDialog" persistent>
  <q-card>
    <q-card-section class="bg-primary text-white text-h6">
      <div class="row items-center">
        <div class="col">{{ previewId ? 'Editar Categoría' : 'Crear Categoría' }}</div>
        <q-btn icon="close" flat round dense v-close-popup />
      </div>
    </q-card-section>
    <q-card-section>
      <CategoriaPreview
        :categoriaId="previewId"
        :allowCreate="true"
        @categoriaCreado="onCreado"
        @categoriaActualizado="onActualizado"
      />
    </q-card-section>
  </q-card>
</q-dialog>
<q-page>
  <q-btn outline color="primary" label="Agregar" icon-right="add" @click="crear" />
  <q-card>
    <CategoriasTable ref="tableRef" ... />
  </q-card>
</q-page>
```

Here the table may be a **reusable component** (CategoriasTable) that exposes **hidrateTable()** instead of **requestServerInteraction()**; use the method that the table component provides.

---

## Summary

| Step | Action |
|------|--------|
| Open create | Set id to null (and edit to false if using form component); set dialog v-model to true. |
| Open edit | Set id to row.id (and edit to true if using form); set dialog v-model to true. |
| On save / creado / actualizado | Set dialog v-model to false; call tableRef.requestServerInteraction() or tableRef.hidrateTable(). |
| Optional | useNotify or $q.notify after save. |

---

## NEVER

- Do not forget to refresh the table after save; otherwise the list will not show the new or updated record until the user reloads.
- Do not mix patterns without reason: if the list uses a reusable table component, use the refresh method it exposes (e.g. hidrateTable), not necessarily requestServerInteraction.
