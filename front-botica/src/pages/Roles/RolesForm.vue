<template>
    <q-card style=" width: 60%; max-width: 70vw" class="shadow-1">
    <q-card-section class="bg-primary">
        <div class="row text-white">
        <!-- <div class="row"> -->
            <div class="text-h6">{{ title }}</div>
                <q-space />
            <q-btn v-close-popup round size="sm" unelevated>
                <q-icon name="close" />
            </q-btn>
        </div>
    </q-card-section>
    <!-- {{ form }} -->
    <q-form @submit.prevent="submit">
      <q-card-section class="q-pb-md">
        <q-input
          dense outlined v-model="form.rol.name" :loading="form.validating"
          label="Nombre" @change="form.validate(propPath + '.name')" :error="form.invalid(propPath + '.name')" :class="form.invalid(propPath + '.name') ? 'q-mb-sm' : ''">
          <template v-slot:prepend>
            <q-icon name="mdi-key" />
          </template>
          <template v-slot:error>
            <div>{{ form.errors[propPath + '.name'] }}</div> 
          </template>
        </q-input>

          <q-scroll-area style="height: 420px; max-width: 100%">
          <div class="q-mb-md">
            <q-list bordered separator>
              <template v-for="(p, i) in permisos" :key="i">
                <q-item clickable v-ripple>
                  <q-item-section>
                    <q-toggle
                      checked-icon="check"
                      keep-color
                      v-model="form.rol.permisosSelected"
                      :label="`${p.name}`"
                      color="primary"
                      :val="p.id"
                      hide-details
                      unchecked-icon="clear"
                    >
                    </q-toggle
                  ></q-item-section>
                </q-item>
              </template>
            </q-list>
          </div>
        </q-scroll-area>

      </q-card-section>
      <q-separator />

      <q-card-actions align="right">
        <q-btn label="Cancelar" flat v-close-popup></q-btn>
        <q-btn outline label="Guardar" :loading="form.processing" type="submit" color="positive"></q-btn>
      </q-card-actions>
    </q-form>
    </q-card>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useForm } from 'laravel-precognition-vue'
import formRol from './FormRol';
import PermisoService from 'src/services/PermisoService';
const emits = defineEmits(["save"]);
const permisos = ref([])

const props = defineProps({
    propPath: String,
    title: String,
    id: {
        type: Number,
    },
});

const form = ref(null)
if (props.id) {
  form.value = useForm('put', 'api/roles/' + props.id, formRol)
} else {
  form.value = useForm('post', 'api/roles', formRol)
}

async function cargar() {
  const { data } = await PermisoService.getData({
    params: { rowsPerPage: 0, order_by: 'id', sistema_id: props.sistema_id },
  })
  permisos.value = data
  console.log(permisos.value)
}

const submit = () => {
  form.value
    .submit()
    .then((response) => {
      form.value.reset();
      // form.setData()
      emits("save");
    })
    .catch((error) => {
      // alert("An error occurred.");
    });
};



onMounted(()=>{
    console.log(props);
    cargar();
});

defineExpose({
  form,
});

</script>

<style lang="scss" scoped>

</style>