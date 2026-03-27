<template>
  <div>
    <selectGeneralAsyncrono
      v-model="seleccionado"
      :add="false"
      :label="label"
      :option-label="optionLabel"
      :filter-fields="filterFields"
      option-value="id"
      :prepend-icon="prependIcon"
      :serviceApi="serviceApi"
      :error="!!errorMensaje"
      :errorMessages="errorMensaje"
      @option-selected="seleccionado = $event"
    />

    <div class="text-right">
      <q-btn 
        color="primary"
        icon-right="add"
        :disable="!seleccionado"
        @click="agregar"
      >
        AGREGAR
      </q-btn>
    </div>

    <transition-group name="chip-list" tag="div" class="q-gutter-xs q-mt-xs">
      <q-chip
        v-for="item in listaOrdenada"
        :key="item.id"
        removable
        color="primary"
        text-color="white"
        :icon="chipIcon"
        class="q-pa-md"
        outline
        @remove="eliminar(item.id)"
      >
        {{ item[optionLabel] }}
      </q-chip>
    </transition-group>

    <div v-if="errorMensaje" class="text-red-13 q-mt-sm text-caption">
      {{ errorMensaje }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import selectGeneralAsyncrono from 'src/components/selectGeneralAsyncrono.vue'

// ── Props ─────────────────────────────────────────────────
const props = defineProps({
  modelValue: {           // array donde se guardan los items
    type: Array,
    default: () => []
  },
  serviceApi: {           // servicio que consume el select
    type: Function,
    required: true
  },
  label: {                // label del select
    type: String,
    default: 'Seleccionar'
  },
  optionLabel: {          // campo a mostrar (nombre, razon_social, etc.)
    type: String,
    default: 'nombre'
  },
  filterFields: {         // campos por los que filtra el select
    type: Array,
    default: () => ['nombre']
  },
  prependIcon: {          // icono del select
    type: String,
    default: 'mdi-filter-variant'
  },
  chipIcon: {             // icono del chip
    type: String,
    default: 'mdi-tag'
  },
})

const emits = defineEmits(['update:modelValue'])

// ── Estado local ──────────────────────────────────────────
const seleccionado = ref(null)
const errorMensaje = ref('')

// ── Computed ──────────────────────────────────────────────
const listaOrdenada = computed(() =>
  [...props.modelValue].sort((a, b) =>
    String(a[props.optionLabel]).localeCompare(String(b[props.optionLabel]))
  )
)

// ── Acciones ──────────────────────────────────────────────
function agregar() {
  errorMensaje.value = ''

  if (!seleccionado.value) {
    errorMensaje.value = 'Debe seleccionar un elemento'
    return
  }

  const yaExiste = props.modelValue.some(({ id }) => id === seleccionado.value.id)

  if (yaExiste) {
    errorMensaje.value = `"${seleccionado.value[props.optionLabel]}" ya fue agregado`
    return
  }

  emits('update:modelValue', [...props.modelValue, { ...seleccionado.value }])
  seleccionado.value = null
}

function eliminar(id) {
  emits('update:modelValue', props.modelValue.filter(item => item.id !== id))
  errorMensaje.value = ''
}
</script>

<style scoped>
.chip-list-enter-active,
.chip-list-leave-active {
  transition: all 0.25s ease;
}
.chip-list-enter-from,
.chip-list-leave-to {
  opacity: 0;
  transform: scale(0.85);
}
</style>