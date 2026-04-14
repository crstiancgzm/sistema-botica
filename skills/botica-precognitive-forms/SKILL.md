---
name: botica-precognitive-forms
description: >
  sistema-botica forms with real-time validation via Laravel Precognition.
  Front: laravel-precognition-vue + Quasar. Back: HandlePrecognitiveRequests + nested FormRequest.
  Trigger: Forms with Precognitive validation, new resources with live validation, FormRequest or route changes that affect validation.
license: MIT
metadata:
  scope: [root, front, back]
  project: sistema-botica
  auto_invoke: "Forms with Precognitive validation, FormRequest for Vue forms"
allowed-tools: Read, Edit, Write, Glob, Grep, Bash, WebFetch, WebSearch, Task
---

## Overview

In sistema-botica, forms with real-time validation use **Laravel Precognition**: the front sends validation requests to the same API endpoints; the back runs the same FormRequest rules and returns 422 with `errors`. The front uses `laravel-precognition-vue` and the same axios instance from boot. Back routes that receive these forms must use `HandlePrecognitiveRequests` and FormRequest with **nested keys** matching the front payload (e.g. `categoria.nombre`).

**Forms that include file upload**: use the **botica-file-upload** skill for storage, sync, rollback, multipart keys, and the FormArchivos component. This skill covers form structure and validation; botica-file-upload covers the file-specific contract and backend storage.

---

## Backend requirements

### Middleware

Any route that receives Vue forms with Precognitive validation MUST use:

```php
// routes/api.php
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;

Route::apiResource('categorias', CategoriaController::class)
    ->middleware([HandlePrecognitiveRequests::class]);
```

### FormRequest (nested keys)

Rules MUST use the same nested key as the front payload. The controller uses `$request['resource_key']` in store/update.

```php
// app/Http/Requests/StoreCategoriaRequest.php
public function rules(): array
{
    return [
        'categoria.nombre'       => 'required|string|max:255',
        'categoria.descripcion'  => 'nullable|string',
        // ...
    ];
}
```

### Controller

Use the nested key in store/update:

```php
public function store(StoreCategoriaRequest $request)
{
    $item = Categoria::create($request['categoria']);
    return response()->json($item, 201);
}

public function update(UpdateCategoriaRequest $request, Categoria $categoria)
{
    $categoria->update($request['categoria']);
    return response()->json($categoria);
}
```

### CORS

`config/cors.php` must expose Precognition headers:

```php
'exposed_headers' => ['precognition', 'precognition-success'],
```

---

## Frontend: Boot and client

The Precognition client MUST use the same axios instance used for API calls (from boot):

```javascript
// src/boot/axios.js
import { client } from 'laravel-precognition-vue'

const api = axios.create({ baseURL: import.meta.env.VITE_API_BACKEND_URL })
// ... interceptors (e.g. auth token) ...

client.use(api)

export { api }
```

Forms and services use `import { api } from 'src/boot/axios'` (or the path your project uses).

---

## Frontend: FormData (initial state)

Each resource has a JS module that exports the **initial form shape** with the same nested key as the backend. File naming: `FormCategoria.js`, `FormInventario.js`, etc., next to the form/page or in the same domain folder.

```javascript
// src/pages/Categorias/FormCategoria.js
const formCategoria = {
  categoria: {
    id: null,
    nombre: '',
    descripcion: '',
  },
}

export default formCategoria
```

Keys and nesting MUST match the FormRequest rules and controller (`categoria.*`).

---

## Frontend: useForm

- **Create**: `useForm('post', 'api/categorias', formCategoria)`
- **Update**: `useForm('put', 'api/categorias/' + id, formCategoria)`

Initialize the form ref conditionally (create vs edit):

```javascript
import { useForm } from 'laravel-precognition-vue'
import formCategoria from './FormCategoria'

const form = ref(null)
if (props.id) {
  form.value = useForm('put', 'api/categorias/' + props.id, formCategoria)
} else {
  form.value = useForm('post', 'api/categorias', formCategoria)
}
```

---

## Pattern 1: Form in page/dialog (submit)

Used in full-page or dialog forms (e.g. CategoriasForm, LaboratoriosForm).

1. The parent holds the form ref and passes it to a child component via **v-model** and **prop-path** (the nested key, e.g. `categoria`).
2. Child uses `defineModel()` and a computed to get the nested object for binding.
3. Each field: `v-model`, `@change="model.validate(propPath + '.field')"`, `:error="model.invalid(propPath + '.field')"`, and error message from `model.errors[propPath + '.field']`.
4. Submit with `form.submit().then(...).catch(...)`.

### Parent (page/dialog)

```vue
<template>
  <form @submit.prevent="submit">
    <q-card-section>
      <ComponenteForm v-model="form" prop-path="categoria" />
    </q-card-section>
    <q-card-actions align="right">
      <q-btn type="submit" color="positive" label="Guardar" />
    </q-card-actions>
  </form>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from 'laravel-precognition-vue'
import ComponenteForm from './ComponenteCategoria.vue'
import formCategoria from './FormCategoria'

const form = ref(null)
if (props.id) {
  form.value = useForm('put', 'api/categorias/' + props.id, formCategoria)
} else {
  form.value = useForm('post', 'api/categorias', formCategoria)
}

const submit = () => {
  form.value
    .submit()
    .then((response) => {
      emit('save', response.data)
      form.value.reset()
    })
    .catch((error) => {
      // handle error / notify
    })
}
</script>
```

### Child (subform component)

Use **prop-path** (e.g. `categoria`) and **defineModel()** to bind to the form object. Resolve the nested value for v-model with a computed:

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
  propPath: String,
  readonly: { type: Boolean, default: false },
  disableInputList: { type: Array, default: () => [] },
})

const model = defineModel()

// Nested object for v-model binding (e.g. form.categoria)
const valor = computed(() => {
  return props.propPath.split('.').reduce((o, i) => (o ? o[i] : undefined), model.value)
})
</script>

<template>
  <q-input
    dense
    outlined
    v-model="valor.nombre"
    label="Nombre"
    :loading="model.validating"
    @change="model.validate(propPath + '.nombre')"
    :error="model.invalid(propPath + '.nombre')"
  >
    <template v-slot:error>
      <div>{{ model.errors[propPath + '.nombre'] }}</div>
    </template>
  </q-input>
</template>
```

For custom components (e.g. select async), pass through error state:

```vue
<selectGeneralAsyncrono
  v-model:id="valor.laboratorio_id"
  :error="model.invalid(propPath + '.laboratorio_id')"
  :errorMessages="model.errors[propPath + '.laboratorio_id']"
  ...
/>
```

---

## Pattern 2: Preview / inline create-edit (validate + save)

Used when the form is inside a preview or stepper; create and edit switch by reinitializing the form and using `setData`. Submit via `form.validate({ only, onSuccess, onValidationError })` and call the domain service with the form object.

```javascript
const precognitionForm = ref(null)
precognitionForm.value = useForm('post', 'api/categorias', formCategoria)

const crearCategoria = async () => {
  precognitionForm.value.validate({
    only: ['categoria.nombre'],
    onSuccess: async () => {
      const categoria = await CategoriaService.save(precognitionForm.value)
      notifySuccess('Categoría creada correctamente')
      emit('categoriaCreado', categoria)
    },
    onValidationError: (response) => {
      notifyError(response.data.message)
    },
  })
}
```

---

## Quasar binding (summary)

| Need | Usage |
|------|--------|
| Loading state | `:loading="form.validating"` or `form.processing` |
| Error state | `:error="form.invalid(propPath + '.field')"` |
| Error message | `form.errors[propPath + '.field']` in slot `#error` or `:error-messages` |
| Validate on change | `@change="form.validate(propPath + '.field')"` |
| Disable while submitting | `form.processing` or `form.validating` |

Use `propPath + '.field'` so the path matches the FormRequest keys (e.g. `categoria.nombre`).

---

## Naming and location (sistema-botica)

| Item | Convention | Example |
|------|------------|---------|
| FormData module | `FormXxx.js` | `FormCategoria.js`, `FormInventario.js` |
| Subform component | `ComponenteXxx.vue` when it's a reusable block for one resource | `ComponenteCategoria.vue` |
| Page form | `XxxForm.vue` or `XxxsForm.vue` | `CategoriasForm.vue`, `LaboratoriosForm.vue` |
| Preview with inline form | `XxxPreview.vue` | `CategoriaPreview.vue` |

Keep FormData and the subform component in the same domain folder (e.g. `pages/Categorias/`). The payload key (e.g. `categoria`) must match the FormRequest nesting and the backend controller `$request['categoria']`.

---

## NEVER

- Do NOT add a route that receives Precognitive form requests without `HandlePrecognitiveRequests::class`.
- Do NOT use flat request keys in FormRequest if the front sends nested payloads; use `resource_key.field` and `$request['resource_key']` in the controller.
- Do NOT use a different axios instance for Precognition; use `client.use(api)` with the same `api` from boot.
- Do NOT mismatch FormData keys (front) and FormRequest rules (back); they must be identical (e.g. `categoria.nombre` everywhere).
- Do NOT validate only on the front; the backend FormRequest is the source of truth for validation rules.

---

## Checklist (new Precognitive resource)

**Back**
- [ ] Route has `HandlePrecognitiveRequests::class`.
- [ ] Store/Update FormRequest with nested rules (`resource_key.field`).
- [ ] Controller uses `$request['resource_key']` in store/update.
- [ ] CORS exposes `precognition`, `precognition-success` (if not already).

**Front**
- [ ] Boot has `client.use(api)`.
- [ ] FormData module with same nested shape as backend.
- [ ] useForm('post', url, formData) for create, useForm('put', url + id, formData) for update.
- [ ] Subform uses prop-path and validate/invalid/errors with `propPath + '.field'`.
- [ ] Submit via `form.submit()` or `form.validate({ only, onSuccess, onValidationError })` and service.save(form) where applicable.
