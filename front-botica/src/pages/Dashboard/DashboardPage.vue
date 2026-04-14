<template>
  <q-page class="dashboard-page q-pa-md">

    <!-- Header -->
    <div class="row items-center justify-between q-mb-lg">
      <div>
        <div class="text-h5 text-weight-bold">Dashboard</div>
        <div class="text-caption">
          Resumen del inventario · {{ fechaHoy }}
        </div>
      </div>
      <q-btn
        flat round dense icon="refresh" color="grey-6"
        :loading="loading" @click="cargar"
      >
        <q-tooltip>Actualizar</q-tooltip>
      </q-btn>
    </div>

    <!-- Loading skeleton -->
    <template v-if="loading">
      <div class="row q-col-gutter-md q-mb-lg">
        <div class="col-6 col-sm-3" v-for="i in 4" :key="i">
          <q-skeleton height="100px" class="rounded-borders" />
        </div>
      </div>
      <div class="row q-col-gutter-md">
        <div class="col-12 col-md-5">
          <q-skeleton height="320px" class="rounded-borders" />
        </div>
        <div class="col-12 col-md-7">
          <q-skeleton height="320px" class="rounded-borders" />
        </div>
      </div>
    </template>

    <template v-else>

      <!-- ── STAT CARDS ──────────────────────────────── -->
      <div class="row q-col-gutter-md q-mb-lg">

        <div class="col-6 col-sm-3">
          <q-card flat class="stat-card">
            <q-card-section class="q-pa-md">
              <div class="row items-center no-wrap">
                <div class="col">
                  <div class="stat-label">TOTAL PRODUCTOS</div>
                  <div class="stat-num">{{ stats.total }}</div>
                </div>
                <q-icon name="inventory_2" size="36px" color="primary" class="stat-icon" />
              </div>
              <div class="text-caption q-mt-xs">en inventario</div>
            </q-card-section>
          </q-card>
        </div>

        <div class="col-6 col-sm-3">
          <q-card flat class="stat-card">
            <q-card-section class="q-pa-md">
              <div class="row items-center no-wrap">
                <div class="col">
                  <div class="stat-label">SIN STOCK</div>
                  <div class="stat-num text-grey-6">{{ stats.sinStock }}</div>
                </div>
                <q-icon name="remove_shopping_cart" size="36px" class="stat-icon" />
              </div>
              <div class="text-caption q-mt-xs">productos agotados</div>
            </q-card-section>
          </q-card>
        </div>

        <div class="col-6 col-sm-3">
          <q-card flat class="stat-card">
            <q-card-section class="q-pa-md">
              <div class="row items-center no-wrap">
                <div class="col">
                  <div class="stat-label">STOCK BAJO</div>
                  <div class="stat-num text-negative">{{ stats.stockBajo }}</div>
                </div>
                <q-icon name="warning_amber" size="36px" color="negative" class="stat-icon" />
              </div>
              <div class="text-caption text-grey-5 q-mt-xs">requieren reposición</div>
            </q-card-section>
          </q-card>
        </div>

        <div class="col-6 col-sm-3">
          <q-card flat class="stat-card">
            <q-card-section class="q-pa-md">
              <div class="row items-center no-wrap">
                <div class="col">
                  <div class="stat-label">VENCIDOS</div>
                  <div class="stat-num text-negative">{{ stats.vencidos }}</div>
                </div>
                <q-icon name="event_busy" size="36px" color="negative" class="stat-icon" />
              </div>
              <div class="text-caption text-grey-5 q-mt-xs">fuera de fecha</div>
            </q-card-section>
          </q-card>
        </div>

      </div>

      <!-- ── SEGUNDA FILA: Donut + Bar ───────────────── -->
      <div class="row q-col-gutter-md q-mb-lg">

        <!-- Donut: Categorías -->
        <div class="col-12 col-md-5">
          <q-card flat class="chart-card full-height">
            <q-card-section>
              <div class="chart-title">Productos por categoría</div>
              <div class="text-caption">Distribución del inventario</div>
            </q-card-section>
            <q-card-section class="q-pt-none">
              <apexchart
                v-if="donutSeries.length"
                type="donut"
                height="280"
                :options="donutOptions"
                :series="donutSeries"
              />
              <div v-else class="flex flex-center  q-py-xl">
                <div class="text-center">
                  <q-icon name="donut_large" size="48px" />
                  <div class="text-caption q-mt-sm">Sin categorías</div>
                </div>
              </div>
            </q-card-section>
          </q-card>
        </div>

        <!-- Bar horizontal: Top productos por stock -->
        <div class="col-12 col-md-7">
          <q-card flat class="chart-card full-height">
            <q-card-section>
              <div class="chart-title">Top 10 productos con más stock</div>
              <div class="text-caption">Unidades disponibles</div>
            </q-card-section>
            <q-card-section class="q-pt-none">
              <apexchart
                v-if="barSeries[0]?.data?.length"
                type="bar"
                height="280"
                :options="barOptions"
                :series="barSeries"
              />
              <div v-else class="flex flex-center  q-py-xl">
                <q-icon name="bar_chart" size="48px" />
              </div>
            </q-card-section>
          </q-card>
        </div>

      </div>

      <!-- ── TERCERA FILA: Estado + Alertas ───────────── -->
      <div class="row q-col-gutter-md">

        <!-- Radial: Estado general -->
        <div class="col-12 col-md-4">
          <q-card flat class="chart-card full-height">
            <q-card-section>
              <div class="chart-title">Estado del inventario</div>
              <div class="text-caption">% sobre total de productos</div>
            </q-card-section>
            <q-card-section class="q-pt-none">
              <apexchart
                type="radialBar"
                height="280"
                :options="radialOptions"
                :series="radialSeries"
              />
            </q-card-section>
          </q-card>
        </div>

        <!-- Lista: Alertas -->
        <div class="col-12 col-md-8">
          <q-card flat class="chart-card full-height">
            <q-card-section>
              <div class="chart-title">Alertas de inventario</div>
              <div class="text-caption">
                Productos que requieren atención ({{ alertas.length }})
              </div>
            </q-card-section>
            <q-card-section class="q-pt-none" style="max-height:260px;overflow-y:auto">
              <div v-if="!alertas.length" class="flex flex-center  q-py-xl">
                <div class="text-center">
                  <q-icon name="check_circle" size="48px" color="positive" />
                  <div class="text-caption q-mt-sm text-positive">Sin alertas</div>
                </div>
              </div>
              <q-list v-else separator dense>
                <q-item v-for="item in alertas" :key="item.id" class="q-px-none">
                  <q-item-section avatar>
                    <q-avatar
                      :color="item.tipo === 'vencido' ? 'negative' : item.tipo === 'proximo' ? 'warning' : 'negative'"
                      text-color="white"
                      size="32px"
                      font-size="14px"
                    >
                      <q-icon :name="item.tipo === 'vencido' ? 'event_busy' : item.tipo === 'proximo' ? 'schedule' : 'warning'" size="16px" />
                    </q-avatar>
                  </q-item-section>
                  <q-item-section>
                    <q-item-label class="text-weight-medium" style="font-size:13px">
                      {{ item.nombre }}
                    </q-item-label>
                    <q-item-label caption>
                      <span v-if="item.tipo === 'vencido'" class="text-negative">Vencido: {{ item.fecha_vencimiento }}</span>
                      <span v-else-if="item.tipo === 'proximo'" class="text-warning">Vence: {{ item.fecha_vencimiento }}</span>
                      <span v-else class="text-negative">Stock: {{ item.cantidad }} / mín {{ item.stock_minimo }}</span>
                    </q-item-label>
                  </q-item-section>
                  <q-item-section side>
                    <q-chip
                      dense size="xs"
                      :color="item.tipo === 'vencido' ? 'negative' : item.tipo === 'proximo' ? 'warning' : 'negative'"
                      text-color="white"
                      class="q-ma-none" style="padding: 10px;"
                    >
                      {{ item.tipo === 'vencido' ? 'Vencido' : item.tipo === 'proximo' ? 'Próx. vencer' : 'Stock bajo' }}
                    </q-chip>
                  </q-item-section>
                </q-item>
              </q-list>
            </q-card-section>
          </q-card>
        </div>

      </div>

    </template>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import InventarioService from 'src/services/InventarioService'

const loading    = ref(true)
const inventarios = ref([])

const fechaHoy = new Date().toLocaleDateString('es-PE', {
  weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
})

// ── Carga de datos ─────────────────────────────────────
async function cargar() {
  loading.value = true
  try {
    const { data } = await InventarioService.getData({ params: { rowsPerPage: 0 } })
    inventarios.value = data
  } finally {
    loading.value = false
  }
}

onMounted(cargar)

// ── Helpers ────────────────────────────────────────────
function diasParaVencer(fecha) {
  if (!fecha) return null
  return (new Date(fecha) - new Date()) / (1000 * 60 * 60 * 24)
}

// ── Stats cards ────────────────────────────────────────
const stats = computed(() => {
  const all = inventarios.value
  return {
    total:    all.length,
    sinStock: all.filter(p => p.cantidad === 0).length,
    stockBajo: all.filter(p =>
      p.stock_minimo != null && p.cantidad > 0 && p.cantidad <= p.stock_minimo
    ).length,
    vencidos: all.filter(p => {
      const d = diasParaVencer(p.fecha_vencimiento)
      return d !== null && d < 0
    }).length,
  }
})

// ── Alertas list ───────────────────────────────────────
const alertas = computed(() => {
  const result = []
  for (const p of inventarios.value) {
    const d = diasParaVencer(p.fecha_vencimiento)
    if (d !== null && d < 0) {
      result.push({ ...p, tipo: 'vencido' })
    } else if (d !== null && d >= 0 && d <= 30) {
      result.push({ ...p, tipo: 'proximo' })
    } else if (p.stock_minimo != null && p.cantidad > 0 && p.cantidad <= p.stock_minimo) {
      result.push({ ...p, tipo: 'stock_bajo' })
    }
  }
  return result.slice(0, 30)
})

// ── Donut: categorías ──────────────────────────────────
const donutData = computed(() => {
  const mapa = {}
  for (const p of inventarios.value) {
    for (const cat of (p.categorias ?? [])) {
      mapa[cat.nombre] = (mapa[cat.nombre] ?? 0) + 1
    }
  }
  const sorted = Object.entries(mapa).sort((a, b) => b[1] - a[1])
  const top = sorted.slice(0, 6)
  const otros = sorted.slice(6).reduce((acc, [, v]) => acc + v, 0)
  if (otros > 0) top.push(['Otros', otros])
  return { labels: top.map(([k]) => k), series: top.map(([, v]) => v) }
})

const donutSeries  = computed(() => donutData.value.series)
const donutOptions = computed(() => ({
  chart: { type: 'donut', fontFamily: 'Nunito, sans-serif', toolbar: { show: false } },
  labels: donutData.value.labels,
  // colors: ['#6366f1', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#3b82f6', '#94a3b8'],
  legend: { position: 'bottom', fontSize: '12px', offsetY: 4 },
  plotOptions: {
    pie: {
      donut: {
        size: '68%',
        labels: {
          show: true,
          total: {
            show: true,
            label: 'Total',
            fontSize: '13px',
            color: '#9e9e9e',
            formatter: () => inventarios.value.length
          }
        }
      }
    }
  },
  dataLabels: { enabled: false },
  stroke: { width: 2 },
  tooltip: { y: { formatter: v => v + ' productos' } },
}))

// ── Bar: top productos por stock ───────────────────────
const barData = computed(() => {
  const sorted = [...inventarios.value]
    .filter(p => p.cantidad > 0)
    .sort((a, b) => b.cantidad - a.cantidad)
    .slice(0, 10)
  return {
    names: sorted.map(p => p.nombre.length > 20 ? p.nombre.slice(0, 20) + '…' : p.nombre),
    data:  sorted.map(p => p.cantidad),
  }
})

const barSeries = computed(() => [{
  name: 'Stock',
  data: barData.value.data,
}])

const barOptions = computed(() => ({
  chart: {
    type: 'bar',
    fontFamily: 'Nunito, sans-serif',
    toolbar: { show: false },
    animations: { enabled: true },
  },
  plotOptions: {
    bar: {
      horizontal: true,
      borderRadius: 6,
      barHeight: '60%',
      distributed: true,
    }
  },
  // colors: ['#6366f1', '#7c73e6', '#8b5cf6', '#9d7cfa', '#a78bfa',
  //          '#b39dfa', '#c4b5fd', '#6366f1', '#818cf8', '#a5b4fc'],
  dataLabels: {
    enabled: true,
    // style: { fontSize: '11px', colors: ['#fff'] },
    formatter: v => v,
  },
  xaxis: {
    categories: barData.value.names,
    labels: { style: { fontSize: '11px' } },
  },
  yaxis: { labels: { style: { fontSize: '11px' }, maxWidth: 140 } },
  legend: { show: false },
  grid: { borderColor: '#f3f4f6', strokeDashArray: 4 },
  tooltip: { y: { formatter: v => v + ' unidades' } },
}))

// ── Radial: estado general ─────────────────────────────
const radialSeries = computed(() => {
  const t = stats.value.total || 1
  return [
    Math.round((stats.value.vencidos   / t) * 100),
    Math.round((stats.value.stockBajo  / t) * 100),
    Math.round((stats.value.sinStock   / t) * 100),
  ]
})

const radialOptions = {
  chart: { type: 'radialBar', fontFamily: 'Nunito, sans-serif', toolbar: { show: false } },
  // colors: ['#ef4444', '#f59e0b', '#94a3b8'],
  plotOptions: {
    radialBar: {
      offsetY: 0,
      startAngle: -135,
      endAngle: 135,
      hollow: { size: '30%' },
      track: { background: '#f3f4f6', strokeWidth: '90%' },
      dataLabels: {
        name: { fontSize: '12px', offsetY: -8 },
        value: { fontSize: '16px', fontWeight: 700, offsetY: 4, formatter: v => v + '%' },
        total: {
          show: true,
          label: 'Alertas',
          fontSize: '11px',
          color: '#9e9e9e',
          formatter: () => {
            const t = stats.value.total || 1
            const pct = Math.round(
              ((stats.value.vencidos + stats.value.stockBajo + stats.value.sinStock) / t) * 100
            )
            return pct + '%'
          }
        }
      }
    }
  },
  labels: ['Vencidos', 'Stock bajo', 'Sin stock'],
  legend: { show: true, position: 'bottom', fontSize: '12px' },
}
</script>

<style scoped>
.dashboard-page {
  min-height: 100vh;
}

/* Stat cards */
.stat-card {
  border-radius: 14px;
  transition: box-shadow 0.2s;
}
.stat-card:hover {
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
}

.stat-label {
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  margin-bottom: 4px;
}
.stat-num {
  font-size: 30px;
  font-weight: 800;
  line-height: 1;
}
.stat-icon {
  opacity: 0.15;
}

/* Chart cards */
.chart-card {
  border-radius: 14px;
}
.chart-title {
  font-size: 14px;
  font-weight: 700;
}
</style>
