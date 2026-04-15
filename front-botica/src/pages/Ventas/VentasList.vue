<template>
  <q-dialog v-model="detalleDialog">
    <InventarioDetalle v-if="detalleId" :inventario-id="detalleId" />
  </q-dialog>

  <q-page >
    <div class="row no-wrap" style="min-height: inherit">

      <!-- ── SIDEBAR FILTROS ──────────────────────────── -->
      <div class="venta-sidebar q-pa-sm" :class="[$q.screen.lt.md ? 'sidebar-hidden' : '', $q.dark.isActive ? 'bg-dark' : 'bg-white']">

        <div class="text-overline text-grey-5 q-mb-sm">FILTROS</div>

        <q-expansion-item dense default-opened icon="bi-tag" label="Categoría" class="filter-section">
          <div class="q-pt-sm">
            <SelectGeneralAsyncrono v-model="filtros.categoria" label="Buscar categoría"
              prepend-icon="bi-tag" :service-api="CategoriaService" :add="false"
              @option-selected="aplicarFiltros" @clear="aplicarFiltros" />
          </div>
        </q-expansion-item>

        <q-separator class="q-my-sm" />

        <q-expansion-item dense default-opened icon="mdi-emoticon-sick-outline" label="Malestares" class="filter-section">
          <div class="q-pt-sm">
            <SelectGeneralAsyncrono v-model="filtros.subcategoria" label="Buscar malestar"
              prepend-icon="mdi-emoticon-sick-outline" :service-api="CategoriaMalestarService" :add="false"
              @option-selected="aplicarFiltros" @clear="aplicarFiltros" />
          </div>
        </q-expansion-item>

        <q-separator class="q-my-sm" />

        <q-expansion-item dense default-opened icon="science" label="Laboratorio" class="filter-section">
          <div class="q-pt-sm">
            <SelectGeneralAsyncrono v-model="filtros.laboratorio" label="Buscar laboratorio"
              prepend-icon="science" :service-api="LaboratorioService" :add="false"
              @option-selected="aplicarFiltros" @clear="aplicarFiltros" />
          </div>
        </q-expansion-item>

        <q-separator class="q-my-sm" />

        <q-expansion-item dense default-opened icon="bi-building" label="Área" class="filter-section">
          <div class="q-pt-sm">
            <SelectGeneralAsyncrono v-model="filtros.area" label="Buscar área"
              prepend-icon="bi-building" :service-api="AreaService" :add="false"
              @option-selected="aplicarFiltros" @clear="aplicarFiltros" />
          </div>
        </q-expansion-item>

        <q-separator class="q-my-sm" />

        <q-item dense class="q-px-none q-mt-xs">
          <q-item-section>
            <q-toggle v-model="filtros.flag_blister" label="Solo con blister"
              color="primary" @update:model-value="aplicarFiltros" />
          </q-item-section>
        </q-item>

        <q-btn v-if="filtrosActivos > 0" flat dense color="primary" icon="bi-stars"
          label="Limpiar filtros" class="full-width q-pa-sm" @click="limpiarFiltros" />
      </div>

      <!-- ── CONTENIDO PRINCIPAL ─────────────────────── -->
      <div class="col column">

        <!-- Toolbar -->
        <q-card class="q-mr-xs q-ml-sm q-mt-md" flat :bordered="!$q.dark.isActive">
        <div class="q-px-md q-pt-sm q-pb-sm row items-center q-gutter-sm">
          <q-btn v-if="$q.screen.lt.md" flat round dense icon="bi-funnel"
            :color="filtrosActivos > 0 ? 'primary' : 'grey-6'"
            @click="filtrosMobileOpen = true">
            <q-badge v-if="filtrosActivos > 0" color="primary" floating>{{ filtrosActivos }}</q-badge>
          </q-btn>

          <div class="text-h6 text-weight-bold text-primary col-auto">
            <q-icon name="bi-shop" class="q-mr-xs" />Punto de Venta
          </div>

          <q-input class="col" standout="bg-primary text-white" dense debounce="500"
            v-model="filter" placeholder="Buscar producto...">
            <template v-slot:prepend><q-icon name="search" color="white" /></template>
            <template v-slot:append>
              <q-icon v-if="filter" name="close" class="cursor-pointer" @click="filter = ''" />
            </template>
          </q-input>

          <div class="text-caption text-grey-6" v-if="!loading">
            {{ pagination.rowsNumber }} producto{{ pagination.rowsNumber !== 1 ? 's' : '' }}
          </div>

          <q-btn round unelevated color="primary" icon="bi-cart3" @click="carritoOpen = true">
            <q-badge v-if="totalItems > 0" color="negative" floating>{{ totalItems }}</q-badge>
            <q-tooltip>Ver carrito</q-tooltip>
          </q-btn>
        </div>
        </q-card>

        <!-- Grid de productos -->
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
             class="bg-transparent inv-grid q-px-sm col"
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
                         <div class="img-placeholder" style="padding-top:100%">
                           <div class="placeholder-inner">
                             <q-icon name="mdi-pill" size="32px" color="primary" />
                           </div>
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
                         color="red-1" text-color="red-8" size="xs" dense icon="dangerous"
                         class="q-ma-xs chip-sm" style="padding:10px">Vencido</q-chip>
                       <q-chip v-else-if="vencimientoCercano(props.row.fecha_vencimiento)"
                         color="warning" text-color="white" size="xs" dense icon="schedule"
                         class="q-ma-xs chip-sm">Vence pronto</q-chip>
                       <q-chip v-if="sinStock(props.row)"
                         color="grey-4" text-color="grey-7" size="xs" dense class="q-ma-xs chip-sm">
                         Sin stock</q-chip>
                     </div>
   
                     <!-- Stock badge -->
                     <q-badge outline
                       :color="props.row.cantidad === 0 ? 'grey-6' : stockBajo(props.row) ? 'negative' : 'positive'"
                       class="stock-badge">
                       STOCK : {{ props.row.cantidad }}
                     </q-badge>
                   </div>
   
                   <!-- Body -->
                   <div class="q-px-sm q-pt-sm q-pb-xs">
                     <div class="inv-nombre">{{ props.row.nombre }}</div>
                     <div class="row items-center q-gutter-xs q-mt-xs">
                       <span class="text-caption text-grey-8 q-ml-mt"># {{ props.row.codigo ?? '—' }}</span>
                       <q-chip dense size="xs" color="blue-1" text-color="blue-8"
                         class="q-ma-sm chip-pres" style="padding:10px">
                         {{ props.row.presentacion?.nombre ?? '—' }}
                       </q-chip>
                     </div>
                     <div class="row items-center q-mt-xs q-gutter-xs">
                       <q-icon name="science" size="14px" color="grey-6" />
                       <span class="text-caption text-grey-8 ellipsis col">
                         {{ props.row.laboratorio?.nombre ?? '—' }}
                       </span>
                     </div>
                     <div v-if="props.row.categorias?.length" class="row q-gutter-xs q-mt-xs">
                       <q-chip v-for="cat in props.row.categorias.slice(0, 2)" :key="cat.id"
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
                     <span class="text-subtitle2 text-weight-bold text-primary" v-if="props.row.precio_blister">
                       B ( S/. {{ props.row.precio_blister }} )
                     </span>
                     <span class="text-subtitle2 text-weight-bold text-primary" v-if="props.row.precio_unidad">
                      U ( S/. {{ props.row.precio_unidad }} )
                     </span>
                     <div class="row q-gutter-xs">
                       <q-btn round flat size="sm" color="grey-6" icon="bi-eye"
                         @click="verDetalle(props.row.id)">
                         <q-tooltip>Ver detalle</q-tooltip>
                       </q-btn>
                       <q-btn round unelevated size="sm" color="primary" icon="bi-cart-plus"
                         :disable="sinStock(props.row) || estaVencido(props.row.fecha_vencimiento)"
                         @click="agregar(props.row)">
                         <q-tooltip>Agregar al carrito</q-tooltip>
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
               </div>
             </template>
           </q-table>

      </div>
    </div>

    <!-- ── CARRITO (drawer derecho) ──────────────────── -->
    <q-drawer v-model="carritoOpen" side="right" bordered :width="310" overlay>
      <div class="column full-height">
        <div class="q-pa-sm row items-center justify-between bg-primary text-white" style="border-bottom-right-radius: 10px; border-bottom-left-radius: 10px;">
          <div class="text-subtitle1 text-weight-bold">
            <q-icon name="bi-cart3" size="sm" class="q-ml-sm q-mr-sm" />Punto de venta
          </div>
          <q-btn dense flat round icon="close" color="white" @click="carritoOpen = false" />
        </div>

        <q-scroll-area class="col">
          <div v-if="carrito.length === 0" class="column flex-center q-pa-xl text-grey-7">
            <q-icon name="bi-cart-x" size="48px" class="q-mb-sm" />
            <div class="text-caption">El carrito está vacío</div>
          </div>

          <q-list v-else separator class="q-px-sm q-pt-sm">
            <q-item v-for="item in carrito" :key="item.id" class="carrito-item q-mb-xs">
              <q-item-section avatar>
                <q-img v-if="firstImage(item)" :src="firstImage(item)"
                  style="width:44px;height:44px;border-radius:8px" fit="cover" />
                <div v-else class="carrito-img-placeholder">
                  <q-icon name="mdi-pill" size="20px" color="primary" />
                </div>
              </q-item-section>
              <q-item-section>
                <q-item-label class="carrito-nombre">{{ item.nombre }}</q-item-label>
                <div class="row items-center q-gutter-xs q-mt-xs">
                  <q-chip dense size="xs"
                    :color="item.tipo_venta === 'blister' ? 'orange-1' : 'blue-1'"
                    :text-color="item.tipo_venta === 'blister' ? 'orange-9' : 'blue-9'"
                    class="q-ma-none" style="padding: 8px;">
                    {{ item.tipo_venta === 'blister' ? 'Blister' : 'Unidad' }}
                  </q-chip>
                  <span class="text-caption">S/. {{ item.precio_venta }}</span>
                </div>
                <div class="row items-center q-gutter-xs q-mt-xs">
                  <q-btn round flat dense size="xs" icon="remove" @click="decrementar(item)" />
                  <span class="text-weight-bold" style="min-width:20px;text-align:center">{{ item.qty }}</span>
                  <q-btn round flat dense size="xs" icon="add"
                    :disable="!puedeIncrementar(item)" @click="item.qty++" />
                </div>
                <div class="text-caption text-grey-9 q-mt-xs">
                  <q-icon name="inventory_2" size="11px" />
                  consume {{ item.tipo_venta === 'blister' ? item.qty * (item.cantidad_blister || 1) : item.qty }} und. de {{ item.cantidad }}
                </div>
              </q-item-section>
              <q-item-section side>
                <div class="text-weight-bold text-primary">
                  S/. {{ (item.precio_venta * item.qty).toFixed(2) }}
                </div>
                <q-btn flat round dense size="xs" icon="delete" color="negative"
                  @click="quitarDelCarrito(item._key)" class="q-mt-xs" />
              </q-item-section>
            </q-item>
          </q-list>
        </q-scroll-area>

        <div class="q-pa-md" style="border-top:1px solid #eee">
          <div class="row justify-between q-mb-sm">
            <span class="text-subtitle2 text-grey-7">Total</span>
            <span class="text-h6 text-weight-bold text-primary">S/. {{ total }}</span>
          </div>
          <q-btn unelevated color="primary" icon="bi-receipt" label="Procesar venta"
            class="full-width" :disable="carrito.length === 0" />
          <q-btn flat color="negative" label="Vaciar carrito"
            class="full-width q-mt-xs" :disable="carrito.length === 0"
            @click="vaciarCarrito" />
        </div>
      </div>
    </q-drawer>

    <!-- ── FILTROS MOBILE ─────────────────────────────── -->
    <q-dialog v-model="filtrosMobileOpen" position="left" full-height>
      <q-card style="width:260px;max-width:90vw" class="q-pa-md">
        <div class="row items-center justify-between q-mb-md">
          <div class="text-subtitle1 text-weight-bold">Filtros</div>
          <q-btn flat round dense icon="close" v-close-popup />
        </div>
        <SelectGeneralAsyncrono v-model="filtros.categoria" label="Categoría"
          prepend-icon="bi-tag" :service-api="CategoriaService" :add="false"
          @option-selected="aplicarFiltros" @clear="aplicarFiltros" class="q-mb-sm" />
        <SelectGeneralAsyncrono v-model="filtros.subcategoria" label="Malestares"
          prepend-icon="mdi-emoticon-sick-outline" :service-api="CategoriaMalestarService" :add="false"
          @option-selected="aplicarFiltros" @clear="aplicarFiltros" class="q-mb-sm" />
        <SelectGeneralAsyncrono v-model="filtros.laboratorio" label="Laboratorio"
          prepend-icon="science" :service-api="LaboratorioService" :add="false"
          @option-selected="aplicarFiltros" @clear="aplicarFiltros" class="q-mb-sm" />
        <SelectGeneralAsyncrono v-model="filtros.area" label="Área"
          prepend-icon="bi-building" :service-api="AreaService" :add="false"
          @option-selected="aplicarFiltros" @clear="aplicarFiltros" class="q-mb-sm" />
        <q-toggle v-model="filtros.flag_blister" label="Solo con blister"
          color="primary" @update:model-value="aplicarFiltros" />
        <q-btn v-if="filtrosActivos > 0" flat dense color="negative" icon="bi-x-circle"
          label="Limpiar filtros" class="full-width q-mt-md" @click="limpiarFiltros" />
      </q-card>
    </q-dialog>

    <q-inner-loading :showing="loading">
      <q-spinner-pie size="200px" color="primary" />
    </q-inner-loading>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import InventarioService from "src/services/InventarioService";
import LaboratorioService from "src/services/LaboratorioService";
import CategoriaService from "src/services/CategoriaService";
import CategoriaMalestarService from "src/services/CategoriaMalestarService";
import AreaService from "src/services/AreaService";
import { useQuasar } from "quasar";
import { useLayoutStore } from "src/stores/layout-store";
import InventarioDetalle from "src/pages/Inventarios/InventarioDetalle.vue";
import SelectGeneralAsyncrono from "src/components/selectGeneralAsyncrono.vue";

const $q          = useQuasar();
const API_URL     = process.env.API_BACKEND_URL;
const layoutStore = useLayoutStore();

const columns = [
  { name: "nombre",            field: (row) => row.nombre,            sortable: true },
  { name: "codigo",            field: (row) => row.codigo,            sortable: true },
  { name: "cantidad",          field: (row) => row.cantidad,          sortable: true },
  { name: "precio_oficial",    field: (row) => row.precio_oficial,    sortable: true },
  { name: "fecha_vencimiento", field: (row) => row.fecha_vencimiento, sortable: true },
];

const tableRef          = ref();
const detalleDialog     = ref(false);
const detalleId         = ref(null);
const rows              = ref([]);
const filter            = ref("");
const loading           = ref(false);
const carritoOpen       = ref(false);
const filtrosMobileOpen = ref(false);
const carrito           = ref([]);
const filtros           = ref({ laboratorio: null, categoria: null, subcategoria: null, area: null, flag_blister: false });
const pagination        = ref({
  sortBy: "id", descending: true, page: 1, rowsPerPage: 12, rowsNumber: 0,
});

onMounted(() => {
  layoutStore.closeDrawer();
  tableRef.value.requestServerInteraction();
});
onUnmounted(() => layoutStore.openDrawer());

// ── Filtros ───────────────────────────────────────────
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

// ── Helpers ───────────────────────────────────────────
function firstImage(row) {
  const archivo = row.archivos?.[0];
  if (!archivo?.url) return null;
  return `${API_URL}/storage/${archivo.url}`;
}
function sinStock(row)  { return (row.cantidad ?? 0) === 0; }
function stockBajo(row) { return row.stock_minimo != null && row.cantidad > 0 && row.cantidad <= row.stock_minimo; }
function diasParaVencer(fecha) {
  if (!fecha) return null;
  return (new Date(fecha) - new Date()) / (1000 * 60 * 60 * 24);
}
function estaVencido(fecha)        { const d = diasParaVencer(fecha); return d !== null && d < 0; }
function vencimientoCercano(fecha) { const d = diasParaVencer(fecha); return d !== null && d >= 0 && d <= 30; }

// ── Tabla ─────────────────────────────────────────────
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

// ── Detalle ───────────────────────────────────────────
function verDetalle(id) {
  detalleId.value     = id;
  detalleDialog.value = true;
}

// ── Carrito ───────────────────────────────────────────
const totalItems = computed(() => carrito.value.reduce((s, i) => s + i.qty, 0));
const total      = computed(() => carrito.value.reduce((s, i) => s + i.precio_venta * i.qty, 0).toFixed(2));

function agregar(producto) {
  const tieneBlister = producto.flag_blister && producto.precio_blister;
  const tieneUnidad  = producto.flag_unidad  && producto.precio_unidad;

  if (tieneBlister && tieneUnidad) {
    mostrarDialogTipoVenta(producto);
    return;
  }
  if (tieneBlister) {
    agregarAlCarrito(producto, 'blister', producto.precio_blister);
    return;
  }
  agregarAlCarrito(producto, 'unidad', producto.precio_oficial);
}

function mostrarDialogTipoVenta(producto) {
  $q.dialog({
    title: producto.nombre,
    message: '¿Cómo desea vender este producto?',
    options: {
      type: 'radio',
      model: 'unidad',
      items: [
        { label: `Por unidad — S/. ${producto.precio_unidad} (x 1 unidad)`, value: 'unidad' },
        { label: `Blister — S/. ${producto.precio_blister} (x ${producto.cantidad_blister} unidades)`,   value: 'blister' },
      ],
    },
    cancel: true,
  }).onOk((tipo) => {
    const precio = tipo === 'blister' ? producto.precio_blister : producto.precio_unidad;
    agregarAlCarrito(producto, tipo, precio);
  });
}

function unidadesPorItem(item) {
  return item.tipo_venta === 'blister' ? (item.cantidad_blister || 1) : 1;
}

function consumidoPorProducto(productoId) {
  return carrito.value
    .filter(i => i.id === productoId)
    .reduce((sum, i) => sum + i.qty * unidadesPorItem(i), 0);
}

function puedeIncrementar(item) {
  return consumidoPorProducto(item.id) + unidadesPorItem(item) <= item.cantidad;
}

function agregarAlCarrito(producto, tipo, precio) {
  const key        = `${producto.id}_${tipo}`;
  const itemMock   = { tipo_venta: tipo, cantidad_blister: producto.cantidad_blister };
  const uPorItem   = unidadesPorItem(itemMock);
  const consumido  = consumidoPorProducto(producto.id);

  if (consumido + uPorItem > producto.cantidad) {
    $q.notify({ type: 'warning', message: 'Stock insuficiente para agregar más.', position: 'top-right', timeout: 1200 });
    return;
  }

  const existe = carrito.value.find(i => i._key === key);
  if (existe) {
    existe.qty++;
  } else {
    carrito.value.push({ ...producto, _key: key, qty: 1, tipo_venta: tipo, precio_venta: Number(precio) });
  }
  $q.notify({ type: 'positive', message: `${producto.nombre} agregado.`, position: 'top-right', timeout: 900 });
}

function decrementar(item) {
  if (item.qty <= 1) quitarDelCarrito(item._key);
  else item.qty--;
}
function quitarDelCarrito(key) {
  const idx = carrito.value.findIndex(i => i._key === key);
  if (idx !== -1) carrito.value.splice(idx, 1);
}
function vaciarCarrito() {
  $q.dialog({
    title: '¿Vaciar carrito?',
    message: 'Se eliminarán todos los productos.',
    cancel: true, persistent: true,
  }).onOk(() => { carrito.value = []; });
}
</script>

<style scoped>
.venta-page { display: flex; flex-direction: column; }

/* Sidebar */
.venta-sidebar {
  width: 250px;
  flex-shrink: 0;
  border-right: 1px solid #f0f0f0;
  position: sticky;
  top: 0;
  height: calc(100vh - 64px);
  overflow-y: auto;
  scrollbar-width: thin;
}
.venta-sidebar::-webkit-scrollbar { width: 4px; }
.venta-sidebar::-webkit-scrollbar-thumb { background: #e0e0e0; border-radius: 4px; }
.sidebar-hidden { display: none; }

.filter-section :deep(.q-expansion-item__header) {
  padding-left: 0;
  font-weight: 600;
  font-size: 13px;
}

/* Grid */
.inv-grid :deep(.q-table__grid-content) { padding: 0; }

.inv-card {
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,0.07);
  transition: box-shadow 0.2s ease, transform 0.2s ease;
}
.inv-card:hover {
  box-shadow: 0 6px 20px rgba(0,0,0,0.12);
  transform: translateY(-2px);
}

.img-wrap { position: relative; background: #f5f5f5; }
.inv-img  { border-radius: 0; }
.img-placeholder {
  position: relative;
  background: linear-gradient(135deg, #fce4ec, #fdf2f5);
}
.placeholder-inner {
  position: absolute; inset: 0;
  display: flex; align-items: center; justify-content: center;
}
.overlay-top { position: absolute; top: 0; left: 0; display: flex; flex-wrap: wrap; }
.stock-badge {
  position: absolute; top: 6px; right: 6px;
  font-size: 12px; font-weight: 900; padding: 4px;
  border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}
.inv-nombre {
  font-weight: 600;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.chip-sm   { font-size: 10px !important; padding: 0 6px !important; height: 18px !important; }
.chip-pres { height: 18px !important; font-size: 10px !important; padding: 0 5px !important; }
.chip-cat  { font-size: 10px !important; padding: 10px !important; margin: 4px; }
.ellipsis  { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* Carrito */
.carrito-item { border-radius: 10px; border: 1px solid #e0e0e0 ; }
.carrito-nombre {
  font-size: 12px; font-weight: 600; overflow: hidden;
  display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical;
}
.carrito-img-placeholder {
  width: 44px; height: 44px; border-radius: 8px;
  background: linear-gradient(135deg, #fce4ec, #fdf2f5);
  display: flex; align-items: center; justify-content: center;
}
</style>
