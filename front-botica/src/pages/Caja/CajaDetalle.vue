<template>
  <q-page class="q-pa-md">

    <!-- Header -->
    <div class="row items-center q-mb-md">
      <q-btn flat round dense icon="arrow_back" color="grey-7" class="q-mr-sm"
        @click="$router.push({ name: 'CajaList' })" />
      <div>
        <div class="text-h5 text-weight-bold">DETALLE DE CAJA</div>
        <div class="text-caption text-grey-5" v-if="caja">
          {{ formatFecha(caja.fecha) }} — Turno {{ caja.turno }}
        </div>
      </div>
      <q-space />
      <q-btn flat round dense icon="refresh" color="grey-5" :loading="loading" @click="cargar" />
    </div>

    <q-inner-loading :showing="loading">
      <q-spinner-pie size="80px" color="primary" />
    </q-inner-loading>

    <template v-if="caja && !loading">

      <!-- Stat cards -->
      <div class="row q-col-gutter-sm q-mb-md">
        <div class="col-6 col-sm-3">
          <q-card flat class="stat-card">
            <q-card-section class="q-pa-md">
              <div class="row items-center no-wrap">
                <div class="col">
                  <div class="stat-label">Total recaudado</div>
                  <div class="stat-value text-primary">S/. {{ Number(caja.ventas_sum_total ?? 0).toFixed(2) }}</div>
                </div>
                <div class="stat-icon bg-primary"><q-icon name="bi-graph-up-arrow" size="20px" color="white" /></div>
              </div>
            </q-card-section>
          </q-card>
        </div>
        <div class="col-6 col-sm-3">
          <q-card flat class="stat-card">
            <q-card-section class="q-pa-md">
              <div class="row items-center no-wrap">
                <div class="col">
                  <div class="stat-label">Ventas</div>
                  <div class="stat-value">{{ caja.ventas_count ?? 0 }}</div>
                </div>
                <div class="stat-icon bg-teal"><q-icon name="bi-receipt" size="20px" color="white" /></div>
              </div>
            </q-card-section>
          </q-card>
        </div>
        <div class="col-6 col-sm-3">
          <q-card flat class="stat-card">
            <q-card-section class="q-pa-md">
              <div class="row items-center no-wrap">
                <div class="col">
                  <div class="stat-label">M. Inicial</div>
                  <div class="stat-value">S/. {{ Number(caja.monto_inicial ?? 0).toFixed(2) }}</div>
                </div>
                <div class="stat-icon bg-orange"><q-icon name="payments" size="20px" color="white" /></div>
              </div>
            </q-card-section>
          </q-card>
        </div>
        <div class="col-6 col-sm-3">
          <q-card flat class="stat-card">
            <q-card-section class="q-pa-md">
              <div class="row items-center no-wrap">
                <div class="col">
                  <div class="stat-label">M. Final</div>
                  <div class="stat-value">
                    {{ caja.monto_final != null ? `S/. ${Number(caja.monto_final).toFixed(2)}` : '—' }}
                  </div>
                </div>
                <div class="stat-icon" :class="caja.estado === 'cerrada' ? 'bg-positive' : 'bg-grey-5'">
                  <q-icon :name="caja.estado === 'cerrada' ? 'bi-lock-fill' : 'bi-unlock-fill'" size="20px" color="white" />
                </div>
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>

      <!-- Info caja + breakdown -->
      <div class="row q-col-gutter-md q-mb-md">

        <!-- Info general -->
        <div class="col-12 col-md-5">
          <q-card flat bordered>
            <q-card-section class="q-pb-xs">
              <div class="text-overline text-grey-6">INFORMACIÓN DE CAJA</div>
            </q-card-section>
            <q-card-section class="q-pt-xs q-gutter-xs">
              <div class="detail-row">
                <span class="detail-label"><q-icon name="bi-calendar3" class="q-mr-xs" />Fecha</span>
                <span>{{ formatFecha(caja.fecha) }}</span>
              </div>
              <q-separator />
              <div class="detail-row">
                <span class="detail-label"><q-icon name="bi-clock-history" class="q-mr-xs" />Turno</span>
                <q-chip dense size="sm"
                  :color="caja.turno === 'mañana' ? 'orange-1' : 'blue-1'"
                  :text-color="caja.turno === 'mañana' ? 'orange-9' : 'blue-9'"
                  style="padding:10px">{{ caja.turno }}</q-chip>
              </div>
              <q-separator />
              <div class="detail-row">
                <span class="detail-label"><q-icon name="bi-door-open" class="q-mr-xs" />Apertura</span>
                <span>{{ formatHora(caja.hora_apertura) }} — {{ caja.usuario_apertura?.name ?? '—' }}</span>
              </div>
              <q-separator v-if="caja.hora_cierre" />
              <div class="detail-row" v-if="caja.hora_cierre">
                <span class="detail-label"><q-icon name="bi-door-closed" class="q-mr-xs" />Cierre</span>
                <span>{{ formatHora(caja.hora_cierre) }} — {{ caja.usuario_cierre?.name ?? '—' }}</span>
              </div>
              <q-separator v-if="caja.observaciones" />
              <div class="detail-row" v-if="caja.observaciones">
                <span class="detail-label"><q-icon name="bi-chat-text" class="q-mr-xs" />Obs.</span>
                <span class="text-caption">{{ caja.observaciones }}</span>
              </div>
            </q-card-section>
          </q-card>
        </div>

        <!-- Breakdown por método de pago -->
        <div class="col-12 col-md-7">
          <q-card flat bordered>
            <q-card-section class="q-pb-xs">
              <div class="text-overline text-grey-6">RESUMEN POR MÉTODO DE PAGO</div>
            </q-card-section>
            <q-card-section class="q-pt-xs">
              <div v-if="breakdown.length === 0" class="text-grey-5 text-caption">Sin ventas registradas</div>
              <div v-for="item in breakdown" :key="item.metodo_pago" class="q-mb-sm">
                <div class="row items-center q-mb-xs">
                  <q-icon :name="iconMetodo(item.metodo_pago)" class="q-mr-sm" :color="colorMetodo(item.metodo_pago)" />
                  <span class="text-weight-medium text-capitalize">{{ item.metodo_pago }}</span>
                  <q-space />
                  <span class="text-caption text-grey-6 q-mr-sm">{{ item.cantidad }} venta{{ item.cantidad != 1 ? 's' : '' }}</span>
                  <span class="text-weight-bold text-primary">S/. {{ Number(item.total).toFixed(2) }}</span>
                </div>
                <q-linear-progress
                  :value="Number(item.total) / Number(caja.ventas_sum_total || 1)"
                  :color="colorMetodo(item.metodo_pago)"
                  rounded size="6px" />
              </div>
            </q-card-section>
          </q-card>
        </div>

      </div>

      <!-- Tabla de ventas -->
      <q-card flat bordered>
        <q-card-section class="q-pb-xs row items-center">
          <div class="text-overline text-grey-6">VENTAS DE LA CAJA</div>
          <q-space />
          <q-input v-model="searchVenta" dense standout="bg-primary text-white"
            placeholder="Buscar..." debounce="300" style="max-width:220px">
            <template v-slot:prepend><q-icon name="search" color="white" /></template>
            <template v-slot:append>
              <q-icon v-if="searchVenta" name="close" class="cursor-pointer" @click="searchVenta=''" />
            </template>
          </q-input>
        </q-card-section>

        <q-table
          flat
          :rows="ventasFiltradas"
          :columns="colVentas"
          row-key="id"
          :rows-per-page-options="[10, 20, 0]"
          :filter="searchVenta"
        >
          <!-- Comprobante -->
          <template v-slot:body-cell-tipo_comprobante="props">
            <q-td :props="props">
              <q-badge outline :color="colorComprobante(props.row.tipo_comprobante)"
                :label="labelComprobante(props.row.tipo_comprobante)" />
            </q-td>
          </template>

          <!-- Método pago -->
          <template v-slot:body-cell-metodo_pago="props">
            <q-td :props="props">
              <q-chip dense size="sm" :color="colorMetodo(props.row.metodo_pago) + '-1'"
                :text-color="colorMetodo(props.row.metodo_pago) + '-9'"
                :icon="iconMetodo(props.row.metodo_pago)" style="padding:8px">
                {{ props.row.metodo_pago }}
              </q-chip>
            </q-td>
          </template>

          <!-- Total -->
          <template v-slot:body-cell-total="props">
            <q-td :props="props">
              <span class="text-weight-bold text-primary">S/. {{ Number(props.row.total).toFixed(2) }}</span>
            </q-td>
          </template>

          <!-- Acciones: expandir items + reimprimir -->
          <template v-slot:body-cell-acciones="props">
            <q-td :props="props">
              <div class="row q-gutter-xs justify-end">
                <q-btn flat round dense size="sm" color="grey-6"
                  :icon="expandidos.includes(props.row.id) ? 'expand_less' : 'expand_more'"
                  @click="toggleExpand(props.row.id)">
                  <q-tooltip>Ver productos</q-tooltip>
                </q-btn>
                <q-btn flat round dense size="sm" color="primary" icon="bi-printer"
                  @click="reimprimir(props.row)">
                  <q-tooltip>Reimprimir boleta</q-tooltip>
                </q-btn>
              </div>
            </q-td>
          </template>

          <!-- Fila expandida con items -->
          <template v-slot:body="props">
            <q-tr :props="props">
              <q-td v-for="col in props.cols" :key="col.name" :props="props">
                <template v-if="col.name === 'tipo_comprobante'">
                  <q-badge outline :color="colorComprobante(props.row.tipo_comprobante)"
                    :label="labelComprobante(props.row.tipo_comprobante)" />
                </template>
                <template v-else-if="col.name === 'metodo_pago'">
                  <q-chip dense size="sm" :color="colorMetodo(props.row.metodo_pago) + '-1'"
                    :text-color="colorMetodo(props.row.metodo_pago) + '-9'"
                    :icon="iconMetodo(props.row.metodo_pago)" style="padding:8px">
                    {{ props.row.metodo_pago }}
                  </q-chip>
                </template>
                <template v-else-if="col.name === 'total'">
                  <span class="text-weight-bold text-primary">S/. {{ Number(props.row.total).toFixed(2) }}</span>
                </template>
                <template v-else-if="col.name === 'acciones'">
                  <div class="row q-gutter-xs justify-end">
                    <q-btn flat round dense size="sm" color="grey-6"
                      :icon="expandidos.includes(props.row.id) ? 'expand_less' : 'expand_more'"
                      @click="toggleExpand(props.row.id)">
                      <q-tooltip>Ver productos</q-tooltip>
                    </q-btn>
                    <q-btn flat round dense size="sm" color="primary" icon="bi-printer"
                      @click="reimprimir(props.row)">
                      <q-tooltip>Reimprimir boleta</q-tooltip>
                    </q-btn>
                  </div>
                </template>
                <template v-else>{{ col.value }}</template>
              </q-td>
            </q-tr>

            <!-- Items expandidos -->
            <q-tr v-if="expandidos.includes(props.row.id)" :props="props" class="bg-blue">
              <q-td colspan="100%" class="q-pa-sm">
                <q-table
                  flat dense
                  :rows="props.row.items || []"
                  :columns="colItems"
                  row-key="id"
                  hide-pagination
                  :rows-per-page-options="[0]"
                  class="bg-blue text-white"
                >
                  <template v-slot:body-cell-subtotal="itemProps">
                    <q-td :props="itemProps">
                      <span class="text-weight-bold">S/. {{ Number(itemProps.row.subtotal).toFixed(2) }}</span>
                    </q-td>
                  </template>
                  <template v-slot:body-cell-tipo_venta="itemProps">
                    <q-td :props="itemProps">
                      <q-chip dense size="xs"
                        :color="itemProps.row.tipo_venta === 'blister' ? 'orange-1' : 'blue-1'"
                        :text-color="itemProps.row.tipo_venta === 'blister' ? 'orange-9' : 'blue-9'"
                        style="padding:6px">
                        {{ itemProps.row.tipo_venta }}
                      </q-chip>
                    </q-td>
                  </template>
                </q-table>
              </q-td>
            </q-tr>
          </template>

          <template v-slot:no-data>
            <div class="full-width column flex-center q-pa-xl text-grey-5">
              <q-icon name="bi-receipt-cutoff" size="40px" class="q-mb-sm" />
              <div>Sin ventas en esta caja</div>
            </div>
          </template>
        </q-table>
      </q-card>

    </template>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { date } from 'quasar'
import CajaService from 'src/services/CajaService'
import { abrirBoletaPDF } from 'src/pages/Ventas/boletaPDF'

const route    = useRoute()
const loading  = ref(false)
const caja     = ref(null)
const breakdown = ref([])
const expandidos = ref([])
const searchVenta = ref('')

const colVentas = [
  { name: 'id',               label: '#',          field: 'id',               align: 'left',   sortable: true },
  { name: 'created_at',       label: 'Hora',       field: 'created_at',       align: 'center',
    format: v => v ? date.formatDate(new Date(v), 'HH:mm') : '—' },
  { name: 'cliente_nombre',   label: 'Cliente',    field: 'cliente_nombre',   align: 'left',   sortable: true },
  { name: 'tipo_comprobante', label: 'Comprobante',field: 'tipo_comprobante', align: 'center' },
  { name: 'metodo_pago',      label: 'Pago',       field: 'metodo_pago',      align: 'center' },
  { name: 'cajero',           label: 'Cajero',     field: r => r.usuario?.name ?? '—', align: 'left' },
  { name: 'total',            label: 'Total',      field: 'total',            align: 'right',  sortable: true },
  { name: 'acciones',         label: '',           field: 'id',               align: 'right' },
]

const colItems = [
  { name: 'codigo',      label: 'Código',    field: r => r.inventario?.codigo ?? '—', align: 'left' },
  { name: 'nombre',      label: 'Producto',  field: r => r.inventario?.nombre ?? '—', align: 'left' },
  { name: 'tipo_venta',  label: 'Tipo',      field: 'tipo_venta',  align: 'center' },
  { name: 'cantidad',    label: 'Cant.',     field: 'cantidad',    align: 'center' },
  { name: 'precio_venta',label: 'P. Unit.',  field: 'precio_venta', align: 'right',
    format: v => `S/. ${Number(v).toFixed(2)}` },
  { name: 'subtotal',    label: 'Subtotal',  field: 'subtotal',    align: 'right' },
]

const ventasFiltradas = computed(() => {
  if (!caja.value?.ventas) return []
  const q = searchVenta.value.toLowerCase()
  if (!q) return caja.value.ventas
  return caja.value.ventas.filter(v =>
    v.cliente_nombre?.toLowerCase().includes(q) ||
    v.cliente_dni?.includes(q) ||
    String(v.id).includes(q)
  )
})

function toggleExpand(id) {
  const idx = expandidos.value.indexOf(id)
  if (idx >= 0) expandidos.value.splice(idx, 1)
  else expandidos.value.push(id)
}

function formatFecha(f) { return f ? date.formatDate(f, 'DD/MM/YYYY') : '—' }
function formatHora(h)  { return h ? date.formatDate(new Date(h), 'HH:mm') : '—' }

function colorComprobante(tipo) {
  return { boleta: 'primary', factura: 'deep-orange', sin_comprobante: 'grey-6' }[tipo] ?? 'grey'
}
function labelComprobante(tipo) {
  return { boleta: 'Boleta', factura: 'Factura', sin_comprobante: 'S/C' }[tipo] ?? tipo
}
function colorMetodo(metodo) {
  return { efectivo: 'green', tarjeta: 'blue', transferencia: 'purple' }[metodo] ?? 'grey'
}
function iconMetodo(metodo) {
  return { efectivo: 'payments', tarjeta: 'credit_card', transferencia: 'swap_horiz' }[metodo] ?? 'payments'
}

async function cargar() {
  loading.value = true
  try {
    const res = await CajaService.getDetalle(route.params.id)
    caja.value      = res.caja
    breakdown.value = res.breakdown || []
  } finally {
    loading.value = false
  }
}

function reimprimir(venta) {
  const ventaConCaja = { ...venta, caja: caja.value }
  abrirBoletaPDF(ventaConCaja)
}

onMounted(cargar)
</script>

<style scoped>
.stat-card {
  border-radius: 10px !important;
}
.stat-label {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  opacity: 0.65;
  margin-bottom: 4px;
}
.stat-value {
  font-size: 20px;
  font-weight: 800;
  line-height: 1.1;
}
.stat-icon {
  width: 40px; height: 40px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; margin-left: 10px;
}
.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 5px 0;
  font-size: 13px;
}
.detail-label {
  color: var(--q-grey-6);
  display: flex;
  align-items: center;
}
</style>
