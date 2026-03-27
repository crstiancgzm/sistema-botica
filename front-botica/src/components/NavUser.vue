<template>
  <q-btn round flat class="redondeo q-ml-md">
   <q-avatar :size="$q.screen.gt.sm ? '40px' : '26px'">
    <template v-if="!photo">
        <img src="https://cdn.quasar.dev/img/avatar3.jpg">
    </template>
    <template v-else> <q-img :src="photo" /> </template>
    </q-avatar>
    <q-menu class="shadow-1"
      transition-show="jump-down"
      transition-hide="jump-up"
      style="min-width: 250px;"
    >

        <div class="row" style="width: 100%;">
         <div class="col-12">
         {{ name.value }}
          <q-list>
            <q-item class="q-ma-xs" >
            <q-item-section avatar>
              <q-avatar :color="$q.dark.isActive ? 'white' : 'white'" :text-color="$q.dark.isActive ? 'dark' : 'dark'" >
                  <template v-if="!photo"> {{ initialsMayus }} </template>
                  <template v-else> <q-img :src="photo" /> </template>
              </q-avatar>
            </q-item-section>

            <q-item-section side> {{ name }}
              <q-item-label caption>Administrador</q-item-label>
             </q-item-section>
          </q-item>
          <q-separator inset />
            <q-item :to="{ name: 'Roles' }" :active="link === 'Roles'" @click="link = 'Roles'" clickable v-ripple class="q-ma-xs">
            <q-item-section avatar>
              <q-icon name="mdi-account" size="25px" />
            </q-item-section>
            <q-item-section side> Perfil </q-item-section>
          </q-item>
          <!-- <q-separator inset />
           <q-item :to="{ name: 'Roles' }" :active="link === 'Roles'" @click="link = 'Roles'" clickable v-ripple class="q-ma-xs">
            <q-item-section avatar>
              <q-icon name="mdi-account" size="25px" />
            </q-item-section>
            <q-item-section side> Configuraciones </q-item-section>
          </q-item> -->
          </q-list>
         </div>
        </div>

      <q-separator inset />
      <q-list dense style="min-width: 250px;">
        <q-item clickable v-close-popup @click="logout()" >
          <q-item-section class="q-ma-sm text-red text-weight-medium text-center text-uppercase">Cerrar Sesión </q-item-section>
        </q-item>
      </q-list>
    </q-menu>
  </q-btn>
  <!-- <UsuariosForm :perfil="true" ref="usuariosformRef" @save="save()"></UsuariosForm> -->
</template>

<script setup>
import { useUserStore } from "src/stores/user-store";
import { onMounted } from "vue";
import { ref } from "vue";
import { computed } from "vue";
import { useRouter } from "vue-router";
// import UsuarioService from "src/services/UsuarioService";

// import UsuariosForm from "pages/Admin/usuarios/UsuariosForm.vue";
import { useQuasar } from "quasar";
const $q = useQuasar();
const usuariosformRef = ref();
const nombre = ref(null);
const userStore = useUserStore();
const router = useRouter();
const name = ref("");
const area = ref("");
const photo = ref("");
const initialsMayus = computed(() => {
  // Divide la frase por espacios en palabras
  const words = name.value.split(" ");

  // Obtiene la primera letra de cada palabra y la convierte a mayúscula
  const initials = words.map((word) => word.charAt(0).toUpperCase());

  // Une las iniciales para formar la frase final

  return initials.join("");
  // console.log(userStore.getName);
  // return name.value;
});
onMounted(async () => {
  await userStore.getUser();
  name.value = userStore.getName;
  area.value = userStore.getArea;
  photo.value = userStore.getPhoto;
});
const logout = async () => {
  userStore.logout();
  router.push({ name: "Login" });
};

// async function editar(id) {
//   usuariosformRef.value.show = true;
//   const row = await UsuarioService.get(id);
//   usuariosformRef.value.setValue(row);
// }
// async function save() {
//   usuariosformRef.value.loading = true;

//   try {
//     nombre.value = await UsuarioService.save(usuariosformRef.value.form);
//     usuariosformRef.value.loading = false;
//     usuariosformRef.value.show = false;
//     // tableRef.value.requestServerInteraction();
//     $q.notify({
//       type: "positive",
//       message: "Guardado con Exito.",
//       position: "top-right",
//       progress: true,
//       timeout: 1000,
//     });
//     name.value = nombre.value.name;
//   } catch (e) {
//     console.log(e.response.data.errors);
//     usuariosformRef.value.errors = e.response.data.errors;
//     usuariosformRef.value.loading = false;
//   }
// }
</script>

<style lang="css" scoped>
  .redondeo { 
   border-radius: 20px !important;
  }
</style>
