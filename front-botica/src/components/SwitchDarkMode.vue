<script setup>
import { useQuasar } from "quasar";
import { ref, onMounted } from "vue";

const $q = useQuasar();

//Estados reactivos
const darkMode = ref($q.dark.isActive);

//OnMounted
onMounted(() => {
  // console.log(darkMode.value);
});

//Métodos
function changeDarkMode() {
  $q.dark.set(darkMode.value);
}
</script>

<template>
  <q-toggle
    dense
    v-model="darkMode"
    @update:model-value="changeDarkMode()"
    size="lg"
    checked-icon="mdi-moon-waxing-crescent"
    unchecked-icon="mdi-white-balance-sunny"
  >
  </q-toggle>
</template>