<template>
  <q-dialog v-model="formInventarios" persistent>
    <InventariosForm :title="title" :edit="edit" :id="editId" ref="inventariosformRef"
      prop-path="inventario" @save="save" />
  </q-dialog>

  <q-dialog v-model="detalleDialog">
    <InventarioDetalle
      v-if="detalleId"
      :inventario-id="detalleId"
      @editar="(id) => { detalleDialog = false; editar(id) }"
    />
  </q-dialog>

  <q-page>
    <!-- Toolbar -->
    <div class="q-px-md q-pt-sm q-pb-sm row items-center q-gutter-sm">
      <q-btn  color="primary" icon-right="add"
        :label="$q.screen.lt.sm ? '' : 'Agregar producto'" :disable="loading"
        @click="{ formInventarios = true; edit = false; title = 'REGISTRAR PRODUCTO'; editId = null; }" />
      <q-btn color="secondary" icon="bi-upc-scan"
        :label="$q.screen.lt.sm ? '' : 'Códigos de barras'" :disable="loading"
        @click="abrirGeneradorBarcodes">
        <q-tooltip>Generar etiquetas con código de barras</q-tooltip>
      </q-btn>
      <q-input
        class="col"
        standout="bg-primary text-white" dense debounce="500"
        v-model="filter" placeholder="Buscar producto..."
      >
        <template v-slot:prepend><q-icon name="search" color="white" /></template>
        <template v-slot:append>
          <q-icon v-if="filter" name="close" class="cursor-pointer" @click="filter = ''" />
        </template>
      </q-input>
      <div class="text-caption text-grey-6" v-if="!loading">
        {{ pagination.rowsNumber }} resultado{{ pagination.rowsNumber !== 1 ? 's' : '' }}
      </div>
      <q-btn flat round dense icon="bi-funnel" :color="filtrosActivos > 0 ? 'primary' : 'grey-6'"
        @click="showFiltros = !showFiltros">
        <q-badge v-if="filtrosActivos > 0" color="primary" floating>{{ filtrosActivos }}</q-badge>
        <q-tooltip>{{ showFiltros ? 'Ocultar filtros' : 'Filtros' }}</q-tooltip>
      </q-btn>
    </div>

    <!-- Panel de filtros -->
    <transition name="slide-down">
      <q-card v-if="showFiltros" bordered flat class="q-ma-sm q-pt-md q-pl-md">
        <div class="row q-col-gutter-sm items-end">
          <div class="col-xs-12 col-sm-6 col-md-3 col-lg-2">
            <SelectGeneralAsyncrono
              v-model="filtros.laboratorio"
              label="Laboratorio"
              prepend-icon="science"
              :service-api="LaboratorioService"
              :add="false"
              @option-selected="aplicarFiltros"
              @clear="aplicarFiltros"
            />
          </div>
          <div class="col-xs-12 col-sm-6 col-md-3 col-lg-2">
            <SelectGeneralAsyncrono
              v-model="filtros.categoria"
              label="Categoría"
              prepend-icon="bi-tag"
              :service-api="CategoriaService"
              :add="false"
              @option-selected="aplicarFiltros"
              @clear="aplicarFiltros"
            />
          </div>
          <div class="col-xs-12 col-sm-6 col-md-3 col-lg-2">
            <SelectGeneralAsyncrono
              v-model="filtros.subcategoria"
              label="Malestar"
              prepend-icon="mdi-emoticon-sick-outline"
              :service-api="CategoriaMalestarService"
              :add="false"
              @option-selected="aplicarFiltros"
              @clear="aplicarFiltros"
            />
          </div>
          <div class="col-xs-12 col-sm-6 col-md-3 col-lg-2 ">
            <SelectGeneralAsyncrono
              v-model="filtros.area"
              label="Área"
              prepend-icon="bi-building"
              :service-api="AreaService"
              :add="false"
              @option-selected="aplicarFiltros"
              @clear="aplicarFiltros"
            />
          </div>
          <div class="col-xs-12 col-sm-6 col-md-3 col-lg-2 q-mb-lg">
            <q-toggle
              v-model="filtros.flag_blister"
              label="SOLO BLISTER"
              color="primary"
              checked-icon="check"
              @update:model-value="aplicarFiltros"
            />
          </div>
          <div class="col-xs-12 col-sm-6 col-md-3 col-lg-2 flex items-end q-mb-lg">
            <q-btn size="md" flat outline color="primary" icon="bi-stars" label="Limpiar"
              :disable="filtrosActivos === 0"
              @click="limpiarFiltros" />
          </div>
        </div>
      </q-card>
    </transition>

    <!-- Grid -->
    <q-table
      ref="tableRef"
      grid
      :rows="rows"
      :columns="columns"
      row-key="id"
      v-model:pagination="pagination"
      :loading="loading"
      :filter="filter"
      binary-state-sort
      :rows-per-page-options="[12, 16, 24, 32]"
      hide-header
      class="bg-transparent inv-grid q-px-sm"
      @request="onRequest"
    >
      <template v-slot:item="props">
        <div class="q-pa-xs col-xs-12 col-sm-2">
          <q-card class="inv-card">

            <!-- Imagen -->
            <div class="img-wrap">
              <q-img
                v-if="firstImage(props.row)"
                :src="firstImage(props.row)"
                :ratio="16 / 14"
                fit="cover"
                class="inv-img"
              >
                <template v-slot:error>
                  <div class="img-placeholder">
                    <q-icon name="bi-capsule" size="350px" />
                  </div>
                </template>
              </q-img>
              <div v-else class="img-placeholder" style="padding-top:100%">
                <div class="placeholder-inner">
                  <q-icon name="mdi-pill" size="32px" color="primary" />
                </div>
              </div>

              <!-- Badges flotantes -->
              <div class="overlay-top">
                <q-chip v-if="estaVencido(props.row.fecha_vencimiento)"
                  color="red-1" text-color="red-8" size="xs" dense icon="dangerous" class="q-ma-xs chip-sm" style="padding: 10px;">
                  Vencido
                </q-chip>
                <q-chip v-else-if="vencimientoCercano(props.row.fecha_vencimiento)"
                  color="warning" text-color="white" size="xs" dense icon="schedule" class="q-ma-xs chip-sm">
                  Vence pronto
                </q-chip>
                <q-chip v-if="stockBajo(props.row)"
                  color="negative" text-color="white" size="xs" dense icon="warning" class="q-ma-xs chip-sm">
                  Stock bajo
                </q-chip>
              </div>

              <!-- Stock badge -->
              <q-badge outline
                :color="props.row.cantidad === 0 ? 'grey-6' : stockBajo(props.row) ? 'negative' : 'positive'"
                class="stock-badge"
              >
                STOCK : {{ props.row.cantidad }}
              </q-badge>
            </div>

            <!-- Body -->
            <div class="q-px-sm q-pt-sm q-pb-xs">
              <div class="inv-nombre">{{ props.row.nombre }}</div>
              <div class="row items-center q-gutter-xs q-mt-xs">
                <span class="text-caption text-grey-8 q-ml-mt"># {{ props.row.codigo ?? '—' }}</span>
                <q-chip dense size="xs" color="blue-1" text-color="blue-8" class="q-ma-sm chip-pres" style="padding: 10px;">
                  Presentacion : {{ props.row.presentacion?.nombre ?? '—' }}
                </q-chip>
              </div>

              <div class="row items-center q-mt-xs q-gutter-xs">
                <q-icon name="science" size="14px" color="grey-6" />
                <span class="text-caption text-grey-8 ellipsis col">
                  {{ props.row.laboratorio?.nombre ?? '—' }}
                </span>
              </div>

              <!-- Categorías (máx 2) -->
              <div v-if="props.row.categorias?.length" class="row q-gutter-xs q-mt-xs">
                <q-chip
                  v-for="cat in props.row.categorias.slice(0, 2)" :key="cat.id"
                  dense size="xs" color="cyan-1" text-color="cyan-8" class="q-ma-none chip-cat">
                  {{ cat.nombre }}
                </q-chip>
                <q-chip v-if="props.row.categorias.length > 2" dense size="xs" class="q-ma-none chip-cat">
                  +{{ props.row.categorias.length - 2 }}
                </q-chip>
              </div>
            </div>

            <q-separator />

            <!-- Footer -->
            <div class="row items-center justify-between q-px-sm q-py-xs">
              <span class="text-subtitle2 text-weight-bold">
                S/. {{ props.row.precio_oficial }}
              </span>
              <div class="row q-gutter-xs">
                <q-btn round flat size="sm" color="grey-6" icon="bi-eye"
                  @click="verDetalle(props.row.id)">
                  <q-tooltip>Ver detalle</q-tooltip>
                </q-btn>
                <q-btn round flat size="sm" color="positive" icon="bi-pencil"
                  @click="editar(props.row.id)">
                  <q-tooltip>Editar</q-tooltip>
                </q-btn>
                <q-btn round flat size="sm" color="negative" icon="bi-trash3"
                  @click="eliminar(props.row.id)">
                  <q-tooltip>Eliminar</q-tooltip>
                </q-btn>
              </div>
            </div>

          </q-card>
        </div>
      </template>

      <template v-slot:no-data>
        <div class="full-width column flex-center q-pa-xl text-grey-5">
          <q-icon name="mdi-package-variant-remove" size="64px" class="q-mb-md" />
          <div class="text-h6">Sin productos</div>
          <div class="text-caption">Agrega el primer producto con el botón Agregar</div>
        </div>
      </template>
    </q-table>

    <q-inner-loading :showing="loading">
      <q-spinner-pie size="200px" color="primary" />
    </q-inner-loading>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import InventarioService from "src/services/InventarioService";
import LaboratorioService from "src/services/LaboratorioService";
import CategoriaService from "src/services/CategoriaService";
import CategoriaMalestarService from "src/services/CategoriaMalestarService";
import AreaService from "src/services/AreaService";
import { useQuasar } from "quasar";
import InventariosForm from "src/pages/Inventarios/InventariosForm.vue";
import InventarioDetalle from "src/pages/Inventarios/InventarioDetalle.vue";
import GeneradorBarcodesDialog from "src/pages/Inventarios/GeneradorBarcodesDialog.vue";
import SelectGeneralAsyncrono from "src/components/selectGeneralAsyncrono.vue";

const $q      = useQuasar();
const API_URL = process.env.API_BACKEND_URL;

const columns = [
  { name: "nombre",            field: (row) => row.nombre,            sortable: true },
  { name: "codigo",            field: (row) => row.codigo,            sortable: true },
  { name: "cantidad",          field: (row) => row.cantidad,          sortable: true },
  { name: "precio_oficial",    field: (row) => row.precio_oficial,    sortable: true },
  { name: "fecha_vencimiento", field: (row) => row.fecha_vencimiento, sortable: true },
];

const tableRef           = ref();
const formInventarios    = ref(false);
const inventariosformRef = ref();
const detalleDialog      = ref(false);
const detalleId          = ref(null);
const title              = ref("");
const edit               = ref(false);
const editId             = ref();
const rows               = ref([]);
const filter             = ref("");
const loading            = ref(false);
const showFiltros        = ref(false);
const filtros            = ref({ laboratorio: null, categoria: null, subcategoria: null, area: null, flag_blister: false });
const pagination         = ref({
  sortBy: "id", descending: true, page: 1, rowsPerPage: 12, rowsNumber: 0,
});

const filtrosActivos = computed(() => [
  filtros.value.laboratorio,
  filtros.value.categoria,
  filtros.value.subcategoria,
  filtros.value.area,
  filtros.value.flag_blister || null,
].filter(Boolean).length);

function aplicarFiltros() {
  pagination.value.page = 1;
  tableRef.value.requestServerInteraction();
}

function limpiarFiltros() {
  filtros.value = { laboratorio: null, categoria: null, subcategoria: null, area: null, flag_blister: false };
  aplicarFiltros();
}

function firstImage(row) {
  const archivo = row.archivos?.[0];
  if (!archivo?.url) return null;
  return `${API_URL}/storage/${archivo.url}`;
}

function stockBajo(row) {
  return row.stock_minimo != null && row.cantidad > 0 && row.cantidad <= row.stock_minimo;
}

function diasParaVencer(fecha) {
  if (!fecha) return null;
  return (new Date(fecha) - new Date()) / (1000 * 60 * 60 * 24);
}

function estaVencido(fecha) {
  const dias = diasParaVencer(fecha);
  return dias !== null && dias < 0;
}

function vencimientoCercano(fecha) {
  const dias = diasParaVencer(fecha);
  return dias !== null && dias >= 0 && dias <= 30;
}

async function onRequest(props) {
  const { page, rowsPerPage, sortBy, descending } = props.pagination;
  loading.value = true;
  const order_by = descending ? "-" + sortBy : sortBy;

  const params = {
    rowsPerPage: rowsPerPage || 0, page, search: props.filter, order_by,
    ...(filtros.value.laboratorio?.id  && { laboratorio_id:  filtros.value.laboratorio.id }),
    ...(filtros.value.categoria?.id    && { categoria_id:    filtros.value.categoria.id }),
    ...(filtros.value.subcategoria?.id && { subcategoria_id: filtros.value.subcategoria.id }),
    ...(filtros.value.area?.id         && { area_id:         filtros.value.area.id }),
    ...(filtros.value.flag_blister     && { flag_blister: 1 }),
  };

  const { data, total = 0 } = await InventarioService.getData({ params });
  rows.value.splice(0, rows.value.length, ...data);
  pagination.value = { ...pagination.value, rowsNumber: total || data.length, page, rowsPerPage, sortBy, descending };
  loading.value = false;
}

onMounted(() => tableRef.value.requestServerInteraction());

const save = () => {
  formInventarios.value = false;
  tableRef.value.requestServerInteraction();
  $q.notify({ type: "positive", message: "Guardado con éxito.", position: "top-right", progress: true, timeout: 1500 });
};

function verDetalle(id) {
  detalleId.value = id;
  detalleDialog.value = true;
}

async function editar(id) {
  title.value = "EDITAR INVENTARIO";
  formInventarios.value = true;
  edit.value  = true;
  editId.value = id;
  const row = await InventarioService.get(id);
  inventariosformRef.value.form.setData({ inventario: { archivos: [], ...row } });
}

function abrirGeneradorBarcodes() {
  $q.dialog({ component: GeneradorBarcodesDialog });
}

async function eliminar(id) {
  $q.dialog({
    title: "¿Estás seguro?",
    message: "Este proceso es irreversible.",
    cancel: true, persistent: true,
  }).onOk(async () => {
    await InventarioService.delete(id);
    tableRef.value.requestServerInteraction();
    $q.notify({ type: "positive", message: "Eliminado con éxito.", position: "top-right", progress: true, timeout: 1500 });
  });
}
</script>

<style scoped>
.inv-grid :deep(.q-table__grid-content) {
  padding: 0;
}

.inv-card {
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.07);
  transition: box-shadow 0.2s ease, transform 0.2s ease;
}
.inv-card:hover {
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
  transform: translateY(-2px);
}

/* Imagen */
.img-wrap {
  position: relative;
  background: #f5f5f5;
}
.inv-img {
  border-radius: 0;
}
.img-placeholder {
  position: relative;
  background: linear-gradient(135deg, #fce4ec, #fdf2f5);
}
.placeholder-inner {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Overlays */
.overlay-top {
  position: absolute;
  top: 0;
  left: 0;
  display: flex;
  flex-wrap: wrap;
}
.stock-badge {
  position: absolute;
  top: 6px;
  right: 6px;
  font-size: 12px;
  font-weight: 900;
  padding: 4px;
  border-radius: 8px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

/* Nombre del producto — 2 líneas máx */
.inv-nombre {
  font-weight: 600;
  display: -webkit-box;
}

/* Chips pequeños */
.chip-sm {
  font-size: 10px !important;
  padding: 0 6px !important;
  height: 18px !important;
}
.chip-pres {
  height: 18px !important;
  font-size: 10px !important;
  padding: 0 5px !important;
}
.chip-cat {
  font-size: 10px !important;
  padding: 10px !important;
  margin: 4px;
}


.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.2s ease;
  overflow: hidden;
}
.slide-down-enter-from,
.slide-down-leave-to {
  max-height: 0;
  opacity: 0;
  padding-top: 0;
  padding-bottom: 0;
}
.slide-down-enter-to,
.slide-down-leave-from {
  max-height: 200px;
  opacity: 1;
}

/* Texto truncado en 1 línea */
.ellipsis {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
