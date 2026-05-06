<template>

  <q-card :style="{ width: '100%', maxWidth: $q.screen.gt.sm ? '45vw' : '100vw' }" class="shadow-1">
    <q-card-section class="bg-primary">
      <div class="row text-white">
        <div style="font-size: large;">{{ title }}</div>
        <q-space />
        <q-btn v-close-popup round size="sm" unelevated>
          <q-icon name="close" />
        </q-btn>
      </div>

    </q-card-section>
    <div class="q-ma-md">
      <q-tabs v-model="mostrarFotografias" inline-label dense align="justify" :vertical="$q.screen.lt.sm ? true : false"
        class="bg-transparent" :class="$q.dark.isActive ? 'text-white' : 'text-primary'"
        style="font-weight: bolder; border-radius: 10px;" indicator-color="transparent" :breakpoint="0"
        :active-color="true ? 'white' : 'primary'" active-bg-color="primary">
        <q-tab name="producto" label="Producto" />
        <q-tab name="categoria" label="Categoria" />
        <!-- <q-tab name="subcategoria" label="Categoria Malestar" /> -->
        <q-tab name="fotografias" label="Fotografías" />
      </q-tabs>
    </div>
    <q-form @submit.prevent="submit">
      <!-- <q-card-section class="q-pb-xs"> -->
      <q-tab-panels v-model="mostrarFotografias" animated>
        <q-tab-panel name="producto">
          <div class="row q-col-gutter-sm">
            <div class="col-12 col-md-6">
              <q-input autogrow dense outlined v-model="form.inventario.nombre" :loading="form.validating"
                label="Nombre del producto" @change="form.validate(propPath + '.nombre')"
                :error="form.invalid(propPath + '.nombre')"
                :class="form.invalid(propPath + '.nombre') ? 'q-mb-sm' : ''">
                <template v-slot:prepend>
                  <q-icon name="mdi-file" />
                </template>
                <template v-slot:error>
                  <div>{{ form.errors[propPath + '.nombre'] }}</div>
                </template>
              </q-input>
            </div>
            <div class="col-12 col-md-6">
              <q-input dense outlined v-model="form.inventario.nombre_principio_activo" label="Nombre del principio activo"
                @change="form.validate(propPath + '.nombre_principio_activo')" :error="form.invalid(propPath + '.nombre_principio_activo')"
                :class="form.invalid(propPath + '.nombre_principio_activo') ? 'q-mb-sm' : ''">
                <template v-slot:prepend>
                  <q-icon name="description" />
                </template>
                <template v-slot:error>
                  <div>{{ form.errors[propPath + '.nombre_principio_activo'] }}</div>
                </template>
              </q-input>
            </div>
            <div class="col-12 col-md-4">
              <q-input dense outlined v-model="form.inventario.codigo" label="Código"
                @change="form.validate(propPath + '.codigo')" :error="form.invalid(propPath + '.codigo')"
                :class="form.invalid(propPath + '.codigo') ? 'q-mb-sm' : ''">
                <template v-slot:prepend>
                  <q-icon name="description" />
                </template>
                <template v-slot:error>
                  <div>{{ form.errors[propPath + '.codigo'] }}</div>
                </template>
              </q-input>
            </div>
            <div class="col-12 col-md-4">
              <q-input dense outlined type="number" v-model="form.inventario.cantidad" label="Cantidad"
                @change="form.validate(propPath + '.cantidad')" :error="form.invalid(propPath + '.cantidad')"
                :class="form.invalid(propPath + '.cantidad') ? 'q-mb-sm' : ''">
                <template v-slot:prepend>
                  <q-icon name="description" />
                </template>
                <template v-slot:error>
                  <div>{{ form.errors[propPath + '.cantidad'] }}</div>
                </template>
              </q-input>
            </div>
            <!-- <div class="col-12 col-md-5">
              <q-select dense outlined v-model="form.inventario.categoria_id" :options="form.categorias"
              option-label="nombre" option-value="id" label="Seleccionar categoria" clearable
              @change="form.validate(propPath + '.categoria_id')" :error="form.invalid(propPath + '.categoria_id')"
              :class="form.invalid(propPath + '.categoria_id') ? 'q-mb-sm' : ''">
              <template v-slot:prepend>
                  <q-icon name="mdi-format-list-bulleted-square" />
                </template>
                <template v-slot:error>
                  <div>{{ form.errors[propPath + '.categoria_id'] }}</div>
                </template>
              </q-select>
            </div> -->
            <div class="col-12 col-md-4">
              <selectGeneralAsyncrono v-model="form.inventario.presentacion"
                v-model:id="form.inventario.presentacion_id" :add="false" label="Presentación" option-label="nombre"
                option-value="id" prepend-icon="mdi-format-align-justify" :serviceApi="PresentacionService"
                :error="form.invalid(propPath + '.presentacion_id')"
                :errorMessages="form.errors[propPath + '.presentacion_id']" />
            </div>
            <div class="col-12 col-md-4">
              <q-select map-options emit-value dense outlined v-model="form.inventario.flag_blister"
                :options="[{ label: 'SI', value: 1 }, { label: 'NO', value: 0 }]" label="¿Precio del blister?">
                <template v-slot:prepend>
                  <q-icon name="mdi-format-list-bulleted-square" />
                </template>
              </q-select>
            </div>
            <div class="col-12 col-md-4" v-if="form.inventario.flag_blister">
              <q-input dense outlined v-model="form.inventario.precio_blister" mask="##.##" prefix="S/. "
                reverse-fill-mask fill-mask="0" label="Precio del Blister"
                @change="form.validate(propPath + '.precio_blister')"
                :error="form.invalid(propPath + '.precio_blister')"
                :class="form.invalid(propPath + '.precio_blister') ? 'q-mb-sm' : ''">
                <template v-slot:prepend>
                  <q-icon name="description" />
                </template>
                <template v-slot:error>
                  <div>{{ form.errors[propPath + '.precio_blister'] }}</div>
                </template>
              </q-input>
            </div>
            <div class="col-12 col-md-4" v-if="form.inventario.flag_blister">
              <q-input dense outlined type="number" v-model="form.inventario.cantidad_blister" label="Cantidad Blister"
                @change="form.validate(propPath + '.cantidad_blister')"
                :error="form.invalid(propPath + '.cantidad_blister')"
                :class="form.invalid(propPath + '.cantidad_blister') ? 'q-mb-sm' : ''">
                <template v-slot:prepend>
                  <q-icon name="description" />
                </template>
                <template v-slot:error>
                  <div>{{ form.errors[propPath + '.cantidad_blister'] }}</div>
                </template>
              </q-input>
            </div>
            <div class="col-12 col-md-4">
              <q-select map-options emit-value dense outlined v-model="form.inventario.flag_unidad"
                :options="[{ label: 'SI', value: 1 }, { label: 'NO', value: 0 }]" label="¿Precio de unidad?">
                <template v-slot:prepend>
                  <q-icon name="mdi-format-list-bulleted-square" />
                </template>
              </q-select>
            </div>
            <div class="col-12 col-md-4" v-if="form.inventario.flag_unidad">
              <q-input dense outlined v-model="form.inventario.precio_unidad" mask="##.##" prefix="S/. "
                reverse-fill-mask fill-mask="0" label="Precio de unidad"
                @change="form.validate(propPath + '.precio_unidad')" :error="form.invalid(propPath + '.precio_unidad')"
                :class="form.invalid(propPath + '.precio_unidad') ? 'q-mb-sm' : ''">
                <template v-slot:prepend>
                  <q-icon name="description" />
                </template>
                <template v-slot:error>
                  <div>{{ form.errors[propPath + '.precio_unidad'] }}</div>
                </template>
              </q-input>
            </div>
            <div class="col-12 col-md-4">
              <q-input dense outlined v-model="form.inventario.precio_oficial" mask="##.##" prefix="S/. "
                reverse-fill-mask fill-mask="0" label="Precio real"
                @change="form.validate(propPath + '.precio_oficial')"
                :error="form.invalid(propPath + '.precio_oficial')"
                :class="form.invalid(propPath + '.precio_oficial') ? 'q-mb-sm' : ''">
                <template v-slot:prepend>
                  <q-icon name="description" />
                </template>
                <template v-slot:error>
                  <div>{{ form.errors[propPath + '.precio_oficial'] }}</div>
                </template>
              </q-input>
            </div>
            <div class="col-12 col-md-4">
              <selectGeneralAsyncrono v-model="form.inventario.area" v-model:id="form.inventario.area_id" :add="false"
                label="Area" option-label="nombre" option-value="id" prepend-icon="mdi-format-align-justify"
                :serviceApi="AreaService" :error="form.invalid(propPath + '.area_id')"
                :errorMessages="form.errors[propPath + '.area_id']" />
            </div>
            <!-- <div class="col-12 text-subtitle1 text-weight-bold">Registro sanitario</div> -->
            <div class="col-12 col-md-4">
              <q-input type="date" dense outlined v-model="form.inventario.fecha_vencimiento"
                label="Fecha de vencimiento" @change="form.validate(propPath + '.fecha_vencimiento')"
                :error="form.invalid(propPath + '.fecha_vencimiento')"
                :class="form.invalid(propPath + '.fecha_vencimiento') ? 'q-mb-sm' : ''">
                <template v-slot:prepend>
                  <q-icon name="event" />
                </template>
                <template v-slot:error>
                  <div>{{ form.errors[propPath + '.fecha_vencimiento'] }}</div>
                </template>
              </q-input>
            </div>
            <div class="col-12 col-md-6">
              <q-input dense outlined v-model="form.inventario.registro_sanitario" label="Registro sanitario"
                @change="form.validate(propPath + '.registro_sanitario')"
                :error="form.invalid(propPath + '.registro_sanitario')"
                :class="form.invalid(propPath + '.registro_sanitario') ? 'q-mb-sm' : ''">
                <template v-slot:prepend>
                  <q-icon name="description" />
                </template>
                <template v-slot:error>
                  <div>{{ form.errors[propPath + '.registro_sanitario'] }}</div>
                </template>
              </q-input>
            </div>
            <div class="col-12 col-md-6">
              <q-input dense outlined v-model="form.inventario.lote" label="Lote"
                @change="form.validate(propPath + '.lote')" :error="form.invalid(propPath + '.lote')"
                :class="form.invalid(propPath + '.lote') ? 'q-mb-sm' : ''">
                <template v-slot:prepend>
                  <q-icon name="description" />
                </template>
                <template v-slot:error>
                  <div>{{ form.errors[propPath + '.lote'] }}</div>
                </template>
              </q-input>
            </div>
            <div class="col-12 col-md-6">
              <selectGeneralAsyncrono v-model:id="form.inventario.laboratorio_id" v-model="form.inventario.laboratorio" :add="false" label="Laboratorio"
                option-label="nombre" option-value="id" prepend-icon="mdi-format-align-justify"
                :serviceApi="LaboratorioService" :error="form.invalid(propPath + '.laboratorio_id')"
                :errorMessages="form.errors[propPath + '.laboratorio_id']" />
            </div>
            <div class="col-12 col-md-6">
              <q-input dense outlined v-model="form.inventario.ubicacion" label="Ubicación"
                @change="form.validate(propPath + '.ubicacion')" :error="form.invalid(propPath + '.ubicacion')"
                :class="form.invalid(propPath + '.ubicacion') ? 'q-mb-sm' : ''">
                <template v-slot:prepend>
                  <q-icon name="description" />
                </template>
                <template v-slot:error>
                  <div>{{ form.errors[propPath + '.ubicacion'] }}</div>
                </template>
              </q-input>
            </div>
            <div class="col-12 col-md-6">
              <selectGeneralAsyncrono v-model:id="form.inventario.proveedor_id" v-model="form.inventario.proveedor" :add="false" label="Proveedor"
                option-label="razon_social" :filterFields="['razon_social']" option-value="id"
                prepend-icon="mdi-format-align-justify" :serviceApi="ProveedorService"
                :error="form.invalid(propPath + '.proveedor_id')"
                :errorMessages="form.errors[propPath + '.proveedor_id']" />
            </div>
          </div>
        </q-tab-panel>
        <q-tab-panel name="categoria">
          <div class="row q-col-gutter-xs">
            <div class="col-12 col-md-6">
              <div class="col-12 text-subtitle1 text-weight-bold q-ml-xs text-primary">Categoria Malestar</div>
              <q-card class="q-pa-xs" bordered flat>
                <SelectConChips v-model="form.inventario.subcategorias" :serviceApi="CategoriaMalestarService"
                  label="Asignar Malestares" chip-icon="mdi-emoticon-sad" />
              </q-card>
            </div>
            <div class="col-12 col-md-6">
              <div class="col-12 text-subtitle1 text-weight-bold q-ml-xs text-primary">Categoria</div>
              <q-card class="q-pa-xs" bordered flat>
                <SelectConChips v-model="form.inventario.categorias" :serviceApi="CategoriaService"
                  label="Asignar Categorias" chip-icon="mdi-check-decagram" />
              </q-card>
            </div>
          </div>

        </q-tab-panel>
        <q-tab-panel name="fotografias">
          <div class="col-12">
            <fotografiasComponent v-model="form.inventario"></fotografiasComponent>
          </div>
        </q-tab-panel>
      </q-tab-panels>
      <!-- </q-card-section> -->
      <q-separator />

      <q-card-actions align="right">
        <q-btn label="Cancelar" flat v-close-popup></q-btn>
        <q-btn outline label="Guardar" :loading="form.processing" type="submit" color="primary"></q-btn>
      </q-card-actions>
    </q-form>
  </q-card>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import { useForm } from 'laravel-precognition-vue'
import formInventarios from './FormInventario';
import { useUserStore } from "src/stores/user-store";
import fotografiasComponent from 'src/components/fotografiasComponent.vue';
import SelectConChips from 'src/components/SelectConChips.vue';
import PresentacionService from 'src/services/PresentacionService';
import LaboratorioService from 'src/services/LaboratorioService';
import ProveedorService from 'src/services/ProveedorService';
import selectGeneralAsyncrono from 'src/components/selectGeneralAsyncrono.vue';
import CategoriaService from 'src/services/CategoriaService';
import CategoriaMalestarService from 'src/services/CategoriaMalestarService';
import AreaService from 'src/services/AreaService';
const userStore = useUserStore();
const emits = defineEmits(["save"]);
const props = defineProps({
  propPath: String,
  title: String,
  id: {
    type: Number,
  },
});
let read = ref();

read.value = userStore.getAreaId ? true : false;

const areaStore = ref();
areaStore.value = userStore.getAreaId;

const mostrarFotografias = ref('producto');

const form = ref(null)
if (props.id) {
  form.value = useForm('post', 'api/inventarios/' + props.id + "?_method=PUT", formInventarios)
} else {
  form.value = useForm('post', 'api/inventarios', formInventarios)
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

// const columns = [
//   {
//     name: 'nombre',
//     label: 'Malestar',
//     field: 'nombre',
//     align: 'left'
//   },
//   {
//     name: 'acciones',
//     label: 'Acciones',
//     align: 'center'
//   }
// ]

// const listaMalestares = ref([])
// const malestarSeleccionado = ref(null)
// const errorMensaje = ref('')

// async function selectedRequest(params) {
//   malestarSeleccionado.value = params
// }


// const listaOrdenada = computed(() =>
//   [...form.value.inventario.malestares].sort((a, b) => a.nombre.localeCompare(b.nombre))
// )

// // ── Acciones ──────────────────────────────────────────────
// function agregarAlArray() {
//   errorMensaje.value = ''

//   if (!malestarSeleccionado.value) {
//     errorMensaje.value = 'Debe seleccionar un malestar'
//     return
//   }

//   const yaExiste = form.value.inventario.malestares.some(
//     ({ id }) => id === malestarSeleccionado.value.id
//   )

//   if (yaExiste) {
//     errorMensaje.value = `Este malestar "${malestarSeleccionado.value.nombre}" ya fue agregado`
//     return
//   }

//   form.value.inventario.malestares.push({ ...malestarSeleccionado.value })
//   malestarSeleccionado.value = null
// }

// function eliminar(id) {
//   form.value.inventario.malestares = form.value.inventario.malestares.filter(
//     item => item.id !== id
//   )
//   errorMensaje.value = ''
// }

onMounted(() => {
  console.log(props);

});

defineExpose({
  form,
});

</script>

<style lang="css" scoped></style>