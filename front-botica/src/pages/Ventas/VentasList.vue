<template>
  <q-dialog v-model="detalleDialog">
    <InventarioDetalle v-if="detalleId" :inventario-id="detalleId" />
  </q-dialog>

  <q-page class="pos-page">
    <div class="pos-layout">

      <!-- ── SIDEBAR ── -->
      <aside class="pos-sidebar" v-if="sidebarOpen">
        <div class="pos-sidebar__head">
          <span class="pos-overline">Filtros</span>
          <button class="pos-clear-btn" @click="limpiarFiltros">Limpiar</button>
        </div>

        <div class="pos-sidebar__body">
          <!-- Categoría -->
          <div class="pos-section">
            <button class="pos-section__toggle" @click="sec.cat = !sec.cat">
              <span class="pos-section__label">
                <q-icon name="sell" size="12px" />Categoría
              </span>
              <q-icon :name="sec.cat ? 'expand_more' : 'chevron_right'" size="16px" style="color:var(--pos-ink-3)" />
            </button>
            <div v-if="sec.cat" class="pos-chips-wrap">
              <button v-for="c in categorias" :key="c.id"
                class="pos-chip" :class="{ active: filtros.categoria?.id === c.id }"
                @click="toggleFiltro('categoria', c)">
                {{ c.nombre }}
              </button>
            </div>
          </div>

          <!-- Malestares -->
          <div class="pos-section">
            <button class="pos-section__toggle" @click="sec.mal = !sec.mal">
              <span class="pos-section__label">
                <q-icon name="favorite_border" size="12px" />Malestares
              </span>
              <q-icon :name="sec.mal ? 'expand_more' : 'chevron_right'" size="16px" style="color:var(--pos-ink-3)" />
            </button>
            <div v-if="sec.mal" class="pos-chips-wrap">
              <button v-for="m in malestares" :key="m.id"
                class="pos-chip" :class="{ active: filtros.subcategoria?.id === m.id }"
                @click="toggleFiltro('subcategoria', m)">
                {{ m.nombre }}
              </button>
            </div>
          </div>

          <!-- Laboratorio -->
          <div class="pos-section">
            <button class="pos-section__toggle" @click="sec.lab = !sec.lab">
              <span class="pos-section__label">
                <q-icon name="science" size="12px" />Laboratorio
              </span>
              <q-icon :name="sec.lab ? 'expand_more' : 'chevron_right'" size="16px" style="color:var(--pos-ink-3)" />
            </button>
            <div v-if="sec.lab" class="pos-checkboxes">
              <label v-for="l in laboratorios" :key="l.id" class="pos-check-row">
                <span class="pos-checkbox" :class="{ checked: filtros.laboratorio?.id === l.id }"
                  @click="toggleFiltro('laboratorio', l)">
                  <q-icon v-if="filtros.laboratorio?.id === l.id" name="check" size="10px" />
                </span>
                <span class="pos-check-label" @click="toggleFiltro('laboratorio', l)">{{ l.nombre }}</span>
              </label>
            </div>
          </div>

          <!-- Área -->
          <div class="pos-section">
            <button class="pos-section__toggle" @click="sec.area = !sec.area">
              <span class="pos-section__label">
                <q-icon name="layers" size="12px" />Área
              </span>
              <q-icon :name="sec.area ? 'expand_more' : 'chevron_right'" size="16px" style="color:var(--pos-ink-3)" />
            </button>
            <div v-if="sec.area" class="pos-checkboxes">
              <label v-for="a in areas" :key="a.id" class="pos-check-row">
                <span class="pos-checkbox" :class="{ checked: filtros.area?.id === a.id }"
                  @click="toggleFiltro('area', a)">
                  <q-icon v-if="filtros.area?.id === a.id" name="check" size="10px" />
                </span>
                <span class="pos-check-label" @click="toggleFiltro('area', a)">{{ a.nombre }}</span>
              </label>
            </div>
          </div>

          <!-- Toggles -->
          <div class="pos-toggles">
            <div class="pos-toggle-row">
              <span class="pos-toggle-label">Solo con blíster</span>
              <span class="pos-switch" :class="{ on: filtros.flag_blister }" @click="toggleBool('flag_blister')">
                <span class="pos-switch__knob" />
              </span>
            </div>
            <div class="pos-toggle-row">
              <span class="pos-toggle-label">Solo disponibles</span>
              <span class="pos-switch" :class="{ on: filtros.flag_disponible }" @click="toggleBool('flag_disponible')">
                <span class="pos-switch__knob" />
              </span>
            </div>
            <div class="pos-toggle-row">
              <span class="pos-toggle-label">Precio promocional</span>
              <span class="pos-switch" :class="{ on: filtros.flag_promo }" @click="toggleBool('flag_promo')">
                <span class="pos-switch__knob" />
              </span>
            </div>
          </div>
        </div>
      </aside>

      <!-- ── MAIN ── -->
      <section class="pos-main">
        <!-- Toolbar -->
        <div class="pos-toolbar">
          <button class="pos-sidebar-toggle" @click="sidebarOpen = !sidebarOpen" :title="sidebarOpen ? 'Ocultar filtros' : 'Mostrar filtros'">
            <q-icon name="menu" size="18px" />
          </button>

          <div class="pos-toolbar__info">
            <div class="pos-toolbar__title-row">
              <q-icon name="point_of_sale" size="18px" style="color:var(--pos-accent)" />
              <span class="pos-toolbar__title">Punto de Venta</span>
            </div>
            <div class="pos-toolbar__sub">Catálogo · {{ pagination.rowsNumber }} productos</div>
          </div>

          <div class="pos-search-box">
            <q-icon name="search" size="16px" class="pos-search-icon" />
            <input v-model="searchInput" class="pos-search-input"
              placeholder="Buscar producto por nombre, código o laboratorio…" />
            <kbd class="pos-kbd">⌘ K</kbd>
          </div>

          <select v-model="sortField" class="pos-sort-select" @change="applySort">
            <option value="nombre">A → Z</option>
            <option value="precio_oficial">Precio ↓</option>
            <option value="-precio_oficial">Precio ↑</option>
            <option value="cantidad">Stock</option>
          </select>

          <div class="pos-view-btns">
            <button :class="{ active: viewMode === 'grid' }" @click="viewMode = 'grid'">
              <q-icon name="grid_view" size="16px" />
            </button>
            <button :class="{ active: viewMode === 'list' }" @click="viewMode = 'list'">
              <q-icon name="view_list" size="16px" />
            </button>
          </div>
        </div>

        <!-- Grid -->
        <div class="pos-grid-area">
          <q-table ref="tableRef" grid :rows="rows" :columns="columns" row-key="id"
            v-model:pagination="pagination" :loading="loading" :filter="filter"
            binary-state-sort :rows-per-page-options="[12, 16, 24, 32]"
            hide-header class="pos-qtable" @request="onRequest">

            <template v-slot:item="props">
              <div class="pos-card-cell">
                <div class="pos-card">
                  <!-- Image -->
                  <div class="pos-card__img" :style="imgStyle(props.row)">
                    <q-img v-if="firstImage(props.row)" :src="firstImage(props.row)"
                      fit="cover" style="width:100%;height:100%;border-radius:10px 10px 0 0">
                      <template v-slot:error>
                        <div class="pos-card__ph"><q-icon name="mdi-pill" size="24px" /></div>
                      </template>
                    </q-img>
                    <div v-else class="pos-card__ph">
                      <q-icon name="mdi-pill" size="24px" />
                    </div>

                    <div class="pos-stock" :class="stockClass(props.row)">
                      <span class="pos-stock__dot" />
                      STOCK · {{ props.row.cantidad }}
                    </div>

                    <div v-if="cartQty(props.row) > 0" class="pos-cart-qty">
                      ×{{ cartQty(props.row) }}
                    </div>
                  </div>

                  <!-- Body -->
                  <div class="pos-card__body">
                    <div class="pos-card__name">{{ props.row.nombre }}</div>

                    <div class="pos-card__meta-row">
                      <span class="pos-code"># {{ props.row.codigo ?? '—' }}</span>
                      <span class="pos-pres">{{ props.row.presentacion?.nombre ?? '—' }}</span>
                    </div>

                    <div class="pos-card__lab">
                      <q-icon name="science" size="11px" />
                      {{ props.row.laboratorio?.nombre ?? '—' }}
                    </div>

                    <div class="pos-card__cats">
                      <span v-for="cat in (props.row.categorias || []).slice(0, 2)" :key="cat.id" class="pos-cat">
                        {{ cat.nombre }}
                      </span>
                    </div>

                    <div class="pos-card__divider" />

                    <div class="pos-card__footer">
                      <div class="pos-price-block">
                        <div class="pos-unit" v-if="priceValUnidad(props.row) !== '0.00'">{{ priceLabelUnidad(props.row) }}</div>
                        <div class="pos-price" v-if="priceValUnidad(props.row) !== '0.00'">S/ {{ priceValUnidad(props.row) }}</div>
                        <div class="pos-unit" v-if="priceValBlister(props.row) !== '0.00'">{{ priceLabelBlister(props.row) }}</div>
                        <div class="pos-price" v-if="priceValBlister(props.row) !== '0.00'">S/ {{ priceValBlister(props.row) }}</div>
                      </div>
                      <div class="pos-card__btns">
                        <button class="pos-btn-eye" @click.stop="verDetalle(props.row.id)">
                          <q-icon name="visibility" size="14px" />
                        </button>
                        <button class="pos-btn-add"
                          :disabled="sinStock(props.row) || estaVencido(props.row.fecha_vencimiento)"
                          @click.stop="agregar(props.row)">
                          <q-icon name="add" size="14px" />
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </template>

            <template v-slot:no-data>
              <div class="pos-no-data">
                <q-icon name="mdi-package-variant-remove" size="52px" />
                <div class="pos-no-data__title">Sin resultados</div>
                <div class="pos-no-data__sub">Prueba con otro término o ajusta los filtros</div>
              </div>
            </template>
          </q-table>
        </div>
      </section>

      <!-- ── CART PANEL ── -->
      <aside class="pos-cart">
        <div class="pos-cart__head">
          <div>
            <div class="pos-cart__title">Venta actual</div>
            <div class="pos-cart__ticket">Ticket #{{ ticketNum }} · {{ hoy }}</div>
          </div>
          <div class="pos-items-badge">{{ totalItems }} {{ totalItems === 1 ? 'ítem' : 'ítems' }}</div>
        </div>

        <!-- <div class="pos-cart__client">
          <div class="pos-overline" style="margin-bottom:8px">Cliente</div>
          <div class="pos-client-row">
            <div class="pos-avatar">VG</div>
            <div class="pos-client-info">
              <div class="pos-client-name">Venta general</div>
              <div class="pos-client-sub">Sin datos del cliente · Boleta</div>
            </div>
            <button class="pos-edit-btn">Editar</button>
          </div>
        </div> -->

        <q-scroll-area class="pos-cart__scroll">
          <div v-if="carrito.length === 0" class="pos-cart__empty">
            <q-icon name="shopping_cart" size="32px"
              style="opacity:0.35;margin-bottom:8px;color:var(--pos-ink-3)" />
            <div style="font-size:13px;font-weight:600;color:var(--pos-ink-2)">Carrito vacío</div>
            <div style="font-size:11.5px;color:var(--pos-ink-3);margin-top:4px">
              Selecciona productos del catálogo
            </div>
          </div>

          <div v-else>
            <div v-for="item in carrito" :key="item._key" class="pos-cart-item">
              <div class="pos-cart-item__thumb">
                <q-icon name="mdi-pill" size="16px" style="color:var(--pos-ink-4)" />
              </div>
              <div class="pos-cart-item__body">
                <div class="pos-cart-item__name">{{ item.nombre }}</div>
                <div class="pos-cart-item__sub">
                  <span class="pos-code">#{{ item.codigo ?? item.id }}</span>
                  <span>·</span>
                  <span>{{ item.tipo_venta === 'blister' ? 'Blíster' : 'Unidad' }}</span>
                </div>
                <div class="pos-cart-item__controls">
                  <div class="pos-qty">
                    <button @click="decrementar(item)"><q-icon name="remove" size="11px" /></button>
                    <span>{{ item.qty }}</span>
                    <button @click="item.qty++" :disabled="!puedeIncrementar(item)">
                      <q-icon name="add" size="11px" />
                    </button>
                  </div>
                  <span class="pos-cart-item__total">
                    S/ {{ (item.precio_venta * item.qty).toFixed(2) }}
                  </span>
                </div>
              </div>
              <button class="pos-cart-item__del" @click="quitarDelCarrito(item._key)">
                <q-icon name="close" size="12px" />
              </button>
            </div>
          </div>
        </q-scroll-area>

        <div class="pos-cart__foot">
          <div class="pos-subtotals">
            <div class="pos-subtotal-row"><span>Subtotal</span><span>S/ {{ subtotal }}</span></div>
            <div class="pos-subtotal-row"><span>IGV (18%)</span><span>S/ {{ igv }}</span></div>
          </div>
          <div class="pos-grand-row">
            <span>Total</span>
            <span class="pos-grand-amount">S/ {{ total }}</span>
          </div>

          <div class="pos-pay-row">
            <button class="pos-pay-btn" :class="{ 'active_tarjeta': pago === 'efectivo' }" @click="pago = 'efectivo'">
              <q-icon name="payments" size="18px" /><span>Efectivo</span>
            </button>
            <button class="pos-pay-btn" :class="{ 'active_tarjeta': pago === 'tarjeta' }" @click="pago = 'tarjeta'">
              <q-icon name="credit_card" size="18px" /><span>Tarjeta</span>
            </button>
            <button class="pos-pay-btn" :class="{ 'active_tarjeta': pago === 'transferencia' }" @click="pago = 'transferencia'">
              <q-icon name="smartphone" size="18px" /><span>Yape</span>
            </button>
          </div>

          <button class="pos-checkout" :disabled="carrito.length === 0" @click="procesarVenta">
            Cobrar · S/ {{ total }}
            <q-icon name="arrow_forward" size="14px" />
          </button>
        </div>
      </aside>

    </div>

    <q-inner-loading :showing="loading">
      <q-spinner-pie size="80px" color="primary" />
    </q-inner-loading>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import InventarioService from "src/services/InventarioService";
import LaboratorioService from "src/services/LaboratorioService";
import CategoriaService from "src/services/CategoriaService";
import CategoriaMalestarService from "src/services/CategoriaMalestarService";
import AreaService from "src/services/AreaService";
import { useQuasar } from "quasar";
import { useLayoutStore } from "src/stores/layout-store";
import InventarioDetalle from "src/pages/Inventarios/InventarioDetalle.vue";
import ProcesarVentaDialog from "src/pages/Ventas/ProcesarVentaDialog.vue";

const $q          = useQuasar();
const API_URL     = process.env.API_BACKEND_URL;
const layoutStore = useLayoutStore();

const pago = ref("efectivo");

// ── UI ──────────────────────────────────────────
const sidebarOpen = ref(true);
const viewMode    = ref('grid');
const sec         = ref({ cat: true, mal: true, lab: true, area: false });

// ── Filter options (loaded on mount) ─────────────
const categorias   = ref([]);
const malestares   = ref([]);
const laboratorios = ref([]);
const areas        = ref([]);

// ── Table ────────────────────────────────────────
const tableRef   = ref();
const rows       = ref([]);
const loading    = ref(false);
const filter     = ref('');
const searchInput = ref('');
const sortField  = ref('nombre');
const columns    = [
  { name: 'nombre',            field: r => r.nombre,            sortable: true },
  { name: 'codigo',            field: r => r.codigo,            sortable: true },
  { name: 'cantidad',          field: r => r.cantidad,          sortable: true },
  { name: 'precio_oficial',    field: r => r.precio_oficial,    sortable: true },
  { name: 'fecha_vencimiento', field: r => r.fecha_vencimiento, sortable: true },
];
const pagination = ref({
  sortBy: 'id', descending: true, page: 1, rowsPerPage: 12, rowsNumber: 0,
});

// ── Filters ──────────────────────────────────────
const filtros = ref({
  laboratorio: null, categoria: null, subcategoria: null, area: null,
  flag_blister: false, flag_disponible: true, flag_promo: false,
});

// ── Cart ─────────────────────────────────────────
const carrito       = ref([]);
const detalleDialog = ref(false);
const detalleId     = ref(null);
const ticketNum     = ref(String(Math.floor(Math.random() * 99999)).padStart(5, '0'));

// ── Derived ──────────────────────────────────────
const totalItems = computed(() => carrito.value.reduce((s, i) => s + i.qty, 0));
const total      = computed(() => carrito.value.reduce((s, i) => s + i.precio_venta * i.qty, 0).toFixed(2));
const subtotal   = computed(() => (Number(total.value) / 1.18).toFixed(2));
const igv        = computed(() => (Number(total.value) - Number(subtotal.value)).toFixed(2));
const hoy        = computed(() => new Date().toLocaleDateString('es-PE'));

// ── Lifecycle ────────────────────────────────────
let searchTimer = null;

onMounted(async () => {
  layoutStore.closeDrawer();
  tableRef.value.requestServerInteraction();
  loadFiltroData();
});
onUnmounted(() => layoutStore.openDrawer());

watch(searchInput, val => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    pagination.value.page = 1;
    filter.value = val;
  }, 400);
});

async function loadFiltroData() {
  const toArr = r => (Array.isArray(r) ? r : r?.data) ?? [];
  const [cats, labs, mals, ars] = await Promise.all([
    CategoriaService.getData({ params: { rowsPerPage: 0 } }).catch(() => []),
    LaboratorioService.getData({ params: { rowsPerPage: 0 } }).catch(() => []),
    CategoriaMalestarService.getData({ params: { rowsPerPage: 0 } }).catch(() => []),
    AreaService.getData({ params: { rowsPerPage: 0 } }).catch(() => []),
  ]);
  categorias.value   = toArr(cats);
  laboratorios.value = toArr(labs);
  malestares.value   = toArr(mals);
  areas.value        = toArr(ars);
}

// ── Filters ──────────────────────────────────────
function toggleFiltro(key, val) {
  filtros.value[key] = filtros.value[key]?.id === val.id ? null : val;
  aplicarFiltros();
}

function toggleBool(key) {
  filtros.value[key] = !filtros.value[key];
  aplicarFiltros();
}

function aplicarFiltros() {
  pagination.value.page = 1;
  tableRef.value.requestServerInteraction();
}

function limpiarFiltros() {
  filtros.value = {
    laboratorio: null, categoria: null, subcategoria: null, area: null,
    flag_blister: false, flag_disponible: true, flag_promo: false,
  };
  aplicarFiltros();
}

function applySort() {
  const desc  = sortField.value.startsWith('-');
  const field = sortField.value.replace('-', '');
  pagination.value.sortBy     = field;
  pagination.value.descending = desc;
  pagination.value.page       = 1;
  tableRef.value.requestServerInteraction();
}

// ── Table request ────────────────────────────────
async function onRequest(props) {
  const { page, rowsPerPage, sortBy, descending } = props.pagination;
  loading.value = true;
  const order_by = descending ? '-' + sortBy : sortBy;
  const params = {
    rowsPerPage: rowsPerPage || 0, page, search: props.filter, order_by,
    flag_disponible: filtros.value.flag_disponible,
    ...(filtros.value.laboratorio?.id  && { laboratorio_id:  filtros.value.laboratorio.id }),
    ...(filtros.value.categoria?.id    && { categoria_id:    filtros.value.categoria.id }),
    ...(filtros.value.subcategoria?.id && { subcategoria_id: filtros.value.subcategoria.id }),
    ...(filtros.value.area?.id         && { area_id:         filtros.value.area.id }),
    ...(filtros.value.flag_blister     && { flag_blister: 1 }),
    ...(filtros.value.flag_disponible  && { flag_disponible: 1 }),
  };
  const { data, total: t = 0 } = await InventarioService.getData({ params });
  rows.value.splice(0, rows.value.length, ...data);
  pagination.value = { ...pagination.value, rowsNumber: t || data.length, page, rowsPerPage, sortBy, descending };
  loading.value = false;
}

// ── Helpers ──────────────────────────────────────
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
function estaVencido(fecha) {
  const d = diasParaVencer(fecha);
  return d !== null && d < 0;
}

function stockClass(row) {
  if (sinStock(row)) return 'grey';
  if (stockBajo(row)) return 'amber';
  if ((row.cantidad ?? 0) < 150) return 'teal';
  return 'mint';
}

function imgStyle(row) {
  const seed = (row.nombre || '').charCodeAt(0) || 65;
  const hue  = (seed * 23) % 360;
  return { background: `linear-gradient(180deg, oklch(0.97 0.02 ${hue}), oklch(0.94 0.03 ${hue}))` };
}

function cartQty(row) {
  return carrito.value.filter(i => i.id === row.id).reduce((s, i) => s + i.qty, 0);
}

function priceLabelUnidad(row) {
  return row.flag_unidad ? 'UNIDAD' : 'BLÍSTER';
}

function priceValUnidad(row) {
  return Number(row.precio_unidad || 0).toFixed(2);
}

function priceLabelBlister(row) {
  return row.flag_blister ? 'BLÍSTER' : 'UNIDAD';
}

function priceValBlister(row) {
  return Number(row.precio_blister || 0).toFixed(2);
}


// ── Detalle ──────────────────────────────────────
function verDetalle(id) {
  detalleId.value     = id;
  detalleDialog.value = true;
}

// ── Carrito ──────────────────────────────────────
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

function agregar(producto) {
  const tieneBlister = producto.flag_blister && producto.precio_blister;
  const tieneUnidad  = producto.flag_unidad  && producto.precio_unidad;
  if (tieneBlister && tieneUnidad) { mostrarDialogTipoVenta(producto); return; }
  if (tieneBlister) { agregarAlCarrito(producto, 'blister', producto.precio_blister); return; }
  agregarAlCarrito(producto, 'unidad', producto.precio_oficial);
}

function mostrarDialogTipoVenta(producto) {
  $q.dialog({
    title: producto.nombre,
    message: '¿Cómo desea vender este producto?',
    options: {
      type: 'radio', model: 'unidad',
      items: [
        { label: `Por unidad — S/. ${producto.precio_unidad}`, value: 'unidad' },
        { label: `Blíster — S/. ${producto.precio_blister} (x ${producto.cantidad_blister} und.)`, value: 'blister' },
      ],
    },
    cancel: true,
  }).onOk(tipo => {
    const precio = tipo === 'blister' ? producto.precio_blister : producto.precio_unidad;
    agregarAlCarrito(producto, tipo, precio);
  });
}

function agregarAlCarrito(producto, tipo, precio) {
  const key      = `${producto.id}_${tipo}`;
  const uPorItem = unidadesPorItem({ tipo_venta: tipo, cantidad_blister: producto.cantidad_blister });
  if (consumidoPorProducto(producto.id) + uPorItem > producto.cantidad) {
    $q.notify({ type: 'warning', message: 'Stock insuficiente.', position: 'top-right', timeout: 1200 });
    return;
  }
  const existe = carrito.value.find(i => i._key === key);
  if (existe) { existe.qty++; }
  else { carrito.value.push({ ...producto, _key: key, qty: 1, tipo_venta: tipo, precio_venta: Number(precio) }); }
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

function procesarVenta() {
  $q.dialog({
    component: ProcesarVentaDialog,
    componentProps: { carrito: carrito.value, total: total.value, tipoPago: pago.value },
  }).onOk(() => {
    carrito.value = [];
    tableRef.value.requestServerInteraction();
  });
}
</script>

<style scoped lang="scss">
.pos-page {
  --pos-bg:        #f7f7f5;
  --pos-surface:   #ffffff;
  --pos-surface-2: #fafaf8;
  --pos-border:    #ececea;
  --pos-border-s:  #d9d9d4;
  --pos-ink:       #17171a;
  --pos-ink-2:     #45464b;
  --pos-ink-3:     #7a7b80;
  --pos-ink-4:     #a8a9ad;

  --pos-accent:     oklch(0.62 0.18 10);
  --pos-accent-ink: oklch(0.42 0.18 10);
  --pos-accent-bg:  oklch(0.96 0.03 10);
  --pos-teal:       oklch(0.62 0.12 200);
  --pos-teal-bg:    oklch(0.96 0.03 200);
  --pos-mint:       oklch(0.64 0.12 160);
  --pos-mint-bg:    oklch(0.95 0.04 160);
  --pos-amber:      oklch(0.72 0.14 75);
  --pos-amber-bg:   oklch(0.96 0.05 75);

  background: var(--pos-bg);
  font-family: 'Inter', sans-serif;
}

/* ── Layout ── */
.pos-layout {
  display: flex;
  height: calc(100vh - 50px);
  overflow: hidden;
}

/* ── Sidebar ── */
.pos-sidebar {
  width: 272px;
  flex-shrink: 0;
  background: var(--pos-surface);
  border-right: 1px solid var(--pos-border);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.pos-sidebar__head {
  padding: 14px 16px;
  border-bottom: 1px solid var(--pos-border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-shrink: 0;
}

.pos-overline {
  font-size: 11.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  color: var(--pos-ink);
}

.pos-clear-btn {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  color: var(--pos-accent-ink);
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
}

.pos-sidebar__body {
  flex: 1;
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: var(--pos-border-s) transparent;

  &::-webkit-scrollbar { width: 4px; }
  &::-webkit-scrollbar-thumb { background: var(--pos-border-s); border-radius: 4px; }
}

.pos-section {
  border-bottom: 1px solid var(--pos-border);
  padding: 14px 16px;
}

.pos-section__toggle {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
}

.pos-section__label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  font-weight: 600;
  color: var(--pos-ink-2);
  text-transform: uppercase;
  letter-spacing: 0.4px;
}

.pos-chips-wrap {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 12px;
}

.pos-chip {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 5px 10px;
  border-radius: 999px;
  border: 1px solid var(--pos-border);
  background: var(--pos-surface);
  color: var(--pos-ink-2);
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: all .12s;
  font-family: inherit;

  &:hover { border-color: var(--pos-border-s); }
  &.active {
    border-color: var(--pos-accent);
    background: var(--pos-accent-bg);
    color: var(--pos-accent-ink);
  }
}

.active_tarjeta {
  background-color: #000000 !important;
  color: white !important;
}

.pos-checkboxes {
  display: flex;
  flex-direction: column;
  gap: 2px;
  margin-top: 12px;
}

.pos-check-row {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 5px 3px;
  cursor: pointer;
  border-radius: 6px;

  &:hover { background: var(--pos-surface-2); }
}

.pos-checkbox {
  width: 16px;
  height: 16px;
  border-radius: 4px;
  border: 1.5px solid var(--pos-border-s);
  background: transparent;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  cursor: pointer;
  transition: all .12s;
  color: #fff;

  &.checked {
    background: var(--pos-accent);
    border-color: var(--pos-accent);
  }
}

.pos-check-label {
  font-size: 13px;
  color: var(--pos-ink-2);
  cursor: pointer;
}

.pos-toggles {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.pos-toggle-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 5px 0;
}

.pos-toggle-label {
  font-size: 13px;
  color: var(--pos-ink-2);
}

.pos-switch {
  width: 30px;
  height: 18px;
  border-radius: 999px;
  background: var(--pos-border-s);
  position: relative;
  cursor: pointer;
  transition: background .15s;
  flex-shrink: 0;

  &.on { background: var(--pos-accent); }
}

.pos-switch__knob {
  position: absolute;
  top: 2px;
  left: 2px;
  width: 14px;
  height: 14px;
  border-radius: 50%;
  background: #fff;
  box-shadow: 0 1px 2px rgba(0,0,0,.2);
  transition: left .15s;

  .pos-switch.on & { left: 14px; }
}

/* ── Main ── */
.pos-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  background: var(--pos-bg);
}

/* ── Toolbar ── */
.pos-toolbar {
  background: var(--pos-surface);
  border-bottom: 1px solid var(--pos-border);
  padding: 14px 22px;
  display: flex;
  align-items: center;
  gap: 12px;
  flex-shrink: 0;
}

.pos-sidebar-toggle {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: none;
  background: transparent;
  color: var(--pos-ink-2);
  display: grid;
  place-items: center;
  cursor: pointer;
  flex-shrink: 0;
  transition: all .12s;

  &:hover { background: var(--pos-surface-2); color: var(--pos-ink); }
}

.pos-toolbar__info { margin-right: 4px; }

.pos-toolbar__title-row {
  display: flex;
  align-items: center;
  gap: 8px;
}

.pos-toolbar__title {
  font-size: 16px;
  font-weight: 700;
  letter-spacing: -0.2px;
  color: var(--pos-ink);
}

.pos-toolbar__sub {
  font-size: 11.5px;
  color: var(--pos-ink-3);
  margin-left: 26px;
  margin-top: 1px;
}

.pos-search-box {
  flex: 1;
  max-width: 560px;
  position: relative;
  display: flex;
  align-items: center;
}

.pos-search-icon {
  position: absolute;
  left: 12px;
  color: var(--pos-ink-3);
  pointer-events: none;
}

.pos-search-input {
  width: 100%;
  padding: 9px 48px 9px 36px;
  border: 1px solid var(--pos-border);
  border-radius: 9px;
  background: var(--pos-surface-2);
  font-size: 13px;
  font-family: inherit;
  color: var(--pos-ink);
  outline: none;
  transition: all .12s;

  &:focus {
    background: #fff;
    border-color: var(--pos-accent);
  }
  &::placeholder { color: var(--pos-ink-3); }
}

.pos-kbd {
  position: absolute;
  right: 10px;
  padding: 2px 6px;
  border-radius: 4px;
  border: 1px solid var(--pos-border);
  font-size: 10px;
  color: var(--pos-ink-3);
  background: #fff;
  font-family: inherit;
  font-weight: 500;
  pointer-events: none;
}

.pos-sort-select {
  padding: 8px 12px;
  border-radius: 8px;
  border: 1px solid var(--pos-border);
  background: var(--pos-surface-2);
  font-size: 12.5px;
  color: var(--pos-ink-2);
  font-family: inherit;
  cursor: pointer;
  outline: none;
}

.pos-view-btns {
  display: inline-flex;
  border: 1px solid var(--pos-border);
  border-radius: 8px;
  overflow: hidden;

  button {
    padding: 7px 9px;
    background: transparent;
    color: var(--pos-ink-3);
    border: none;
    cursor: pointer;
    display: grid;
    place-items: center;
    transition: all .12s;

    &.active { background: var(--pos-surface-2); color: var(--pos-ink); }
    &:hover:not(.active) { background: var(--pos-surface-2); }
  }
}

/* ── Grid Area ── */
.pos-grid-area {
  flex: 1;
  overflow-y: auto;
  padding: 18px 22px;
  scrollbar-width: thin;
}

.pos-qtable {
  background: transparent !important;

  :deep(.q-table__grid-content) {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    gap: 14px;
    padding: 0;
  }

  :deep(.q-table__bottom) {
    background: transparent;
    border-top: 1px solid var(--pos-border);
    margin-top: 8px;
  }
}

.pos-card-cell { display: contents; }

/* ── Product Card ── */
.pos-card {
  background: var(--pos-surface);
  border: 1px solid var(--pos-border);
  border-radius: 10px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transition: all .14s;
  box-shadow: 0 1px 2px rgba(20,20,25,.04);

  &:hover {
    border-color: var(--pos-border-s);
    box-shadow: 0 4px 12px rgba(20,20,25,.06);
    transform: translateY(-1px);
  }
}

.pos-card__img {
  width: 100%;
  height: 140px;
  position: relative;
  border-radius: 10px 10px 0 0;
  overflow: hidden;
}

.pos-card__ph {
  width: 100%;
  height: 100%;
  display: grid;
  place-items: center;
  color: var(--pos-ink-4);
  opacity: 0.5;
}

.pos-stock {
  position: absolute;
  top: 8px;
  left: 8px;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 3px 7px 3px 6px;
  border-radius: 999px;
  font-size: 10.5px;
  font-weight: 600;
  letter-spacing: 0.2px;

  &.mint  { background: var(--pos-mint-bg);  color: var(--pos-mint); }
  &.teal  { background: var(--pos-teal-bg);  color: var(--pos-teal); }
  &.amber { background: var(--pos-amber-bg); color: var(--pos-amber); }
  &.grey  { background: #f0f0ee; color: var(--pos-ink-3); }
}

.pos-stock__dot {
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background: currentColor;
  flex-shrink: 0;
}

.pos-cart-qty {
  position: absolute;
  top: 8px;
  right: 8px;
  background: var(--pos-accent);
  color: #fff;
  font-size: 11px;
  font-weight: 700;
  padding: 3px 7px;
  border-radius: 999px;
}

.pos-card__body {
  padding: 10px 12px 12px;
  display: flex;
  flex-direction: column;
  gap: 7px;
  flex: 1;
}

.pos-card__name {
  font-size: 13.5px;
  font-weight: 600;
  color: var(--pos-ink);
  line-height: 1.3;
  min-height: 36px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.pos-card__meta-row {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}

.pos-code {
  font-size: 10.5px;
  color: var(--pos-ink-3);
  font-family: 'JetBrains Mono', monospace;
  font-weight: 500;
}

.pos-pres {
  font-size: 10.5px;
  color: var(--pos-ink-2);
  background: var(--pos-surface-2);
  border: 1px solid var(--pos-border);
  padding: 1px 6px;
  border-radius: 4px;
}

.pos-card__lab {
  font-size: 11.5px;
  color: var(--pos-ink-3);
  display: flex;
  align-items: center;
  gap: 4px;
}

.pos-card__cats {
  display: flex;
  gap: 4px;
  flex-wrap: wrap;
}

.pos-cat {
  font-size: 10.5px;
  color: oklch(0.40 0.12 200);
  background: var(--pos-teal-bg);
  padding: 2px 7px;
  border-radius: 4px;
  font-weight: 500;
}

.pos-card__divider {
  height: 1px;
  background: repeating-linear-gradient(
    90deg,
    var(--pos-border) 0, var(--pos-border) 4px,
    transparent 4px, transparent 8px
  );
  margin-top: auto;
}

.pos-card__footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 10px;
}

.pos-price-block {
  display: flex;
  flex-direction: column;
  line-height: 1.1;
}

.pos-unit {
  font-size: 10px;
  font-weight: 500;
  color: var(--pos-ink-3);
  letter-spacing: 0.4px;
}

.pos-price {
  font-size: 16px;
  font-weight: 700;
  color: var(--pos-ink);
  font-variant-numeric: tabular-nums;
}

.pos-card__btns {
  display: flex;
  gap: 4px;
}

.pos-btn-eye {
  width: 30px;
  height: 30px;
  border-radius: 8px;
  border: 1px solid var(--pos-border);
  background: transparent;
  color: var(--pos-ink-3);
  display: grid;
  place-items: center;
  cursor: pointer;
  transition: all .12s;

  &:hover { background: var(--pos-surface-2); border-color: var(--pos-border-s); }
}

.pos-btn-add {
  width: 30px;
  height: 30px;
  border-radius: 8px;
  background: var(--pos-accent);
  color: #fff;
  border: none;
  display: grid;
  place-items: center;
  cursor: pointer;
  box-shadow: 0 1px 2px rgba(0,0,0,.08);
  transition: all .12s;

  &:hover { filter: brightness(1.05); }
  &:active { transform: scale(0.94); }
  &:disabled { background: var(--pos-border-s); cursor: not-allowed; }
}

/* ── No data ── */
.pos-no-data {
  padding: 60px 20px;
  text-align: center;
  color: var(--pos-ink-3);
  width: 100%;
}
.pos-no-data__title { font-size: 14px; font-weight: 600; color: var(--pos-ink-2); margin-top: 8px; }
.pos-no-data__sub   { font-size: 12px; margin-top: 4px; }

/* ── Cart Panel ── */
.pos-cart {
  width: 340px;
  flex-shrink: 0;
  background: var(--pos-surface);
  border-left: 1px solid var(--pos-border);
  display: flex;
  flex-direction: column;
  height: 100%;
}

.pos-cart__head {
  padding: 14px 18px;
  border-bottom: 1px solid var(--pos-border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-shrink: 0;
}

.pos-cart__title {
  font-size: 14px;
  font-weight: 700;
  color: var(--pos-ink);
}

.pos-cart__ticket {
  font-size: 11px;
  color: var(--pos-ink-3);
  margin-top: 2px;
}

.pos-items-badge {
  padding: 4px 10px;
  border-radius: 999px;
  background: var(--pos-mint-bg);
  color: var(--pos-mint);
  font-size: 11px;
  font-weight: 600;
}

.pos-cart__client {
  padding: 12px 18px;
  border-bottom: 1px solid var(--pos-border);
  flex-shrink: 0;
}

.pos-client-row {
  display: flex;
  align-items: center;
  gap: 8px;
}

.pos-avatar {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: var(--pos-accent-bg);
  color: var(--pos-accent-ink);
  display: grid;
  place-items: center;
  font-size: 12px;
  font-weight: 700;
  flex-shrink: 0;
}

.pos-client-info { flex: 1; }

.pos-client-name {
  font-size: 13px;
  font-weight: 600;
  color: var(--pos-ink);
}

.pos-client-sub {
  font-size: 11px;
  color: var(--pos-ink-3);
}

.pos-edit-btn {
  padding: 5px 10px;
  font-size: 11px;
  font-weight: 600;
  border: 1px solid var(--pos-border);
  border-radius: 6px;
  color: var(--pos-ink-2);
  background: transparent;
  cursor: pointer;
  font-family: inherit;
  transition: all .12s;

  &:hover { border-color: var(--pos-border-s); background: var(--pos-surface-2); }
}

.pos-cart__scroll {
  flex: 1;
  min-height: 0;
  height: 100%;
}

.pos-cart__empty {
  padding: 40px 20px;
  text-align: center;
}

.pos-cart-item {
  display: flex;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 8px;
  position: relative;
  transition: background .1s;
  margin: 2px 6px;

  &:hover { background: var(--pos-surface-2); }
}

.pos-cart-item__thumb {
  width: 40px;
  height: 40px;
  border-radius: 6px;
  background: var(--pos-surface-2);
  border: 1px solid var(--pos-border);
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

.pos-cart-item__body { flex: 1; min-width: 0; }

.pos-cart-item__name {
  font-size: 12.5px;
  font-weight: 600;
  color: var(--pos-ink);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.pos-cart-item__sub {
  font-size: 10.5px;
  color: var(--pos-ink-3);
  margin-top: 1px;
  display: flex;
  gap: 5px;
}

.pos-cart-item__controls {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 6px;
}

.pos-qty {
  display: inline-flex;
  align-items: center;
  border: 1px solid var(--pos-border);
  border-radius: 6px;
  overflow: hidden;

  button {
    width: 22px;
    height: 22px;
    display: grid;
    place-items: center;
    background: transparent;
    border: none;
    color: var(--pos-ink-2);
    cursor: pointer;
    transition: background .1s;

    &:hover { background: var(--pos-surface-2); }
    &:disabled { opacity: 0.4; cursor: not-allowed; }
  }

  span {
    width: 26px;
    text-align: center;
    font-size: 12px;
    font-weight: 600;
    color: var(--pos-ink);
    font-variant-numeric: tabular-nums;
  }
}

.pos-cart-item__total {
  font-size: 13px;
  font-weight: 700;
  color: var(--pos-ink);
  font-variant-numeric: tabular-nums;
}

.pos-cart-item__del {
  position: absolute;
  top: 8px;
  right: 8px;
  color: var(--pos-ink-4);
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 3px;
  border-radius: 4px;
  display: grid;
  place-items: center;

  &:hover { color: var(--pos-ink-2); background: var(--pos-surface-2); }
}

/* ── Cart Footer ── */
.pos-cart__foot {
  border-top: 1px solid var(--pos-border);
  padding: 14px 18px;
  background: var(--pos-surface-2);
  flex-shrink: 0;
}

.pos-subtotals { margin-bottom: 8px; }

.pos-subtotal-row {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  color: var(--pos-ink-2);
  padding: 2px 0;
}

.pos-grand-row {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  border-top: 1px solid var(--pos-border);
  padding-top: 8px;
  margin-bottom: 12px;
  font-size: 13px;
  font-weight: 600;
  color: var(--pos-ink);
}

.pos-grand-amount {
  font-size: 22px;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
}

.pos-pay-row {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 6px;
  margin-bottom: 10px;
}

.pos-pay-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  padding: 8px 4px;
  border-radius: 8px;
  border: 1px solid var(--pos-border);
  background: var(--pos-surface);
  color: var(--pos-ink-2);
  font-size: 11px;
  font-weight: 500;
  cursor: pointer;
  transition: all .12s;
  font-family: inherit;

  &:hover {
    border-color: var(--pos-border-s);
    color: var(--pos-ink);
    background: var(--pos-surface-2);
  }
}

.pos-checkout {
  width: 100%;
  padding: 12px 14px;
  border-radius: 10px;
  background: var(--pos-ink);
  color: #fff;
  font-weight: 600;
  font-size: 13.5px;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all .15s;
  font-family: inherit;

  &:hover:not(:disabled) { background: #2a2a2e; }
  &:disabled { background: var(--pos-border-s); cursor: not-allowed; }
}
</style>
