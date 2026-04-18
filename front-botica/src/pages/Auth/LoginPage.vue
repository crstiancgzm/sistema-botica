<template>
  <q-layout>
    <q-page-container>
      <q-page class="page-full">
        <LoginForm @logued="onLogued" />
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script setup>
import LoginForm from 'pages/Auth/LoginForm.vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const onLogued = () => {
  router.push({ name: 'Dashboard' })
}
</script>

<style>
.page-full {
  min-height: 100vh !important;
  padding: 0 !important;
  display: flex;
}
</style>
