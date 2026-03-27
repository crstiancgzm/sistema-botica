<template>
  <!-- 
  <q-form @submit="login" class="q-gutter-md">
    <q-input outlined dense v-model="formLogin.email" label="Email" lazy-rules :rules="[val => (val && val.length > 0) || 'Por favor, ingresa tu email']" type="text" >
    <template v-slot:prepend>
        <q-icon name="person" />
    </template>
</q-input>

<q-input dense color="primary" outlined :type="isPwd ? 'password' : 'text'" v-model="formLogin.password"
  label="Contraseña" lazy-rules :rules="[(val) => (val !== null && val !== '') || 'Ingrese una contraseña valida']">
  <template v-slot:prepend>
          <q-icon name="lock" />
        </template>
  <template v-slot:append>
          <q-icon
            :name="isPwd ? 'visibility_off' : 'visibility'"
            class="cursor-pointer"
            @click="isPwd = !isPwd"
          />
        </template>
</q-input>

<div>
  <q-btn label="Ingresar" type="submit" color="red-13" text-color="red-1" class="full-width q-pa-sm" unelevated
    :loading="loading" />
</div>
</q-form> -->

  <q-card class="login-card">

    <div class="left-side">
      <span class="brand-name">Siempre Pensando en tu Bienestar - Boticas Puno</span>
      <div class="illustration">
        <img src="/images/Health 2 (2).png" alt="Illustration" style="width: 100%;" />
      </div>
    </div>

    <div class="right-side">
      <q-card class="form-card">
        <div class="welcome-title">
          <!-- Welcome Back -->
          <img src="/images/logo.jpeg" alt="Illustration" style="width: 100%; border-radius: 10px;" />
        </div>

        <!-- <button class="google-btn" @click="loginGoogle">

          <svg class="google-icon" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
            <path fill="#EA4335"
              d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z" />
            <path fill="#4285F4"
              d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z" />
            <path fill="#FBBC05"
              d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z" />
            <path fill="#34A853"
              d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.31-8.16 2.31-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z" />
            <path fill="none" d="M0 0h48v48H0z" />
          </svg>
          Iniciar sesión con Google
        </button> -->

        <div class="divider-row">
          <div class="divider-line"></div>
          <span class="divider-text">O INICIA SESIÓN CON TU CORREO</span>
          <div class="divider-line"></div>
        </div>
        
          <q-form @submit="login" class="q-gutter-md">
          <q-input v-model="formLogin.email" outlined placeholder="Correo electronico" dense >
            <template #prepend>
              <q-icon name="person_outline" />
            </template>
          </q-input>
  
          <q-input class="input-gap" v-model="formLogin.password" outlined :type="isPwd ? 'password' : 'text'"
            placeholder="Password" dense >
            <template #prepend>
              <q-icon name="lock_outline" />
            </template>
            <template #append>
              <q-icon :name="isPwd ? 'visibility_off' : 'visibility'"
                style="cursor:pointer; color:#bbb; font-size:1.1rem" @click="isPwd = !isPwd" />
            </template>
          </q-input>
  
          <div class="bottom-row">
          </div>
  
          <q-btn type="submit" class="login-btn" unelevated :loading="loading">Iniciar Sesión</q-btn>
        </q-form>
      </q-card>
    </div>

  </q-card>
</template>

<script setup>
import { useQuasar } from 'quasar'
import { onMounted, ref } from 'vue'
import { useUserStore } from 'src/stores/user-store'

const loading = ref(false);
const $q = useQuasar()
const emits = defineEmits(['logued'])
const userStore = useUserStore()
const progress = ref({ loading: false, portentage: 0 })
const alertText = ref(null)
const formLogin = ref({
  email: '',
  password: '',
})
const isPwd = ref(true)
// const token = ref(null)

onMounted(() => { })

const login = async () => {
  progress.value.loading = true

  try {
    await userStore.login(formLogin.value.email, formLogin.value.password)
    await userStore.getUser()

    // console.log(login.data.user)
    // userStore.setUser(login.data.user)
    emits('logued')
    console.log('login...')
    alertText.value = 'Redireccionando ...'
    progress.value.loading = false

    $q.notify({
      position: 'top',
      type: 'positive',
      message: 'Sesión Iniciada con Exito',
      timeout: 1000,
    })
  } catch (e) {
    if (e.response) {
      console.log(e.response)
      $q.notify({
        position: 'top',
        type: 'negative',
        message: 'Credenciales Inconrrectas',
      })
    } else {
      console.log(e)
      $q.notify({
        position: 'top',
        type: 'negative',
        message: 'No hay Servidor',
      })
    }
    progress.value.loading = false
  }

}
</script>

<style scoped>
/* ── Main card ── */
.login-card {
  width: 100%;
  max-width: 920px;
  min-height: 520px;
  display: flex;
  position: relative;
}

/* ── Left illustration side ── */
.left-side {
  flex: 1.1;
  position: relative;
  display: flex;
  align-items: flex-end;
  overflow: hidden;
}

/* Orange blob bottom-left */
.left-side::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 48%;
  border-radius: 0 80px 0 0;
  z-index: 0;
}

/* Cream blob center */
.blob-cream {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-58%, -52%);
  width: 340px;
  height: 300px;
  background: #FDF3E4;
  border-radius: 60% 40% 55% 45% / 50% 60% 40% 50%;
  z-index: 1;
}

/* Brand name */
.brand-name {
  position: absolute;
  top: 32px;
  left: 36px;
  font-family: 'Dancing Script', cursive;
  font-size: 1.5rem;
  color: #ff84b0;
  z-index: 10;
  letter-spacing: 1px;
}

/* SVG illustration wrapper */
.illustration {
  position: relative;
  z-index: 5;
  width: 100%;
  display: flex;
  align-items: flex-end;
  justify-content: center;
  padding-bottom: 0;
}

.illustration svg {
  width: 100%;
  max-width: 380px;
  height: auto;
  display: block;
}

/* ── Right login side ── */
.right-side {
  width: 380px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 36px 32px;
  position: relative;
  border-bottom-right-radius: 28px;

}

/* White top-left curve on right panel */
.right-side::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 120px;
  height: 120px;
  border-radius: 0 0 100% 0;
  z-index: 0;
}

/* ── Form card inside right side ──  */
.form-card {
  width: 100%;
  border-radius: 20px;
  padding: 36px 28px 28px;
  box-shadow: 0 8px 40px rgba(0, 0, 0, 0.12);
  position: relative;
  z-index: 2;
}
  

.welcome-title {
  font-family: 'Dancing Script', cursive;
  font-size: 2rem;
  color: #ff84b0;
  text-align: center;
  margin-bottom: 24px;
  line-height: 1.2;
}

/* Google button */
.google-btn {
  width: 100%;
  border: 1.5px solid #E0E0E0 !important;
  border-radius: 12px !important;
  background: #fff !important;
  color: #555 !important;
  font-family: 'Nunito', sans-serif !important;
  font-size: 0.9rem !important;
  font-weight: 600 !important;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  cursor: pointer;
  transition: box-shadow 0.2s;
  text-transform: none !important;
  letter-spacing: 0 !important;
  box-shadow: none !important;
}

.google-btn:hover {
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.10) !important;
}

.google-icon {
  width: 20px;
  height: 20px;
}

/* Divider */
.divider-row {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 18px 0 14px;
}

.divider-line {
  flex: 1;
  height: 1px;
  background: #E0E0E0;
}

.divider-text {
  font-size: 0.72rem;
  color: #aaa;
  font-weight: 600;
  letter-spacing: 0.08em;
  white-space: nowrap;
}



.input-gap {
  margin-top: 12px;
}

/* Checkbox + forgot row */
.bottom-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin: 14px 0 20px;
}

.keep-label {
  font-size: 0.8rem;
  color: #888;
  font-family: 'Nunito', sans-serif;
}


/* Login button */
.login-btn {
  width: 100%;
  height: 50px;
  border-radius: 30px !important;
  background: #ff84b0 !important;
  color: #fff !important;
  font-family: 'Nunito', sans-serif !important;
  font-size: 1rem !important;
  font-weight: 700 !important;
  letter-spacing: 0.02em !important;
  box-shadow: 0 6px 20px rgba(255, 72, 179, 0.6) !important;
  text-transform: none !important;
  transition: transform 0.15s, box-shadow 0.15s;
}

.login-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 28px rgba(243, 19, 154, 0.5) !important;
}

.login-btn:active {
  transform: translateY(0);
}

/* Help row */
.help-row {
  text-align: center;
  margin-top: 18px;
}

.help-label {
  font-size: 0.8rem;
  color: #ff84b0;
  font-weight: 700;
  font-family: 'Nunito', sans-serif;
}

.help-sub {
  font-size: 0.8rem;
  color: #888;
  font-family: 'Nunito', sans-serif;
}

.register-link {
  font-size: 0.8rem;
  color: #333;
  font-weight: 700;
  text-decoration: underline;
  cursor: pointer;
  font-family: 'Nunito', sans-serif;
}

/* ── Responsive ── */
@media (max-width: 680px) {
  .login-card {
    flex-direction: column;
    max-width: 420px;
  }

  .left-side {
    display: none;
  }

  .right-side {
    width: 100%;
    padding: 32px 20px;
  }

  .right-side::before {
    display: none;
  }
}
</style>