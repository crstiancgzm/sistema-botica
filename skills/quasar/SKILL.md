---
name: quasar
description: >
  Quasar Framework (Vue 3) patterns: components, layout, plugins, Vite.
  Trigger: When writing Quasar UI (QPage, QBtn, QCard, layout), Quasar config, or Vite+Quasar setup.
license: MIT
metadata:
  author: crud-test01
  version: "1.0"
  scope: [root, front]
  auto_invoke: "Writing Quasar components or configuring Quasar"
allowed-tools: Read, Edit, Write, Glob, Grep, Bash, WebFetch, WebSearch, Task
---

## Quasar import (REQUIRED)

```javascript
// ✅ ALWAYS: named import for the plugin (no default export)
import { Quasar } from 'quasar'
import quasarUserOptions from './quasar.config.js'
app.use(Quasar, quasarUserOptions)

// ❌ NEVER
import Quasar from 'quasar'
```

## Layout and pages

```vue
<!-- ✅ Standard page with layout -->
<template>
  <q-layout view="hHh lpR fFf">
    <q-header elevated>
      <q-toolbar>
        <q-toolbar-title>App</q-toolbar-title>
      </q-toolbar>
    </q-header>
    <q-page-container>
      <q-page padding>
        <q-card flat bordered class="q-pa-md">
          <q-card-section>Content</q-card-section>
        </q-card>
      </q-page>
    </q-page-container>
  </q-layout>
</template>
```

## Use Quasar components

```vue
<!-- ✅ Prefer Quasar components for UI -->
<q-btn color="primary" label="Save" @click="save" />
<q-input v-model="name" label="Name" outlined dense />
<q-card>
  <q-card-section>...</q-card-section>
  <q-card-actions align="right">
    <q-btn flat label="Cancel" v-close-popup />
    <q-btn unelevated label="OK" color="primary" />
  </q-card-actions>
</q-card>

<!-- ❌ AVOID: raw HTML for buttons/inputs when Quasar has equivalent -->
<button>Save</button>
<input v-model="name" />
```

## useQuasar() for Notify, Loading, Dialog

```javascript
import { useQuasar } from 'quasar'

// ✅ In Composition API
const $q = useQuasar()

$q.notify({ type: 'positive', message: 'Saved' })
$q.notify({ type: 'negative', message: 'Error', caption: err.message })

const loading = $q.loading.show({ message: 'Loading...' })
// later: loading()

$q.dialog({ title: 'Confirm', message: 'Proceed?' })
  .onOk(() => { /* ... */ })
```

## quasar.config.js

```javascript
// ✅ Enable only the plugins you use
export default {
  framework: {
    config: {},
    plugins: ['Notify', 'Loading', 'Dialog'],
  },
  animations: 'all',
}
```

## Vite + Quasar plugin

```javascript
// vite.config.js
import { quasar, transformAssetUrls } from '@quasar/vite-plugin'

export default defineConfig({
  plugins: [
    vue({ template: { transformAssetUrls } }),
    quasar(),
  ],
})
```

## SASS / SCSS variables

Variables may live in `quasar.variables.sass` or `quasar.variables.scss` (e.g. in `src/css/`). Use the extension your project already uses.

```sass
// src/css/quasar.variables.sass (or quasar.variables.scss)
$primary   : #1976d2
$secondary : #26a69a
$accent    : #9c27b0
```

```sass
// In app.sass / app.scss use relative import (no sassVariables in plugin to avoid path issues)
@import './quasar.variables.sass'
```

## NEVER

- Don't use default import for Quasar: `import Quasar from 'quasar'` (fails at build).
- Don't skip QLayout/QPage for full-page views; use them for consistent toolbar and padding.
- Don't add a new axios instance per component. Use the app-provided client: `$axios`, `inject('axios')`, or when the project uses a boot file, `import { api } from 'src/boot/axios'` (follow the project's AGENTS.md).
