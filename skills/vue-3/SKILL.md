---
name: vue-3
description: >
  Vue 3 patterns: Composition API, script setup, ref/reactive, composables.
  Trigger: When writing Vue 3 components (.vue), Pinia stores, or composables in JavaScript/TypeScript.
license: MIT
metadata:
  author: crud-test01
  version: "1.0"
  scope: [root, front]
  auto_invoke: "Writing Vue 3 components or stores"
allowed-tools: Read, Edit, Write, Glob, Grep, Bash, WebFetch, WebSearch, Task
---

## Prefer `<script setup>` (REQUIRED)

```javascript
// ✅ ALWAYS: script setup for single-file components
<script setup>
import { ref, computed, onMounted } from 'vue'

const count = ref(0)
const doubled = computed(() => count.value * 2)

onMounted(() => {
  console.log('mounted')
})
</script>

<template>
  <button @click="count++">{{ doubled }}</button>
</template>

// ❌ AVOID in new code: Options API unless migrating legacy
<script>
export default {
  data() { return { count: 0 } },
  computed: { doubled() { return this.count * 2 } },
}
</script>
```

## ref vs reactive

```javascript
// ✅ ref for primitives and when you need .value in script
const count = ref(0)
const name = ref('')
count.value++

// ✅ reactive for object-shaped state (no .value)
const state = reactive({ items: [], loading: false })
state.items.push(item)

// ✅ ref for objects when you replace the whole object
const user = ref({ name: '' })
user.value = { name: 'New' }
```

## Composables (reusable logic)

```javascript
// ✅ useXxx in composables/ or alongside feature
import { ref, onMounted } from 'vue'

export function useFetch(url) {
  const data = ref(null)
  const loading = ref(true)
  const error = ref(null)

  onMounted(async () => {
    try {
      const res = await fetch(url)
      data.value = await res.json()
    } catch (e) {
      error.value = e
    } finally {
      loading.value = false
    }
  })

  return { data, loading, error }
}
```

## Pinia (global state)

For shared state across the app, use Pinia. Keep stores in `src/stores/` (e.g. `auth`, view state). Use `defineStore` with `state`, `getters`, and `actions`; add `pinia-plugin-persistedstate` if persistence is needed. Prefer composables for stateless reuse; Pinia for state that multiple pages need. See the project's AGENTS.md for exact store patterns.

## inject / provide

```javascript
// ✅ provide in app (e.g. main.js or root component)
import { createApp } from 'vue'
const app = createApp(App)
app.provide('axios', axiosInstance)

// ✅ inject in child (Composition API)
import { inject } from 'vue'
const axios = inject('axios')
// Optional default: inject('axios', fallback)
```

When the project uses a boot file for the API client (e.g. Quasar boot), prefer `import { api } from 'src/boot/axios'` over provide/inject if that is the project convention.

## Props and emit

```javascript
// ✅ defineProps (no need to return)
const props = defineProps({
  modelValue: { type: String, default: '' },
  count: Number,
})

// ✅ defineEmits
const emit = defineEmits(['update:modelValue', 'submit'])
emit('update:modelValue', newValue)
```

## Template refs

```javascript
// ✅ ref in script, same name in template
const inputEl = ref(null)
onMounted(() => {
  inputEl.value?.focus()
})
// template: <input ref="inputEl" />
```

## Prefer direct handlers over watch()

Avoid `watch()` when the logic can run from a user action or lifecycle. Use it only when you must react to reactive changes with no event.

```javascript
// ❌ AVOID: watch para reaccionar a un clic que abre un diálogo
watch(dialogOpen, async (val) => {
  if (val) {
    await nextTick()
    formRef.value?.updateFormData(data)
  }
})
// @click="dialogOpen = true"

// ✅ PREFER: handler directo
async function abrirDialog() {
  dialogOpen.value = true
  await nextTick()
  formRef.value?.updateFormData(data)
}
// @click="abrirDialog"
```

```javascript
// ❌ AVOID: watch para recargar cuando cambia un prop
watch(() => props.id, () => reload())

// ✅ PREFER: :key en el padre para forzar remount
// Parent: <Child :key="id" :id="id" />
// Child: onMounted(() => reload()) — se ejecuta al montar (incl. cuando key cambia)
```

**Usar watch() solo cuando:** no hay evento ni lifecycle que dispare la lógica (ej. reaccionar a cambios de un store externo que no controlas).

## NEVER in Vue 3 SFC

- Don't use Vue 2 Options API patterns in new code unless migrating.
- Don't mutate props; use emit or v-model.
- Don't forget `.value` when reading/writing refs inside script (not in template).
