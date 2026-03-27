<template>
    <q-card :style="{ width: '100%', maxWidth: $q.screen.gt.sm ? '40vw' : '100vw' }" class="shadow-1">
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
          dense outlined v-model="form.proveedor.nombre" :loading="form.validating"
          label="Nombre" @change="form.validate(propPath + '.nombre')" :error="form.invalid(propPath + '.nombre')" :class="form.invalid(propPath + '.nombre') ? 'q-mb-sm' : ''">
          <template v-slot:prepend>
            <q-icon name="mdi-file" />
          </template>
          <template v-slot:error>
            <div>{{ form.errors[propPath + '.nombre'] }}</div> 
          </template>
        </q-input>
        <!-- <q-input dense outlined v-model="form.catalogo.description" label="Descripción" @change="form.validate(propPath + '.description')" :error="form.invalid(propPath + '.description')" :class="form.invalid(propPath + '.description') ? 'q-mb-sm' : ''">
          <template v-slot:prepend>
            <q-icon name="description" />
          </template>
          <template v-slot:error>
            <div>{{ form.errors[propPath + '.description'] }}</div>
          </template>
        </q-input> -->
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
import formProveedores from './FormProveedor';
const emits = defineEmits(["save"]);
const props = defineProps({
    propPath: String,
    title: String,
    id: {
        type: Number,
    },
});

const form = ref(null)
if (props.id) {
  form.value = useForm('put', 'api/proveedores/' + props.id, formProveedores)
} else {
  form.value = useForm('post', 'api/proveedores', formProveedores)
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
    
});

defineExpose({
  form,
});

</script>

<style lang="scss" scoped>

</style>