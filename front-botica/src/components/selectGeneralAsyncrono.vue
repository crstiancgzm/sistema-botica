<template>
  <p v-if="debug">{{ model }}</p>
  <q-select
    :readonly="readonly"
    :disable="disable"
    ref="RefSelect"
    outlined
    dense
    color="primary"
    options-selected-class="text-deep-orange"
    :label="label"
    clearable
    use-input
    hide-selected
    fill-input
    input-debounce="500"
    :option-value="optionValue"
    :option-label="optionLabel"
    v-model="model"
    :loading="isLoading"
    :options="options"
    @filter="filterFn"
    @virtual-scroll="onScroll"
    @update:model-value="(e) => onUpdate(e)"
    @input-value="onInputValue"
    @new-value="addNewOption"
    @clear="(v) => onClear(v)"
    new-value-mode="add-unique"
    :error="error"
    :class="error ? '' : ''"
  >
    <!-- :new-value-mode="add ? 'add-unique' : null" -->
    <template v-slot:error>
      <div>{{ errorMessages }}</div>
    </template>
    <template v-slot:prepend>
      <q-icon :name="prependIcon" />
    </template>
    <template v-slot:no-option="scope">
      <q-item v-if="add" clickable @click="customAdd(scope.inputValue, scope.doneFn)" focused>
        <q-item-section>
          {{ valorInput }}
        </q-item-section>
        <q-item-section class="text-green" side>
          Presiona Enter o Click para agregar
        </q-item-section>
      </q-item>
      <q-item v-else>
        <q-item-section class="" side> No se a encontrado ningun resultado. </q-item-section>
      </q-item>
    </template>
    <template v-slot:after v-if="showAddButton">
      <q-btn 
        :label="$q.screen.lt.sm ? '' : addButtonLabel"
        :color="addButtonColor"
        :icon-right="addButtonIcon"
        @click="customAdd(valorInput, () => {})"
      />
    </template>
  </q-select>
</template>

<script setup>
import { ref, nextTick, onMounted } from 'vue'
// import AreaService from "src/services/AreaService";
import { useNotify } from 'src/composables/useNotify'

const { notifySuccess, notifyError } = useNotify()

// Props
const props = defineProps({
  label: {
    type: String,
    default: 'Seleccionar',
  },
  optionValue: {
    type: String,
    default: 'id',
  },
  optionLabel: {
    type: [String, Function],
    default: 'nombre',
  },
  filterFields: {
    type: Array,
    default: () => ['nombre'], // por defecto sigue siendo nombre
  },
  prependIcon: {
    type: String,
    default: 'mdi-list-box',
  },
  initialOptions: {
    type: Array,
    default: () => [],
  },
  serviceApi: {
    type: Function,
    required: true, // La función que consulta los datos (como AreaService.getData)
  },
  paramsApi: {
    type: Object,
    required: false, // La función que consulta los datos (como AreaService.getData)
  },
  mounted: {
    type: Boolean,
    default: true,
  },
  debug: {
    type: Boolean,
    default: false,
  },
  disable: {
    type: Boolean,
    default: false,
  },
  readonly: {
    type: Boolean,
    default: false,
  },
  error: {
    type: Boolean,
    default: false,
  },
  errorMessages: {
    type: String,
  },
  add: {
    type: Boolean,
    default: true,
  },
  custonGet: {
    type: String,
  },
  showAddButton: {
    type: Boolean,
    default: false,
  },
  addButtonLabel: {
    type: String,
    default: 'Agregar',
  },
  addButtonIcon: {
    type: String,
    default: 'add',
  },
  addButtonColor: {
    type: String,
    default: 'primary',
  },
})

// Refs
const RefSelect = ref(null)
const model = defineModel()
const id = defineModel('id')

const options = ref(props.initialOptions)
const response = ref(null)
const isLoading = ref(false)
const page = ref(1)
const totalPage = ref(0)
const needle = ref(null)
const valorInput = ref('')

// Emitted events
const emit = defineEmits(['add-new-value', 'clear', 'custom-add', 'option-selected'])

onMounted(async () => {
  // console.log(props.optionValue)
  // console.log('model value:', model.value)
  console.log('idddd:', id.value)
  if (!model.value && id.value) {
    // console.log('consultado a api')
    try {
      // model.value = await props.serviceApi.get(id.value)
      const res = await props.serviceApi.get(id.value)
      // console.log('resultado', res)
      // model.value = res
      if (props.custonGet) {
        // console.log('custoget', res[props.custonGet])
        model.value = res[props.custonGet]
        id.value = res[props.custonGet].id
      } else {
        // console.log('no--custoget', res)
        model.value = res
        id.value = res.id
      }
      // console.log(model.value)
    } catch (error) {}
  }
  // console.log('model-afeter:', model.value)

  // console.log('idddd-afeter:', id.value)
})

// Internal Methods
const setLoading = (value) => {
  isLoading.value = value
}
function onUpdate(e) {
  // emit('update:model-value', e)

  id.value = e?.[props.optionValue]
  console.log('upadete:', e?.[props.optionValue])
  console.log('upadete:', e)

  emit('option-selected', e)

  // console.log('onUptada:', e?.[props.optionValue])
}
const refreshData = async () => {
  const params = {
    sort_by: 'id',
    direction: 'desc',
    ...props.paramsApi, // Si paramsApi tiene datos, sobrescribe los valores por defecto
  }
  try {
    response.value = await props.serviceApi.getData({
      params: {
        ...params,
        search: needle.value,
        page: page.value,
      },
    })
  } catch (error) {
    notifyError('Error al obtener datos')
    console.error(error)
  }
}

const filterFn = async (val, update, abort) => {

  console.log("aasddd", val);
  

  try {
    setLoading(true)
    // needle.value = val.toLowerCase()
    needle.value = val
    page.value = 1
    await refreshData()
    // const filteredOptions = response.value.data.filter((v) => {

    //   console.log("vvvvv", v);
      
    //   const label = typeof props.optionLabel === 'function' 
    //     ? props.optionLabel(v) 
    //     : v[props.optionLabel]
    //   // return label?.toLowerCase().includes(needle.value)
    //   return label?.includes(needle.value)
    // })

    const filteredOptions = response.value.data.filter((v) => {
      return props.filterFields.some(field =>
        // String(v[field] ?? '').includes(needle.value)
          String(v[field] ?? '').toLowerCase().includes(needle.value.toLowerCase()) // ✅

      )
    })
    
    update(() => {
      options.value = filteredOptions
      totalPage.value = response.value.last_page
    })
  } catch (error) {
    notifyError('Error en la función de filtrado')
    console.error('Error en la función de filtrado:', error)
    abort()
  } finally {
    setLoading(false)
  }
}

const onScroll = async ({ to, index, ref }) => {
  const lastIndex = options.value.length - 1
  if (isLoading.value !== true && page.value < totalPage.value && index === lastIndex) {
    setLoading(true)
    page.value++
    await refreshData()
    options.value.push(...response.value.data)
    nextTick(() => {
      ref.refresh()
      setLoading(false)
    })
  }
}

const onInputValue = (e) => {
  valorInput.value = e
}

const addNewOption = async (inputValue, doneFn) => {
  try {
    setLoading(true)
    const res = await props.serviceApi.save({ nombre: valorInput.value }) // Ajusta la llamada al API
    doneFn(res, 'add-unique')
    emit('add-new-value', res) // Emite el nuevo valor agregado
    notifySuccess('Datos guardados correctamente!')
  } catch (error) {
    notifyError(error)
    console.error(error)
  } finally {
    setLoading(false)
  }
}

const customAdd = (e, doneFn) => {
  emit('custom-add', valorInput.value)
}

const onClear = (v) => {
  console.log('clear', v)
  emit('clear', v)
}
</script>
