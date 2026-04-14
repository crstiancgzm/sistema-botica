---
name: laravel-api
description: >
  Laravel API patterns: Eloquent, controllers, FormRequest, routes, JSON responses.
  Trigger: When creating or modifying API endpoints, models, migrations, or validation in Laravel.
license: MIT
metadata:
  author: crud-test01
  version: "1.0"
  scope: [root, back]
  auto_invoke: "Creating Laravel API endpoints or models"
allowed-tools: Read, Edit, Write, Glob, Grep, Bash, WebFetch, WebSearch, Task
---

## Thin controllers (REQUIRED)

```php
// ✅ Controller delegates to model or service
public function index()
{
    $items = Item::query()->latest()->paginate(15);
    return response()->json(['data' => $items]);
}

public function store(StoreItemRequest $request)
{
    $item = Item::create($request->validated());
    return response()->json(['data' => $item], 201);
}

// ❌ NEVER: business logic or raw queries in controller
public function store(Request $request)
{
    DB::table('items')->insert([...]);
    // complex logic here
}
```

## Eloquent models

```php
// ✅ $fillable (or $guarded), $casts, timestamps
class Item extends Model
{
    protected $fillable = ['name', 'description', 'user_id'];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata'  => 'array',
    ];
}
```

## FormRequest for validation

```php
// ✅ Dedicated FormRequest for non-trivial input
// php artisan make:request StoreItemRequest
class StoreItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
}
```

Some projects use **nested request keys** (e.g. `vehiculo.nplaca`, `vehiculo.empresa_id`) to match the front-end payload. In that case use `$request['vehiculo']` in the controller and rules like `'vehiculo.nplaca' => 'required|string'`. For list endpoints with filter/search/pagination, the project may use a base controller helper (e.g. `generateViewSetList`); for forms with Laravel Precognition, routes may use `HandlePrecognitiveRequests`. **Follow the backend AGENTS.md** for these project-specific conventions.

## API response format

```php
// ✅ Consistent JSON shape
return response()->json(['data' => $resource]);
return response()->json(['data' => $resource], 201);
return response()->json(['message' => 'Deleted'], 204);

// Validation error (422)
return response()->json([
    'message' => 'The given data was invalid.',
    'errors'  => $validator->errors(),
], 422);

// ❌ AVOID: inconsistent keys (data vs result vs item)
```

## API routes

```php
// routes/api.php (prefix /api by default in Laravel)
use App\Http\Controllers\Api\ItemController;

Route::apiResource('items', ItemController::class);

// Or explicit
Route::get('/items', [ItemController::class, 'index']);
Route::post('/items', [ItemController::class, 'store']);
Route::get('/items/{item}', [ItemController::class, 'show']);
Route::put('/items/{item}', [ItemController::class, 'update']);
Route::delete('/items/{item}', [ItemController::class, 'destroy']);
```

## Migrations

```php
// ✅ Create migration: php artisan make:migration create_items_table
Schema::create('items', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->timestamps();
});
```

## Naming

| Entity       | Pattern              | Example                |
|-------------|----------------------|------------------------|
| Model       | PascalCase singular  | `Item`, `User`         |
| Controller  | PascalCase + Controller | `ItemController`    |
| FormRequest | Action + Request     | `StoreItemRequest`     |
| Route name  | api.resource.action  | `api.items.index`      |

## NEVER

- Don't put business logic in controllers; use model scopes, actions, or service classes.
- Don't use raw DB queries when Eloquent can do it.
- Don't skip validation; use FormRequest or `$request->validate()`.
- Don't return different JSON structures for the same resource type.
