<template>
  <q-layout>
    <q-page-container>
      <q-page class="page-bg">
        <LoginForm @logued="onLogued" />
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref } from 'vue';
import LoginForm from 'pages/Auth/LoginForm.vue'
import SwitchDarkMode from 'src/components/SwitchDarkMode.vue';
import { useRouter } from 'vue-router'

const router = useRouter()

const onLogued = () => {
  router.push({ name: 'Inventario' })
}
</script>

<style>
.page-bg {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 32px 16px;

  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 320'%3E%3Cpath fill='%23ff84b0' d='M0,256L40,213.3C80,171,160,85,240,48C320,11,400,21,480,64C560,107,640,181,720,202.7C800,224,880,192,960,154.7C1040,117,1120,75,1200,90.7C1280,107,1360,181,1400,218.7L1440,256L1440,320L0,320Z'/%3E%3C/svg%3E");

  background-position: bottom;
  background-size: cover;
}
</style>