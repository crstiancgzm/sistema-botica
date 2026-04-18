<template>
  <q-page class="q-pa-md">

    <!-- Header -->
    <div class="row items-center q-mb-md">
      <div>
        <div class="text-h5 text-weight-bold">HISTORIAL DE CAJAS</div>
        <div class="text-caption text-grey-5">Registro histórico de apertura y cierre de cajas</div>
      </div>
      <q-space />
      <q-btn flat round dense icon="refresh" color="grey-5" :loading="loading"
        @click="onRequest({ pagination, filter: '' })" />
    </div>

    <!-- Filtros -->
    <q-card flat bordered class="q-mb-md q-pa-md">
      <div class="row q-col-gutter-sm items-end">
        <div class="col-xs-12 col-sm-6 col-md-3">
          <q-input v-model="filtros.fecha_desde" label="Desde" dense outlined type="date"
            @update:model-value="aplicarFiltros">
            <template v-slot:prepend><q-icon name="event" /></template>
          </q-input>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-3">
          <q-input v-model="filtros.fecha_hasta" label="Hasta" dense outlined type="date"
            @update:model-value="aplicarFiltros">
            <template v-slot:prepend><q-icon name="event" /></template>
          </q-input>
        </div>
        <div class="col-xs-6 col-sm-4 col-md-2">
          <q-select v-model="filtros.estado" label="Estado" dense outlined clearable
            :options="[{ label: 'Abierta', value: 'abierta' }, { label: 'Cerrada', value: 'cerrada' }]"
            emit-value map-options @update:model-value="aplicarFiltros" />
        </div>
        <div class="col-xs-6 col-sm-4 col-md-2">
          <q-select v-model="filtros.turno" label="Turno" dense outlined clearable
            :options="[{ label: 'Mañana', value: 'mañana' }, { label: 'Tarde', value: 'tarde' }]"
            emit-value map-options @update:model-value="aplicarFiltros" />
        </div>
        <div class="col-xs-12 col-sm-4 col-md-2">
          <q-btn flat outline color="primary" icon="bi-stars" label="Limpiar"
            :disable="!filtrosActivos" @click="limpiarFiltros" />
        </div>
      </div>
    </q-card>

    <!-- Tabla -->
    <q-card flat bordered class="q-mb-md q-pa-md">
        <div class="row items-center q-mb-md">
          <div class="col">
            <div class="text-subtitle1 text-weight-bold">Cajas registradas</div>
            <div class="text-caption text-grey-5">Listado de cajas con su respectivo estado, turno y totales</div>
          </div>
        </div>
    <q-table
      ref="tableRef"
      flat bordered
      :rows="rows"
      :columns="columns"
      row-key="id"
      v-model:pagination="pagination"
      :loading="loading"
      binary-state-sort
      :rows-per-page-options="[10, 15, 25, 50]"
      @request="onRequest"
    >
      <!-- Turno -->
      <template v-slot:body-cell-turno="props">
        <q-td :props="props">
          <q-chip dense size="sm"
            :color="props.row.turno === 'mañana' ? 'orange-1' : 'blue-1'"
            :text-color="props.row.turno === 'mañana' ? 'orange-9' : 'blue-9'"
            :icon="props.row.turno === 'mañana' ? 'wb_sunny' : 'nights_stay'"
            style="padding:10px">
            {{ props.row.turno }}
          </q-chip>
        </q-td>
      </template>

      <!-- Estado -->
      <template v-slot:body-cell-estado="props">
        <q-td :props="props">
          <q-badge rounded
            :color="props.row.estado === 'abierta' ? 'positive' : 'grey-5'"
            :label="props.row.estado === 'abierta' ? 'Abierta' : 'Cerrada'" />
        </q-td>
      </template>

      <!-- Total -->
      <template v-slot:body-cell-total="props">
        <q-td :props="props">
          <span class="text-weight-bold text-primary">
            S/. {{ Number(props.row.ventas_sum_total ?? 0).toFixed(2) }}
          </span>
        </q-td>
      </template>

      <!-- Acciones -->
      <template v-slot:body-cell-acciones="props">
        <q-td :props="props" class="text-right">
          <q-btn flat round dense size="sm" color="primary" icon="bi-eye"
            @click="verDetalle(props.row.id)">
            <q-tooltip>Ver detalle</q-tooltip>
          </q-btn>
        </q-td>
      </template>

      <template v-slot:no-data>
        <div class="full-width column flex-center q-pa-xl text-grey-5">
          <q-icon name="bi-journal-x" size="48px" class="q-mb-sm" />
          <div>Sin cajas registradas</div>
        </div>
      </template>
    </q-table>
    </q-card>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { date } from 'quasar'
import CajaService from 'src/services/CajaService'

const router = useRouter()

const tableRef = ref()
const loading  = ref(false)
const rows     = ref([])
const pagination = ref({
  sortBy: 'fecha', descending: true, page: 1, rowsPerPage: 15, rowsNumber: 0,
})

const filtros = ref({ fecha_desde: '', fecha_hasta: '', estado: null, turno: null })

const filtrosActivos = computed(() =>
  filtros.value.fecha_desde || filtros.value.fecha_hasta ||
  filtros.value.estado || filtros.value.turno
)

const columns = [
  { name: 'fecha',       label: 'Fecha',         field: 'fecha',       align: 'left',  sortable: true,
    format: v => date.formatDate(v, 'DD/MM/YYYY') },
  { name: 'turno',       label: 'Turno',          field: 'turno',       align: 'center', sortable: true },
  { name: 'estado',      label: 'Estado',         field: 'estado',      align: 'center', sortable: true },
  { name: 'cajero',      label: 'Cajero apertura', field: r => r.usuario_apertura?.name ?? '—', align: 'left' },
  { name: 'apertura',    label: 'Apertura',       field: 'hora_apertura', align: 'center',
    format: v => v ? date.formatDate(new Date(v), 'HH:mm') : '—' },
  { name: 'cierre',      label: 'Cierre',         field: 'hora_cierre',  align: 'center',
    format: v => v ? date.formatDate(new Date(v), 'HH:mm') : '—' },
  { name: 'monto_ini',   label: 'M. Inicial',     field: 'monto_inicial', align: 'right',
    format: v => `S/. ${Number(v || 0).toFixed(2)}` },
  { name: 'monto_fin',   label: 'M. Final',       field: 'monto_final',  align: 'right',
    format: v => v != null ? `S/. ${Number(v).toFixed(2)}` : '—' },
  { name: 'ventas',      label: 'Ventas',         field: 'ventas_count', align: 'center', sortable: false },
  { name: 'total',       label: 'Total',          field: 'ventas_sum_total', align: 'right', sortable: false },
  { name: 'acciones',    label: '',               field: 'id',           align: 'right' },
]

function aplicarFiltros() {
  pagination.value.page = 1
  tableRef.value.requestServerInteraction()
}

function limpiarFiltros() {
  filtros.value = { fecha_desde: '', fecha_hasta: '', estado: null, turno: null }
  aplicarFiltros()
}

async function onRequest(props) {
  const { page, rowsPerPage, sortBy, descending } = props.pagination
  loading.value = true

  try {
    const order_by = descending ? '-' + sortBy : sortBy
    const params   = {
      rowsPerPage, page, order_by,
      ...(filtros.value.fecha_desde && { fecha_desde: filtros.value.fecha_desde }),
      ...(filtros.value.fecha_hasta && { fecha_hasta: filtros.value.fecha_hasta }),
      ...(filtros.value.estado      && { estado:      filtros.value.estado }),
      ...(filtros.value.turno       && { turno:        filtros.value.turno }),
    }
    const { data, total = 0 } = await CajaService.getHistorial(params)
    rows.value = data
    pagination.value = { ...pagination.value, rowsNumber: total || data.length, page, rowsPerPage, sortBy, descending }
  } finally {
    loading.value = false
  }
}

function verDetalle(id) {
  router.push({ name: 'CajaDetalle', params: { id } })
}

onMounted(() => tableRef.value.requestServerInteraction())
</script>
