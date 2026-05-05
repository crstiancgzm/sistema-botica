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

  <q-page :class="$q.dark.isActive ? 'bg-dark-page' : 'bg-grey-1'">
    <div :class="$q.screen.lt.sm ? 'q-pa-md' : 'q-pa-lg'">

      <!-- ── Header ─────────────────────────────────────────── -->
      <div class="row items-start q-mb-md" :class="$q.screen.lt.sm ? 'q-gutter-sm' : 'no-wrap q-mb-lg'">
        <div class="col-12 col-sm">
          <div class="text-overline text-grey-5 q-mb-xs">Botica · Operaciones</div>
          <div :class="$q.screen.lt.sm ? 'text-h5' : 'text-h4'" class="text-weight-bold">Listado de Inventario</div>
          <div class="text-caption text-grey-5">Gestión de productos y stock de la botica</div>
        </div>
        <div class="col-12 col-sm-auto row q-gutter-xs items-center">
          <q-btn flat outline color="grey-6" icon="bi-upc-scan" no-caps
            :label="$q.screen.lt.md ? 'Códigos de barras' : 'Códigos de barras'"
            :disable="loading" @click="abrirGeneradorBarcodes" />
          <q-btn v-if="$q.screen.gt.sm" flat outline color="grey-6" icon="bi-download" no-caps
            label="Exportar" :disable="true" />
          <q-btn v-if="userStore.hasPermission('admin-inventario-crear')"
            color="primary" unelevated icon-right="add" no-caps
            :label="$q.screen.lt.sm ? 'Agregar Producto' : 'Agregar producto'"
            :disable="loading"
            @click="{ formInventarios = true; edit = false; title = 'REGISTRAR PRODUCTO'; editId = null; }" />
        </div>
      </div>

      <!-- ── Stats ──────────────────────────────────────────── -->
      <div class="row q-col-gutter-md q-mb-lg">
        <div class="col-xs-12 col-md-3">
          <q-card flat :bordered="!$q.dark.isActive" class="stat-card stat-card--pink">
            <q-card-section class="q-pa-md">
              <div class="row items-start no-wrap">
                <div class="col">
                  <div class="stat-label">Productos Activos</div>
                  <div class="stat-value">{{ stats.total.toLocaleString() }}</div>
                </div>
                <q-avatar size="44px" :color="$q.dark.isActive ? 'pink-9' : 'pink-1'" text-color="pink-6" square style="border-radius:10px">
                  <q-icon name="inventory_2" size="24px" />
                </q-avatar>
              </div>
            </q-card-section>
          </q-card>
        </div>
        <div class="col-xs-12 col-md-3">
          <q-card flat :bordered="!$q.dark.isActive" class="stat-card stat-card--green">
            <q-card-section class="q-pa-md">
              <div class="row items-start no-wrap">
                <div class="col">
                  <div class="stat-label">Valor de Inventario</div>
                  <div class="stat-value">S/ {{ formatMoney(stats.valorTotal) }}</div>
                </div>
                <q-avatar size="44px" :color="$q.dark.isActive ? 'green-9' : 'green-1'" text-color="green-7" square style="border-radius:10px">
                  <q-icon name="payments" size="24px" />
                </q-avatar>
              </div>
            </q-card-section>
          </q-card>
        </div>
        <div class="col-xs-12 col-md-3">
          <q-card flat :bordered="!$q.dark.isActive" class="stat-card stat-card--orange">
            <q-card-section class="q-pa-md">
              <div class="row items-start no-wrap">
                <div class="col">
                  <div class="stat-label">Stock Bajo</div>
                  <div class="stat-value">{{ stats.stockBajo }}</div>
                </div>
                <q-avatar size="44px" :color="$q.dark.isActive ? 'orange-9' : 'orange-1'" text-color="orange-7" square style="border-radius:10px">
                  <q-icon name="warning_amber" size="24px" />
                </q-avatar>
              </div>
            </q-card-section>
          </q-card>
        </div>
        <div class="col-xs-12 col-md-3">
          <q-card flat :bordered="!$q.dark.isActive" class="stat-card stat-card--red">
            <q-card-section class="q-pa-md">
              <div class="row items-start no-wrap">
                <div class="col">
                  <div class="stat-label">Sin Stock</div>
                  <div class="stat-value">{{ stats.sinStock }}</div>
                </div>
                <q-avatar size="44px" :color="$q.dark.isActive ? 'red-9' : 'red-1'" text-color="red-6" square style="border-radius:10px">
                  <q-icon name="error_outline" size="24px" />
                </q-avatar>
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>

      <!-- ── Buscador + filtros ────────────────────────────── -->
      <div class="row items-center q-gutter-sm q-mb-xs">
        <q-input class="col" outlined dense debounce="500"
          :bg-color="$q.dark.isActive ? 'grey-9' : 'white'"
          v-model="filter" placeholder="Buscar por nombre, SKU o laboratorio...">
          <template v-slot:prepend><q-icon name="search" color="grey-5" /></template>
          <template v-slot:append>
            <q-icon v-if="filter" name="close" class="cursor-pointer" @click="filter = ''" />
          </template>
        </q-input>
        <q-btn flat dense no-caps color="grey-7" icon="tune"
          :label="$q.screen.gt.xs ? 'Filtros' : ''"
          @click="showFiltros = !showFiltros">
          <q-badge v-if="filtrosActivos > 0" color="primary" floating>{{ filtrosActivos }}</q-badge>
        </q-btn>
      </div>

      <!-- ── Category tabs (scroll horizontal) ─────────────── -->
      <div class="tabs-scroll q-mb-sm">
        <q-btn dense rounded unelevated no-caps size="sm" class="cat-tab flex-shrink-0"
          :class="filtros.categoria === null ? 'cat-tab--active' : ''"
          @click="limpiarCategoriaTab">
          Todos <span class="q-ml-xs text-weight-bold">{{ stats.total }}</span>
        </q-btn>
        <q-btn v-for="cat in categoriasStats" :key="cat.id"
          dense rounded unelevated no-caps size="sm" class="cat-tab flex-shrink-0"
          :class="filtros.categoria?.id === cat.id ? 'cat-tab--active' : ''"
          @click="seleccionarCategoriaTab(cat)">
          {{ cat.nombre }} <span class="q-ml-xs text-weight-bold">{{ cat.count }}</span>
        </q-btn>
      </div>

      <!-- Panel de filtros -->
      <transition name="slide-down">
        <q-card v-if="showFiltros" flat :bordered="!$q.dark.isActive" class="q-mb-sm q-pa-md">
          <div class="row q-col-gutter-sm items-end">
            <div class="col-xs-12 col-sm-6 col-md-3 col-lg-2">
              <SelectGeneralAsyncrono v-model="filtros.laboratorio" label="Laboratorio"
                prepend-icon="science" :service-api="LaboratorioService" :add="false"
                @option-selected="aplicarFiltros" @clear="aplicarFiltros" />
            </div>
            <div class="col-xs-12 col-sm-6 col-md-3 col-lg-2">
              <SelectGeneralAsyncrono v-model="filtros.categoria" label="Categoría"
                prepend-icon="bi-tag" :service-api="CategoriaService" :add="false"
                @option-selected="aplicarFiltros" @clear="aplicarFiltros" />
            </div>
            <div class="col-xs-12 col-sm-6 col-md-3 col-lg-2">
              <SelectGeneralAsyncrono v-model="filtros.subcategoria" label="Malestar"
                prepend-icon="mdi-emoticon-sick-outline" :service-api="CategoriaMalestarService"
                :add="false" @option-selected="aplicarFiltros" @clear="aplicarFiltros" />
            </div>
            <div class="col-xs-12 col-sm-6 col-md-3 col-lg-2">
              <SelectGeneralAsyncrono v-model="filtros.area" label="Área"
                prepend-icon="bi-building" :service-api="AreaService" :add="false"
                @option-selected="aplicarFiltros" @clear="aplicarFiltros" />
            </div>
            <div class="col-xs-12 col-sm-6 col-md-3 col-lg-2 q-mb-lg">
              <q-toggle v-model="filtros.flag_blister" label="SOLO BLISTER"
                color="primary" checked-icon="check" @update:model-value="aplicarFiltros" />
            </div>
            <div class="col-xs-12 col-sm-6 col-md-3 col-lg-2 flex items-end q-mb-lg">
              <q-btn size="md" flat outline color="primary" icon="bi-stars" label="Limpiar"
                :disable="filtrosActivos === 0" @click="limpiarFiltros" />
            </div>
          </div>
        </q-card>
      </transition>

      <!-- ── Tabla ───────────────────────────────────────────── -->
      <q-card flat :bordered="!$q.dark.isActive">
        <q-table
          ref="tableRef"
          flat dense
          :rows="rows"
          :columns="columns"
          row-key="id"
          v-model:pagination="pagination"
          :loading="loading"
          :filter="filter"
          binary-state-sort
          :rows-per-page-options="[9, 12, 15, 25, 50]"
          class="inv-table"
          @request="onRequest"
        >
          <!-- Avatar -->
          <template v-slot:body-cell-avatar="props">
            <q-td :props="props" style="width:52px; padding-right:4px">
              <div class="inv-avatar">
                <img v-if="firstImage(props.row)" :src="firstImage(props.row)" />
                <q-icon v-else name="mdi-pill" size="18px" color="grey-5" />
              </div>
            </q-td>
          </template>

          <!-- Producto -->
          <template v-slot:body-cell-producto="props">
            <q-td :props="props">
              <div class="text-body2 text-weight-medium text-grey-9">{{ props.row.nombre }}</div>
              <div v-if="props.row.nombre_principio_activo" class="text-caption text-grey-5" style="font-style:italic">
                {{ props.row.nombre_principio_activo }}
              </div>
              <div class="row items-center q-gutter-xs q-mt-xs">
                <span class="text-caption text-grey-4">{{ props.row.codigo ?? '—' }}</span>
                <span v-if="estaVencido(props.row.fecha_vencimiento)" class="inv-badge inv-badge--red">Vencido</span>
                <span v-else-if="vencimientoCercano(props.row.fecha_vencimiento)" class="inv-badge inv-badge--orange">Vence pronto</span>
              </div>
            </q-td>
          </template>

          <!-- Laboratorio -->
          <template v-slot:body-cell-laboratorio="props">
            <q-td :props="props">
              <div class="row items-center q-gutter-xs no-wrap" v-if="props.row.laboratorio">
                <q-avatar size="24px" :color="labColor(props.row.laboratorio)" text-color="white" style="font-size:10px; font-weight:700">
                  L{{ props.row.laboratorio.id }}
                </q-avatar>
                <span class="text-caption text-grey-7">{{ props.row.laboratorio.nombre }}</span>
              </div>
              <span v-else class="text-caption text-grey-4">—</span>
            </q-td>
          </template>

          <!-- Presentación -->
          <template v-slot:body-cell-presentacion="props">
            <q-td :props="props">
              <span v-if="props.row.presentacion" class="pres-chip">
                {{ props.row.presentacion.nombre }}
              </span>
              <span v-else class="text-caption text-grey-4">—</span>
            </q-td>
          </template>

          <!-- Categorías -->
          <template v-slot:body-cell-categorias="props">
            <q-td :props="props">
              <div class="row items-center q-gutter-xs no-wrap">
                <span v-for="cat in props.row.categorias?.slice(0, 2)" :key="cat.id"
                  class="cat-chip" :style="catStyle(cat)">
                  {{ cat.nombre }}
                </span>
                <span v-if="props.row.categorias?.length > 2" class="text-caption text-grey-4">
                  +{{ props.row.categorias.length - 2 }}
                </span>
              </div>
            </q-td>
          </template>

          <!-- Stock -->
          <template v-slot:body-cell-cantidad="props">
            <q-td :props="props" class="text-center">
              <span v-if="props.row.cantidad === 0" class="stock-pill stock-pill--empty">
                <i class="stock-dot" />Sin stock
              </span>
              <span v-else-if="stockBajo(props.row)" class="stock-pill stock-pill--low">
                <i class="stock-dot" />{{ props.row.cantidad }} uds · bajo
                <q-tooltip v-if="props.row.fecha_vencimiento">Vence: {{ props.row.fecha_vencimiento?.slice(0, 10) }}</q-tooltip>
              </span>
              <span v-else class="stock-pill stock-pill--ok">
                <i class="stock-dot" />{{ props.row.cantidad }} uds
                <q-tooltip v-if="props.row.fecha_vencimiento">Vence: {{ props.row.fecha_vencimiento?.slice(0, 10) }}</q-tooltip>
              </span>
            </q-td>
          </template>

          <!-- Precio -->
          <template v-slot:body-cell-precio_unidad="props">
            <q-td :props="props">
              <span class="text-body2 text-weight-bold text-grey-8">S/ {{ props.row.precio_unidad }}</span>
            </q-td>
          </template>
          <template v-slot:body-cell-precio_blister="props">
            <q-td :props="props">
              <span class="text-body2 text-weight-bold text-grey-8">S/ {{ props.row.precio_blister }}</span>
            </q-td>
          </template>

          <!-- Acciones -->
          <template v-slot:body-cell-acciones="props">
            <q-td :props="props" auto-width>
              <q-btn size="sm" color="blue-1" text-color="blue-8" outline round
                icon="bi-eye" class="q-mr-xs" @click="verDetalle(props.row.id)">
                <q-tooltip>Ver detalle</q-tooltip>
              </q-btn>
              <q-btn v-if="userStore.hasPermission('admin-inventario-editar')" 
                size="sm" color="cyan-1" text-color="cyan-8" outline round
                icon="bi-pencil" class="q-mr-xs" @click="editar(props.row.id)">
                <q-tooltip>Editar</q-tooltip>
              </q-btn>
              <q-btn v-if="userStore.hasPermission('admin-inventario-eliminar')" 
                size="sm" color="red-1" text-color="red-13" outline round
                icon="bi-trash3" @click="eliminar(props.row.id)">
                <q-tooltip>Eliminar</q-tooltip>
              </q-btn>
            </q-td>
          </template>

          <template v-slot:no-data>
            <div class="full-width column flex-center q-pa-xl text-grey-5">
              <q-icon name="mdi-package-variant-remove" size="64px" class="q-mb-md" />
              <div class="text-h6">Sin productos</div>
              <div class="text-caption">Agrega el primer producto con el botón Agregar</div>
            </div>
          </template>
        </q-table>
      </q-card>

    </div>

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
import { useUserStore } from "src/stores/user-store";
const userStore = useUserStore();

const $q      = useQuasar();
const API_URL = process.env.API_BACKEND_URL;

const columns = [
  { name: "avatar",         label: "",              field: "nombre",                      align: "left",   sortable: false },
  { name: "producto",       label: "Producto",      field: (r) => r.nombre,               align: "left",   sortable: true  },
  { name: "laboratorio",    label: "Laboratorio",   field: (r) => r.laboratorio?.nombre,  align: "left",   sortable: false },
  { name: "presentacion",   label: "Presentación",  field: (r) => r.presentacion?.nombre, align: "left",   sortable: false },
  { name: "categorias",     label: "Categorías",    field: (r) => r.categorias,           align: "left",   sortable: false },
  { name: "cantidad",       label: "Stock",         field: (r) => r.cantidad,             align: "center", sortable: true  },
  { name: "precio_unidad", label: "Precio Unidad",        field: (r) => r.precio_unidad,       align: "right",  sortable: true  },
  { name: "precio_blister", label: "Precio Blister",        field: (r) => r.precio_blister,       align: "right",  sortable: true  },
  { name: "acciones",       label: "Acciones",      field: "id",                          align: "right",  sortable: false },
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
const pagination         = ref({ sortBy: "id", descending: true, page: 1, rowsPerPage: 10, rowsNumber: 0 });
const stats              = ref({ total: 0, sinStock: 0, stockBajo: 0, valorTotal: 0 });
const categoriasStats    = ref([]);

const LAB_COLORS = ['primary', 'purple', 'teal', 'deep-orange', 'deep-purple', 'green', 'indigo', 'pink'];
const CAT_PALETTE_LIGHT = [
  { bg: '#dbeafe', color: '#1d4ed8' },
  { bg: '#ccfbf1', color: '#0f766e' },
  { bg: '#fef9c3', color: '#a16207' },
  { bg: '#ede9fe', color: '#6d28d9' },
  { bg: '#dcfce7', color: '#15803d' },
  { bg: '#fce7f3', color: '#be185d' },
];
const CAT_PALETTE_DARK = [
  { bg: '#1e3a5f', color: '#93c5fd' },
  { bg: '#134e4a', color: '#5eead4' },
  { bg: '#422006', color: '#fde68a' },
  { bg: '#2e1065', color: '#c4b5fd' },
  { bg: '#14532d', color: '#86efac' },
  { bg: '#500724', color: '#fbcfe8' },
];

function labColor(lab) {
  if (!lab?.id) return 'grey-6';
  return LAB_COLORS[(lab.id - 1) % LAB_COLORS.length];
}

function catStyle(cat) {
  const palette = $q.dark.isActive ? CAT_PALETTE_DARK : CAT_PALETTE_LIGHT;
  const p = palette[(cat.id - 1) % palette.length];
  return `background:${p.bg}; color:${p.color}`;
}

function formatMoney(n) {
  return Number(n).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

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

function limpiarCategoriaTab() {
  filtros.value.categoria = null;
  aplicarFiltros();
}

function seleccionarCategoriaTab(cat) {
  filtros.value.categoria = cat;
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

async function fetchStats() {
  const { data, total } = await InventarioService.getData({ params: { rowsPerPage: 0, page: 1 } });
  stats.value.total      = total || data.length;
  stats.value.sinStock   = data.filter(r => r.cantidad === 0).length;
  stats.value.stockBajo  = data.filter(r => stockBajo(r)).length;
  stats.value.valorTotal = data.reduce((acc, r) => acc + Number(r.cantidad) * Number(r.precio_oficial), 0);

  const counts = {};
  data.forEach(r => {
    r.categorias?.forEach(cat => {
      if (!counts[cat.id]) counts[cat.id] = { ...cat, count: 0 };
      counts[cat.id].count++;
    });
  });
  categoriasStats.value = Object.values(counts).sort((a, b) => b.count - a.count).slice(0, 6);
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

onMounted(() => {
  tableRef.value.requestServerInteraction();
  fetchStats();
});

const save = () => {
  formInventarios.value = false;
  tableRef.value.requestServerInteraction();
  fetchStats();
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
    fetchStats();
    $q.notify({ type: "positive", message: "Eliminado con éxito.", position: "top-right", progress: true, timeout: 1500 });
  });
}
</script>

<style scoped>
/* ── Stats ─────────────────────────────────────────── */
.stat-card { border-radius: 12px !important; }
@media (max-width: 599px) {
  .stat-value { font-size: 22px !important; }
  .stat-label { font-size: 10px !important; }
}
.stat-card--pink   { border-left: 4px solid #f472b6 !important; }
.stat-card--green  { border-left: 4px solid #4ade80 !important; }
.stat-card--orange { border-left: 4px solid #fb923c !important; }
.stat-card--red    { border-left: 4px solid #f87171 !important; }
.stat-label {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #9ca3af;
  margin-bottom: 6px;
}
.stat-value {
  font-size: 28px;
  font-weight: 700;
  color: #111827;
  line-height: 1.1;
}
body.body--dark .stat-value { color: #f3f4f6; }

/* ── Category tabs ─────────────────────────────────── */
.cat-tab {
  background: white !important;
  color: #6b7280 !important;
  border: 1px solid #e5e7eb !important;
  font-size: 12px !important;
  padding: 4px 12px !important;
}
.cat-tab--active {
  background: var(--q-primary) !important;
  color: white !important;
  border-color: var(--q-primary) !important;
}
body.body--dark .cat-tab {
  background: #2d2d2d !important;
  color: #9ca3af !important;
  border-color: #374151 !important;
}

/* ── Product avatar ────────────────────────────────── */
.inv-avatar {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  background: #f3f4f6;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}
.inv-avatar img { width: 100%; height: 100%; object-fit: cover; }
body.body--dark .inv-avatar { background: #374151; }

/* ── Presentación chip ─────────────────────────────── */
.pres-chip {
  display: inline-block;
  padding: 2px 10px;
  border: 1px solid #d1d5db;
  border-radius: 20px;
  font-size: 12px;
  color: #374151;
  background: white;
}
body.body--dark .pres-chip { background: #1f2937; color: #d1d5db; border-color: #4b5563; }

/* ── Categoría chip ────────────────────────────────── */
.cat-chip {
  display: inline-block;
  padding: 2px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}

/* ── Alert badges ──────────────────────────────────── */
.inv-badge {
  display: inline-block;
  padding: 1px 6px;
  border-radius: 4px;
  font-size: 10px;
  font-weight: 600;
}
.inv-badge--red            { background: #fee2e2; color: #b91c1c; }
.inv-badge--orange         { background: #ffedd5; color: #c2410c; }
body.body--dark .inv-badge--red    { background: #450a0a; color: #fca5a5; }
body.body--dark .inv-badge--orange { background: #431407; color: #fdba74; }

/* ── Stock pills ───────────────────────────────────── */
.stock-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
}
.stock-dot {
  display: inline-block;
  width: 7px;
  height: 7px;
  border-radius: 50%;
  flex-shrink: 0;
}
.stock-pill--ok    { background: #dcfce7; color: #15803d; }
.stock-pill--ok    .stock-dot { background: #16a34a; }
.stock-pill--low   { background: #fef9c3; color: #a16207; }
.stock-pill--low   .stock-dot { background: #ca8a04; }
.stock-pill--empty { background: #fee2e2; color: #b91c1c; }
.stock-pill--empty .stock-dot { background: #dc2626; }

body.body--dark .stock-pill--ok    { background: #052e16; color: #86efac; }
body.body--dark .stock-pill--ok    .stock-dot { background: #22c55e; }
body.body--dark .stock-pill--low   { background: #1c1400; color: #fde68a; }
body.body--dark .stock-pill--low   .stock-dot { background: #eab308; }
body.body--dark .stock-pill--empty { background: #450a0a; color: #fca5a5; }
body.body--dark .stock-pill--empty .stock-dot { background: #ef4444; }

/* ── Category tabs scroll ──────────────────────────── */
.tabs-scroll {
  display: flex;
  flex-wrap: nowrap;
  overflow-x: auto;
  gap: 6px;
  padding-bottom: 4px;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none;
}
.tabs-scroll::-webkit-scrollbar { display: none; }
.flex-shrink-0 { flex-shrink: 0; }

/* ── Transition ────────────────────────────────────── */
.slide-down-enter-active,
.slide-down-leave-active { transition: all 0.2s ease; overflow: hidden; }
.slide-down-enter-from,
.slide-down-leave-to     { max-height: 0; opacity: 0; }
.slide-down-enter-to,
.slide-down-leave-from   { max-height: 300px; opacity: 1; }
</style>

<style lang="sass">
.inv-table
  height: 68vh

  thead tr th
    position: sticky
    z-index: 1
    top: 0
    background: white
    color: #6b7280
    font-size: 11px
    font-weight: 700
    text-transform: uppercase
    letter-spacing: 0.5px
    border-bottom: 2px solid #f3f4f6 !important

  &.q-table--loading thead tr:last-child th
    top: 48px

  tbody
    scroll-margin-top: 48px

  tbody tr td
    padding-top: 10px
    padding-bottom: 10px
    border-bottom: 1px solid #f9fafb !important

  tbody tr:hover td
    background: #f9fafb

body.body--dark
  .inv-table
    thead tr th
      background: $dark
      color: #6b7280
      border-bottom-color: #374151 !important

    tbody tr td
      border-bottom-color: #1f2937 !important

    tbody tr:hover td
      background: #1f2937
</style>
