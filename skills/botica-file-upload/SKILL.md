---
name: botica-file-upload
description: >
  sistema-botica standard for file upload and storage in forms: ArchivosService, sync pattern, rollback on error, FormArchivos, multipart keys.
  Trigger: Adding or modifying file upload in forms (create/update with attachments), new polymorphic archivos relation, or storage/validation rules.
license: MIT
metadata:
  scope: [root, front, back]
  project: sistema-botica
  auto_invoke: "Adding or modifying file upload / guardado de archivos in forms"
allowed-tools: Read, Edit, Write, Glob, Grep, Bash, WebFetch, WebSearch, Task
---

## Overview

In sistema-botica, file uploads are handled in **forms** (usually Precognitive). The backend uses **ArchivosService** to sync polymorphic attachments (create/update/delete); files are stored on `Storage::disk('public')` with **relative paths** in the database. The front uses a reusable component (**FormArchivos** or equivalent) and sends **multipart/form-data** when the payload contains `File` objects. On controller failure, **rollback** deletes any files already written in that request.

Use this skill when adding or changing file upload for any resource. For form structure and validation, use **botica-precognitive-forms** together with this skill.

---

## When to use this skill

- Adding file upload to a new resource (new model with archivos relation).
- Modifying existing file upload (validation, storage path, allowed types, size).
- Implementing or changing the sync logic (ArchivosService), rollback, or preview URL.
- Creating or changing the front component that collects and sends files (FormArchivos or variant).

---

## Standard summary

| Aspect | Standard |
|--------|----------|
| **Storage** | `Storage::disk('public')`, folder per resource (e.g. `archivos/`) |
| **DB value** | Relative path only: `archivos/nombre.pdf` (or with unique prefix if needed) |
| **Request keys** | `{recurso}.{relacion}.{index}.url` for the file (e.g. `inventario.archivos.0.url`) |
| **Sync logic** | Single service: **ArchivosService::syncArchivos()** for create/update/delete |
| **Rollback** | Collect paths in `$uploadedFiles`; on exception, delete each before rethrowing |
| **Preview URL** | Front: `API_BASE/storage/` + `archivo.url` when `url` is a string (already saved) |

---

## Backend

### Model and relation

The parent model has a **morphMany** relation to Archivo. The Archivo model is polymorphic (`archivable_id`, `archivable_type`).

```php
// app/Models/Inventario.php
public function archivos()
{
    return $this->morphMany(Archivo::class, 'archivable');
}
```

```php
// app/Models/Archivo.php
protected $fillable = [
    'archivable_id', 'archivable_type', 'nombre', 'url',
    'tipo_1', 'tipo_2', 'aux_1', 'aux_2',
];
public function archivable() { return $this->morphTo(); }
```

Store **relative path** in `url` (e.g. `archivos/documento.pdf`), not the full URL.

### ArchivosService

Use **App\Services\ArchivosService::syncArchivos()** for polymorphic archivos:

- **Existing items** (with `id` in request): update metadata (nombre, tipo_1, etc.); do not replace file unless a new file is sent under the same key.
- **New items** (no `id`): read file from `$relationPath.$key.url`, save with `Storage::disk('public')->putFileAs($folder, $file, $filename)`, push **relative path** into `$uploadedFiles`, create Archivo with that path in `url`.
- **Deleted items**: IDs not present in the updated list are removed; delete file from disk then delete the model.

The service accepts:

- `Request $request`
- `Model $model` (parent)
- `string $relation` (e.g. `'archivos'`)
- `string $folder` (e.g. `'archivos'`) for the disk path
- `array &$uploadedFiles` — filled with relative paths of newly uploaded files in this request (for rollback)

### Controller: store/update and rollback

```php
DB::beginTransaction();
$uploadedFiles = [];
try {
    $inventario = Inventario::create($request['inventario']);
    $this->archivosService->syncArchivos(
        $request, $inventario, 'archivos', 'archivos', $uploadedFiles
    );
    $inventario->load('archivos');
    $inventario->save();
    DB::commit();
    return response()->json([...], 201);
} catch (\Exception $e) {
    DB::rollBack();
    foreach ($uploadedFiles as $path) {
        Storage::disk('public')->delete($path);
    }
    return response()->json(['message' => '...'], 500);
}
```

### FormRequest validation (archivos)

- Validate the relation as array: `recurso.archivos` => `'array'`.
- For each item: `recurso.archivos.*.nombre` (nullable|string), `recurso.archivos.*.tipo_1` (nullable|string), etc.
- For `recurso.archivos.*.url`: required (the value is either an uploaded **file** for new items or a string path for existing).

Example:

```php
"inventario.archivos"        => "array",
"inventario.archivos.*.nombre" => "nullable|string",
"inventario.archivos.*.url"    => "required",
"inventario.archivos.*.tipo_1" => "nullable|string",
```

---

## Frontend

### Sending files (multipart)

Forms that include files send **multipart/form-data** when the payload contains `File` objects. The same form (e.g. `useForm` from laravel-precognition-vue) is used; keys must match the backend: `{recurso}.{relacion}.{index}.url` (e.g. `inventario.archivos.0.url`).

### FormArchivos component

The shared component **FormArchivos** (e.g. in `src/components/FormArchivos.vue`) typically:

- Uses `defineModel()` for the list of archivos (array of `{ id?, nombre, url, tipo_1, ... }`).
- For **new** items, `url` is a **File** object; for **existing** (from server), `url` is a **string** (relative path).
- Input: `<input type="file" accept="application/pdf" />`. On select, validate type/size, then push to the model array with `url: file` and `nombre` (derived from file name).
- Remove: filter out the item from the array.
- Preview: for existing (stored) items, `url` is string → show link with `API_BASE/storage/` + `archivo.url`.

Reference: `front-botica/src/components/FormArchivos.vue`.

### Preview URL

Base URL for preview/download: `import.meta.env.VITE_API_BACKEND_URL` (or equivalent). Full URL for an existing archivo: `${base}/storage/${archivo.url}` when `archivo.url` is a string. Ensure the backend serves public storage (`php artisan storage:link` for `public` disk).

### FormData shape

In the form data module (e.g. FormInventario.js), include the relation as an array: `archivos: []`. The parent form binds this to FormArchivos via v-model.

---

## Naming and locations

| Item | Convention |
|------|------------|
| Service | `App\Services\ArchivosService` |
| Method | `syncArchivos(Request, Model, relation, folder, &$uploadedFiles)` |
| Front component | `FormArchivos.vue` in `src/components/` (shared) or domain folder if specific |
| FormData key | Same as relation: e.g. `archivos` under `inventario` |
| Request file key | `{recurso}.{relacion}.{index}.url` |

---

## NEVER

- Do NOT store full URL in the database; store only the relative path.
- Do NOT forget rollback: always delete `$uploadedFiles` on exception in store/update.
- Do NOT use a different sync pattern for new resources; use ArchivosService (or the same pattern) for consistency.
- Do NOT send stored items as files again: in the front, items with string `url` are existing; only items with `url` as `File` are uploaded.

---

## Checklist (new resource with file upload)

**Back**

- [ ] Model has morphMany to Archivo; Archivo has morphTo.
- [ ] ArchivosService::syncArchivos() called in store/update with correct relation and folder.
- [ ] `$uploadedFiles` filled and, on exception, every path deleted before rethrow/response.
- [ ] FormRequest validates `recurso.archivos` and `recurso.archivos.*.*` as needed.
- [ ] Only relative path stored in `url`.

**Front**

- [ ] FormData includes `recurso.relacion` as array (e.g. `inventario.archivos: []`).
- [ ] FormArchivos (or variant) bound with v-model to that array; new items have `url` as File, existing as string.
- [ ] Submit uses same form as rest (multipart when there are File objects); keys match backend.
- [ ] Preview uses `API_BASE/storage/` + `archivo.url` when `url` is string.
