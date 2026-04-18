<template>
  <div class="login-root">

    <!-- ══════════ LEFT PANEL ══════════ -->
    <div class="lp-left">
      <div class="lp-inner">

        <!-- Logo + brand -->
        <div class="lp-brand">
          <img src="/images/logo.jpeg" alt="Logo" class="lp-logo" />
          <!-- <span class="lp-brand-name">Boticas Puno</span> -->
        </div>

        <!-- Heading -->
        <h1 class="lp-heading">¡Bienvenido<br/>de nuevo!</h1>
        <p class="lp-sub">Ingresa tus credenciales para acceder al sistema de gestión.</p>

        <!-- Form -->
        <q-form @submit="login" class="lp-form">

          <div class="lp-field">
            <label class="lp-label">Correo electrónico</label>
            <q-input
              v-model="formLogin.email"
              outlined
              dense
              placeholder="correo@botica.com"
              
              class="lp-input"
            >
              <template #prepend>
                <q-icon name="mail_outline" color="grey-5" size="20px" />
              </template>
            </q-input>
          </div>

          <div class="lp-field">
            <label class="lp-label">Contraseña</label>
            <q-input
              v-model="formLogin.password"
              outlined
              dense
              :type="isPwd ? 'password' : 'text'"
              placeholder="••••••••"
              
              class="lp-input"
            >
              <template #prepend>
                <q-icon name="lock_outline" color="grey-5" size="20px" />
              </template>
              <template #append>
                <q-icon
                  :name="isPwd ? 'visibility_off' : 'visibility'"
                  color="grey-4"
                  size="20px"
                  class="cursor-pointer"
                  @click="isPwd = !isPwd"
                />
              </template>
            </q-input>
          </div>

          <div class="lp-forgot-row">
            <span class="lp-forgot">¿Olvidaste tu contraseña?</span>
          </div>

          <q-btn
            type="submit"
            unelevated
            no-caps
            :loading="loading"
            class="lp-btn"
          >
            Iniciar Sesión
          </q-btn>

        </q-form>

        <p class="lp-footer">Sistema exclusivo para personal autorizado</p>
      </div>
    </div>

    <!-- ══════════ RIGHT PANEL ══════════ -->
    <div class="lp-right">
      <!-- Blobs decorativos -->
      <div class="blob blob-1"></div>
      <div class="blob blob-2"></div>
      <div class="blob blob-3"></div>

      <!-- Texto superior -->
      <div class="lp-right-header">
        <div class="lp-badge">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" fill="#fff"/></svg>
          Sistema de Gestión
        </div>
        <h2 class="lp-right-title">Siempre pensando<br/><strong>en tu bienestar</strong></h2>
      </div>

      <!-- Ilustración SVG -->
      <div class="lp-svg-wrap">
        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
          viewBox="0 0 1105.04 782.74" class="lp-illustration">
          <defs>
            <linearGradient id="lg1" x1="403.57" y1="833.5" x2="403.57" y2="218.16" gradientUnits="userSpaceOnUse">
              <stop offset="0" stop-color="#fff" stop-opacity="0.2"/>
              <stop offset="1" stop-color="#fff" stop-opacity="0.05"/>
            </linearGradient>
          </defs>
          <!-- Heart shape (pharmacy cross) -->
          <path d="M753.1,110.05h-.75c-29.84,0-56.23,15.79-71.41,39.09-15.18-23.3-41.57-39.09-71.41-39.09h-.75A85,85,0,0,0,524.59,195c0,27.81,12.18,67.27,35.93,99.74,45.25,61.87,120.42,116,120.42,116s75.17-54.12,120.41-116c23.76-32.47,35.93-71.93,35.93-99.74A85,85,0,0,0,753.1,110.05ZM742.37,299.5a16.85,16.85,0,0,1-15.67-10.82H708.86a5.64,5.64,0,0,1-5.31-3.84l-5.48-16.51-17.34,60.59a5.61,5.61,0,0,1-5.38,4.05H675a5.54,5.54,0,0,1-5.2-4.64L651.93,220.85l-13.06,63.6a5.57,5.57,0,0,1-5.41,4.23H608.33a5.59,5.59,0,1,1,0-11.17h20.73L647.56,192a5.61,5.61,0,0,1,11,.46l18.25,109.64,15.54-54.21a5.5,5.5,0,0,1,5.23-4h.11a5.58,5.58,0,0,1,5.3,3.84l9.91,29.81h13.58a16.76,16.76,0,1,1,15.92,22Z" transform="translate(-47.24 -58.63)" fill="rgba(255,255,255,0.9)"/>
          <!-- Person left -->
          <!-- <path d="M442.25,277.9a33.87,33.87,0,0,1-1.68,10.61,34.22,34.22,0,1,1,68.43,0Z" transform="translate(-47.24 -58.63)" fill="rgba(255,255,255,0.7)"/>
          <path d="M413.45,353c-13.2-5.13-30.18-22.08-25.94-30a17.72,17.72,0,0,0,1.65-6.36,58.07,58.07,0,0,0,.12-9A123.25,123.25,0,0,0,387.51,293s41.21.93,38.81,7c-.7,1.78-.81,4.87-.62,8.33s.69,7.37,1.21,10.68c.72,4.54,1.44,8,1.44,8s-2.52,3.53-4,3.72S432.39,360.31,413.45,353Z" transform="translate(-47.24 -58.63)" fill="rgba(255,255,255,0.6)"/>
          <polygon points="412.94 360 397.85 613.5 378.17 614.79 327.21 467.8 335.57 293.29 337.33 256.42 359.99 275.03 371.18 284.2 379.93 261.93 396.2 276.11 412.94 360" fill="rgba(255,255,255,0.5)"/> -->
          <!-- Person right -->
          <!-- <path d="M982.82,317.92a29.61,29.61,0,0,1-5.22,16.81,29.66,29.66,0,1,1,5.22-16.81Z" transform="translate(-47.24 -58.63)" fill="rgba(255,255,255,0.7)"/>
          <path d="M960.66,357.15c-1.31,2-5.06,12.24-8.19,21.18-2.93-1.13-5.59-8.41-5.59-8.41l15.62-22.21C964.1,349.9,963.05,353.56,960.66,357.15Z" transform="translate(-47.24 -58.63)" fill="rgba(255,255,255,0.5)"/>
          <path d="M1053.09,646c-27.35,13.58-77.06,1-77.06,1-5,4.6-18.17,11.38-18.17,11.38l-7.19-89c-4.58-12-6.38-74.87-6.38-74.87s5.78-46.91,8.18-51.7c.7-1.4.36-3.48-.47-5.72a72.38,72.38,0,0,1-4.38-33.49l1.92-16.31.53-4.57c9.39-4.2,16.58-17.58,16.58-17.58l5-8.76,9.2-16.14,4.36-7.64.74.67,5.65,5.12s2.4,2,6,5c7.83,6.6,21.36,18.22,28.34,25.33,6.14,6.26,8.08,30.61,8.64,48.94.37,12.07.15,21.53.15,21.53-6.19,10.19,10,92,10,92C1048.5,547,1053.09,646,1053.09,646Z" transform="translate(-47.24 -58.63)" fill="rgba(255,255,255,0.4)"/> -->
          <!-- Plant / tree decorative -->
          <!-- <path d="M128.25,556.68C164.76,583.1,180,624.42,180,624.42s-44-1.51-80.52-27.93-51.73-67.73-51.73-67.73S91.73,530.26,128.25,556.68Z" transform="translate(-47.24 -58.63)" fill="rgba(255,255,255,0.3)"/>
          <path d="M208.85,539.18c-22.83,25.09-27.66,57.88-27.66,57.88s32.19-7.9,55-33,27.66-57.88,27.66-57.88S231.68,514.09,208.85,539.18Z" transform="translate(-47.24 -58.63)" fill="rgba(255,255,255,0.3)"/> -->
        </svg>
      </div>

      <!-- Floating stat card -->
      <div class="lp-stat-card">
        <div class="lp-stat-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z" stroke="#ff84b0" stroke-width="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" stroke="#ff84b0" stroke-width="2"/><line x1="12" y1="12" x2="12" y2="16" stroke="#ff84b0" stroke-width="2" stroke-linecap="round"/><line x1="10" y1="14" x2="14" y2="14" stroke="#ff84b0" stroke-width="2" stroke-linecap="round"/></svg>
        </div>
        <div>
          <p class="lp-stat-title">Gestión Integral</p>
          <p class="lp-stat-sub">Inventario · Ventas · Reportes</p>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { useQuasar } from 'quasar'
import { ref } from 'vue'
import { useUserStore } from 'src/stores/user-store'

const loading = ref(false)
const $q = useQuasar()
const emits = defineEmits(['logued'])
const userStore = useUserStore()
const formLogin = ref({ email: '', password: '' })
const isPwd = ref(true)

const login = async () => {
  loading.value = true
  try {
    await userStore.login(formLogin.value.email, formLogin.value.password)
    await userStore.getUser()
    emits('logued')
    $q.notify({ position: 'top', type: 'positive', message: 'Sesión iniciada con éxito', timeout: 1000 })
  } catch (e) {
    $q.notify({
      position: 'top',
      type: 'negative',
      message: e.response ? 'Credenciales incorrectas' : 'Sin conexión con el servidor',
    })
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* ── Root ── */
.login-root {
  display: flex;
  width: 100%;
  min-height: 100vh;
}

/* ══════════ LEFT PANEL ══════════ */
.lp-left {
  flex: 0 0 420px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 48px 40px;
  position: relative;
  z-index: 2;
  box-shadow: 4px 0 40px rgba(255, 41, 41, 0.06);
}

.lp-inner {
  width: 100%;
  max-width: 340px;
}

/* Brand */
.lp-brand {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 36px;
}

.lp-logo {
  width: 100%;
  border-radius: 10px;
  object-fit: cover;
}

.lp-brand-name {
  font-size: 0.9rem;
  font-weight: 700;
  color: #ff84b0;
  letter-spacing: 0.02em;
}

/* Heading */
.lp-heading {
  font-size: 2rem;
  font-weight: 800;
  line-height: 1.15;
  margin: 0 0 10px;
  letter-spacing: -0.5px;
}

.lp-sub {
  font-size: 0.85rem;
  color: #6b7280;
  margin: 0 0 32px;
  line-height: 1.6;
}

/* Form */
.lp-form {
  display: flex;
  flex-direction: column;
  gap: 0;
}

.lp-field {
  margin-bottom: 16px;
}

.lp-label {
  display: block;
  font-size: 0.78rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 6px;
  letter-spacing: 0.02em;
}

.lp-input :deep(.q-field__control) {
  border-radius: 10px;
  height: 48px;
}

.lp-input :deep(.q-field__control:before) {
  border-color: #e5e7eb;
}

.lp-forgot-row {
  display: flex;
  justify-content: flex-end;
  margin: 4px 0 20px;
}

.lp-forgot {
  font-size: 0.78rem;
  color: #ff84b0;
  font-weight: 600;
  cursor: pointer;
  transition: color 0.2s;
}

.lp-forgot:hover {
  color: #c2185b;
}

.lp-btn {
  width: 100%;
  height: 52px;
  border-radius: 12px !important;
  background: linear-gradient(135deg, #ff84b0 0%, #e91e8c 100%) !important;
  color: #fff !important;
  font-size: 0.95rem !important;
  font-weight: 700 !important;
  letter-spacing: 0.02em;
  box-shadow: 0 8px 24px rgba(255, 132, 176, 0.45) !important;
  transition: filter 0.2s, transform 0.15s;
  margin-top: 4px;
}

.lp-btn:hover {
  filter: brightness(1.08);
  transform: translateY(-1px);
}

.lp-btn:active {
  transform: translateY(0);
}

.lp-footer {
  font-size: 0.73rem;
  color: #9ca3af;
  text-align: center;
  margin-top: 28px;
  letter-spacing: 0.01em;
}

/* ══════════ RIGHT PANEL ══════════ */
.lp-right {
  flex: 1;
  background: linear-gradient(145deg, #7b1548 0%, #c2185b 50%, #ff84b0 100%);
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 48px;
}

/* Blobs */
.blob {
  position: absolute;
  border-radius: 50%;
  filter: blur(70px);
  opacity: 0.3;
}

.blob-1 {
  width: 400px;
  height: 400px;
  background: #ff84b0;
  top: -100px;
  right: -80px;
}

.blob-2 {
  width: 320px;
  height: 320px;
  background: #f06292;
  bottom: -80px;
  left: -60px;
}

.blob-3 {
  width: 220px;
  height: 220px;
  background: #fce4ec;
  top: 38%;
  left: 28%;
}

/* Right header */
.lp-right-header {
  position: relative;
  z-index: 2;
  text-align: center;
  margin-bottom: 32px;
}

.lp-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(255,255,255,0.15);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255,255,255,0.2);
  border-radius: 99px;
  padding: 6px 14px;
  font-size: 0.75rem;
  font-weight: 600;
  color: #fff;
  margin-bottom: 18px;
}

.lp-right-title {
  font-size: 1.8rem;
  color: rgba(255,255,255,0.9);
  font-weight: 400;
  line-height: 1.4;
  margin: 0;
}

.lp-right-title strong {
  font-weight: 800;
  color: #fff;
}

/* SVG illustration */
.lp-svg-wrap {
  position: relative;
  z-index: 2;
  width: 100%;
  max-width: 560px;
}

.lp-illustration {
  width: 100%;
  height: auto;
  filter: drop-shadow(0 24px 60px rgba(0,0,0,0.3));
}

/* Floating card */
.lp-stat-card {
  position: absolute;
  bottom: 36px;
  left: 48px;
  background: rgba(255,255,255,0.95);
  backdrop-filter: blur(16px);
  border-radius: 16px;
  padding: 14px 20px;
  display: flex;
  align-items: center;
  gap: 14px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.2);
  z-index: 5;
}

.lp-stat-icon {
  width: 42px;
  height: 42px;
  background: #ffe4ef;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.lp-stat-title {
  font-size: 0.85rem;
  font-weight: 700;
  color: #2d0a1a;
  margin: 0;
}

.lp-stat-sub {
  font-size: 0.71rem;
  color: #6b7280;
  margin: 3px 0 0;
}

/* ── Responsive ── */
@media (max-width: 768px) {
  .login-root {
    flex-direction: column;
  }

  .lp-left {
    flex: unset;
    width: 100%;
    min-height: 100vh;
    box-shadow: none;
    padding: 40px 24px;
  }

  .lp-right {
    display: none;
  }
}
</style>
