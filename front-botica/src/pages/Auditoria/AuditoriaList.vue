<template>
  <q-page class="q-pa-md">

    <!-- Header -->
    <div class="row items-center q-mb-md">
      <div>
        <div class="text-h5 text-weight-bold">AUDITORÍA</div>
        <div class="text-caption text-grey-5">Registro de cambios en el sistema</div>
      </div>
      <q-space />
      <q-btn flat round dense icon="refresh" color="grey-5" :loading="loading" @click="aplicarFiltros" />
    </div>

    <!-- Filtros -->
    <q-card flat class="q-mb-md q-pa-md">
      <div class="row q-col-gutter-sm items-end">
        <div class="col-xs-12 col-sm-6 col-md-2">
          <q-input v-model="filtros.fecha_desde" label="Desde" dense outlined type="date"
            @update:model-value="aplicarFiltros">
            <template v-slot:prepend><q-icon name="event" /></template>
          </q-input>
        </div>
        <div class="col-xs-12 col-sm-6 col-md-2">
          <q-input v-model="filtros.fecha_hasta" label="Hasta" dense outlined type="date"
            @update:model-value="aplicarFiltros">
            <template v-slot:prepend><q-icon name="event" /></template>
          </q-input>
        </div>
        <div class="col-xs-6 col-sm-4 col-md-2">
          <q-select v-model="filtros.modelo" label="Módulo" dense outlined clearable :options="opcionesModelo"
            emit-value map-options @update:model-value="aplicarFiltros" />
        </div>
        <div class="col-xs-6 col-sm-4 col-md-2">
          <q-select v-model="filtros.evento" label="Evento" dense outlined clearable :options="opcionesEvento"
            emit-value map-options @update:model-value="aplicarFiltros" />
        </div>
        <div class="col-xs-12 col-sm-4 col-md-2">
          <q-select v-model="filtros.usuario_id" label="Usuario" dense outlined clearable :options="usuarios"
            option-value="id" option-label="name" emit-value map-options @update:model-value="aplicarFiltros" />
        </div>
        <div class="col-xs-12 col-sm-4 col-md-2">
          <q-btn flat outline color="secondary" icon="bi-stars" label="Limpiar" :disable="!filtrosActivos"
            @click="limpiarFiltros" />
        </div>
      </div>
    </q-card>

    <!-- Tabla -->
    <q-card class="q-pa-md" flat>
      <div class="row items-center q-pa-md q-pb-sm">
        <div class="col">
          <div class="text-subtitle1 text-weight-bold text-uppercase">Registro de actividad</div>
          <div class="text-caption text-grey-5">Cambios registrados en los módulos del sistema</div>
        </div>
      </div>
      <q-table flat :rows-per-page-options="[7, 10, 15]" class="my-sticky-header-table htable q-ma-sm" ref="tableRef"
        :rows="rows" :columns="columns" row-key="id" v-model:pagination="pagination" :loading="loading" :filter="filter"
        binary-state-sort @request="onRequest">
        <!-- Evento -->
        <template v-slot:body-cell-evento="props">
          <q-td :props="props">
            <q-chip dense :color="colorEvento(props.row.evento)" :text-color="textoEvento(props.row.evento)"
              :icon="iconoEvento(props.row.evento)" style="padding: 13px; border-radius: 10px;">
              {{ props.row.evento }}
            </q-chip>
          </q-td>
        </template>

        <!-- Módulo -->
        <template v-slot:body-cell-modelo="props">
          <q-td :props="props">
            <q-chip dense style="padding: 14px; border-radius: 10px;" outline :color="colorModelo(props.row.modelo)"
              :label="props.row.modelo" />
            <span class="text-grey-9 text-caption q-ml-xs" style="font-size: medium;">#{{ props.row.modelo_id }}</span>
          </q-td>
        </template>

        <!-- Cambios -->
        <template v-slot:body-cell-cambios="props">
          <q-td :props="props">
            <q-btn v-if="tieneCambios(props.row.cambios)" flat dense size="sm" color="secondary" icon="bi-eye"
              label="Ver" @click="verCambios(props.row)" />
            <span v-else class="text-grey-5 text-caption">—</span>
          </q-td>
        </template>

        <template v-slot:no-data>
          <div class="full-width column flex-center q-pa-xl text-grey-5">
            <q-icon name="bi-journal-x" size="48px" class="q-mb-sm" />
            <div>Sin registros de auditoría</div>
          </div>
        </template>
      </q-table>
    </q-card>

    <!-- Dialog detalle de cambios -->
    <q-dialog v-model="dialogCambios">
      <q-card style="min-width: 520px; max-width: 720px; width: 100%">
        <q-card-section class="row items-center q-pb-md bg-secondary text-white">
          <div class="text-h6 text-uppercase">Detalle del cambio</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section v-if="registroActivo">
          <!-- Meta -->
          <div class="row q-col-gutter-sm q-mb-md">
            <div class="col-6">
              <div class="text-caption text-grey-5">Módulo</div>
              <div class="text-body2 text-weight-medium">
                {{ registroActivo.modelo }}
                <span class="text-grey-5">#{{ registroActivo.modelo_id }}</span>
              </div>
            </div>
            <div class="col-6">
              <div class="text-caption text-grey-5">Usuario</div>
              <div class="text-body2 text-weight-medium">{{ registroActivo.usuario }}</div>
            </div>
            <div class="col-6">
              <div class="text-caption text-grey-5">Evento</div>
              <q-chip dense size="sm" :color="colorEvento(registroActivo.evento)"
                :text-color="textoEvento(registroActivo.evento)">
                {{ registroActivo.evento }}
              </q-chip>
            </div>
            <div class="col-6">
              <div class="text-caption text-grey-5">Fecha</div>
              <div class="text-body2">{{ registroActivo.fecha }}</div>
            </div>
          </div>

          <q-separator class="q-mb-md" />

          <!-- Cambios updated: antes / después -->
          <div v-if="registroActivo.evento === 'updated'" class="row q-col-gutter-md q-pa-sm">

            <div class="col-12 col-md-6">
              <q-card flat bordered>
                <q-card-section class="q-pa-sm">
                  <div class="text-caption text-weight-bold text-red q-mb-sm">
                    ANTES
                  </div>

                  <q-list dense separator>
                    <q-item v-for="(val, key) in registroActivo.cambios.old" :key="key">
                      <q-item-section>
                        <q-item-label caption class="text-grey-5">
                          {{ key }}
                        </q-item-label>
                        <q-item-label class="text-red">
                          {{ val ?? '—' }}
                        </q-item-label>
                      </q-item-section>
                    </q-item>
                  </q-list>

                </q-card-section>
              </q-card>
            </div>

            <div class="col-12 col-md-6">
              <q-card flat bordered>
                <q-card-section class="q-pa-sm">
                  <div class="text-caption text-weight-bold text-positive q-mb-sm">
                    DESPUÉS
                  </div>

                  <q-list dense separator>
                    <q-item v-for="(val, key) in registroActivo.cambios.attributes" :key="key">
                      <q-item-section>
                        <q-item-label caption class="text-grey-5">
                          {{ key }}
                        </q-item-label>
                        <q-item-label class="text-positive">
                          {{ val ?? '—' }}
                        </q-item-label>
                      </q-item-section>
                    </q-item>
                  </q-list>

                </q-card-section>
              </q-card>
            </div>

          </div>

          <!-- created / deleted: lista plana -->
          <div v-else>
            <div class="text-caption text-weight-bold q-mb-xs"
              :class="registroActivo.evento === 'deleted' ? 'text-negative' : 'text-positive'">
              {{ registroActivo.evento === 'created' ? 'DATOS CREADOS' : 'DATOS ELIMINADOS' }}
            </div>

            <!-- SI ES CREATED → ROW -->
            <div v-if="registroActivo.evento === 'created'"
              class="row q-col-gutter-md q-pa-sm justify-start items-stretch">
              <div v-for="(val, key) in (registroActivo.cambios.attributes ?? registroActivo.cambios)" :key="key"
                class="col-12 col-sm-6 col-md-4 col-lg-3">
                <q-card flat bordered class="full-height">
                  <q-card-section class="q-pa-sm text-center">
                    <div class="text-caption text-grey-6 ellipsis">
                      {{ key }}
                    </div>
                    <div class="text-body2 text-weight-medium">
                      {{ val ?? '—' }}
                    </div>
                  </q-card-section>
                </q-card>
              </div>
            </div>

            <!-- SI NO → LISTA NORMAL -->
            <q-list v-else dense bordered class="rounded-borders q-pa-md">
              <q-item v-for="(val, key) in (registroActivo.cambios.attributes ?? registroActivo.cambios)" :key="key"
                dense>
                <q-item-section>
                  <q-item-label caption class="text-grey-5">
                    {{ key }}
                  </q-item-label>
                  <q-item-label>
                    {{ val ?? '—' }}
                  </q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { date } from 'quasar'
import AuditService from 'src/services/AuditService'
import UsuarioService from 'src/services/UsuarioService'

const tableRef = ref()
const loading = ref(false)
const rows = ref([])
const usuarios = ref([])
const pagination = ref({
  sortBy: 'fecha', descending: true, page: 1, rowsPerPage: 15, rowsNumber: 0,
})

const filtros = ref({
  fecha_desde: '', fecha_hasta: '', modelo: null, evento: null, usuario_id: null,
})

const dialogCambios = ref(false)
const registroActivo = ref(null)

const opcionesModelo = [
  { label: 'Inventario', value: 'inventario' },
  { label: 'Venta', value: 'venta' },
  { label: 'Caja', value: 'caja' },
  { label: 'Usuario', value: 'usuario' },
]

const opcionesEvento = [
  { label: 'Creado', value: 'created' },
  { label: 'Actualizado', value: 'updated' },
  { label: 'Eliminado', value: 'deleted' },
]

const filtrosActivos = computed(() =>
  filtros.value.fecha_desde || filtros.value.fecha_hasta ||
  filtros.value.modelo || filtros.value.evento || filtros.value.usuario_id
)

const columns = [
  {
    name: 'fecha', label: 'FECHA REGISTRO', field: 'fecha', align: 'left', sortable: false,
    format: v => date.formatDate(new Date(v), 'DD/MM/YYYY HH:mm')
  },
  { name: 'evento', label: 'EVENTO', field: 'evento', align: 'center', sortable: false },
  { name: 'modelo', label: 'MODULO', field: 'modelo', align: 'left', sortable: false },
  { name: 'usuario', label: 'USUARIO', field: 'usuario', align: 'left', sortable: false },
  { name: 'cambios', label: 'CAMBIOS', field: 'cambios', align: 'center', sortable: false },
]

function colorEvento(evento) {
  return { created: 'green-1', updated: 'orange-1', deleted: 'red-1' }[evento] ?? 'grey-2'
}
function textoEvento(evento) {
  return { created: 'green-9', updated: 'orange-9', deleted: 'red-9' }[evento] ?? 'grey-8'
}
function iconoEvento(evento) {
  return { created: 'add_circle', updated: 'edit', deleted: 'delete' }[evento] ?? 'info'
}
function colorModelo(modelo) {
  return { Inventario: 'teal', Venta: 'blue', Caja: 'purple', User: 'indigo' }[modelo] ?? 'grey'
}

function tieneCambios(cambios) {
  if (!cambios) return false
  const attrs = cambios.attributes ?? cambios
  return attrs && Object.keys(attrs).length > 0
}

function verCambios(row) {
  registroActivo.value = row
  dialogCambios.value = true
}

function aplicarFiltros() {
  pagination.value.page = 1
  tableRef.value.requestServerInteraction()
}

function limpiarFiltros() {
  filtros.value = { fecha_desde: '', fecha_hasta: '', modelo: null, evento: null, usuario_id: null }
  aplicarFiltros()
}

async function onRequest(props) {
  const { page, rowsPerPage } = props.pagination
  loading.value = true
  try {
    const params = {
      rowsPerPage, page,
      ...(filtros.value.fecha_desde && { fecha_desde: filtros.value.fecha_desde }),
      ...(filtros.value.fecha_hasta && { fecha_hasta: filtros.value.fecha_hasta }),
      ...(filtros.value.modelo && { modelo: filtros.value.modelo }),
      ...(filtros.value.evento && { evento: filtros.value.evento }),
      ...(filtros.value.usuario_id && { usuario_id: filtros.value.usuario_id }),
    }
    const { data, total = 0 } = await AuditService.getData({ params })
    rows.value = data
    pagination.value = { ...pagination.value, rowsNumber: total || data.length, page, rowsPerPage }
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  const { data } = await UsuarioService.getData({ params: { rowsPerPage: 0 } })
  usuarios.value = data ?? []
  tableRef.value.requestServerInteraction()
})
</script>

<style lang="sass">
.my-sticky-header-table
  /* height or max-height is important */

  .q-table__top,
  .q-table__bottom,
  thead tr:first-child th
    /* bg color is important for th; just specify one */


  thead tr th
    position: sticky
    z-index: 1
    background-color: $secondary
    color: white
  thead tr:first-child th
    top: 0

  /* this is when the loading indicator appears */
  &.q-table--loading thead tr:last-child th
    /* height of all previous header rows */
    top: 48px

  /* prevent scrolling behind sticky top row on focus */
  tbody
    /* height of all previous header rows */
    scroll-margin-top: 48px

</style>