<template>
  <q-card :style="$q.screen.lt.sm ? 'width:100vw;max-height:100vh' : 'width:700px;max-width:96vw'" class="detalle-card">

    <!-- Barra de cierre -->
    <div class="row justify-end q-px-sm q-pt-sm">
      <q-btn dense flat round icon="close" color="grey-6" v-close-popup />
    </div>

    <!-- Loading -->
    <div v-if="loading" class="column flex-center q-pa-xl" style="min-height:300px">
      <q-spinner-cube size="56px" color="primary" />
    </div>

    <q-scroll-area v-else-if="inv" style="height: calc(90vh - 40px); max-height: 680px">
      <div class="q-px-lg q-pb-xl">

        <!-- ── HEADER ───────────────────────────────── -->
        <div class="row items-start no-wrap q-mb-md">
          <!-- Avatar -->
          <div class="avatar-circle q-mr-md">
            <q-img
              v-if="firstImage"
              :src="firstImage"
              fit="cover"
              class="full-height full-width"
              style="border-radius: 50%"
            />
            <span v-else class="avatar-initials">{{ inv.nombre?.charAt(0)?.toUpperCase() }}</span>
          </div>

          <!-- Nombre + subtitulo -->
          <div class="col">
            <div class="text-h6 text-weight-bold text-dark">{{ inv.nombre }}</div>
            <div class="text-caption text-grey-6 q-mt-xs">
              Presentación: {{ inv.presentacion?.nombre ?? '—' }}
            </div>
          </div>

          <!-- Chips de estado -->
          <div class="column items-end q-gutter-xs">
            <q-chip
              v-if="sinStock"
              dense size="sm" outline
              color="grey-7" text-color="grey-7"
            >Sin stock</q-chip>
            <q-chip
              v-else-if="stockBajo"
              dense size="sm" outline
              color="negative" text-color="negative"
            >Stock bajo</q-chip>
            <q-chip
              v-if="vencido"
              dense size="sm" outline
              color="negative" text-color="negative"
            >Vencido</q-chip>
            <q-chip
              v-else-if="proxVencer"
              dense size="sm" outline
              color="warning" text-color="warning"
            >Próx. vencer</q-chip>
          </div>
        </div>

        <!-- ── META LINE ────────────────────────────── -->
        <div class="meta-line q-mb-lg">
          <span v-if="inv.codigo">Código <strong>{{ inv.codigo }}</strong></span>
          <span v-if="inv.codigo" class="sep">·</span>
          <span v-if="inv.lote">Lote <strong>{{ inv.lote }}</strong></span>
          <span v-if="inv.lote" class="sep">·</span>
          <span v-if="inv.registro_sanitario">Reg. <strong>{{ inv.registro_sanitario }}</strong></span>
          <span v-if="inv.registro_sanitario" class="sep">·</span>
          <span :class="vencido ? ' text-weight-bold' : proxVencer ? 'text-warning text-weight-bold' : ''">
            Vence {{ inv.fecha_vencimiento ?? '—' }}
          </span>
        </div>

        <!-- ── STATS ─────────────────────────────────── -->
        <div class="row q-col-gutter-sm q-mb-lg">
          <div class="col-6 col-sm-3">
            <q-card flat bordered class="stat-card">
              <q-card-section class="q-pa-md">
                <div class="stat-label">PRECIO OFICIAL</div>
                <div class="stat-value text-primary">S/. {{ inv.precio_oficial }}</div>
              </q-card-section>
            </q-card>
          </div>
          <div class="col-6 col-sm-3">
            <q-card flat bordered class="stat-card">
              <q-card-section class="q-pa-md">
                <div class="stat-label">STOCK</div>
                <div class="stat-value" :class="sinStock ? 'text-grey-6' : stockBajo ? '' : 'text-positive'">
                  {{ inv.cantidad }}
                </div>
              </q-card-section>
            </q-card>
          </div>
          <div class="col-6 col-sm-3">
            <q-card flat bordered class="stat-card">
              <q-card-section class="q-pa-md">
                <div class="stat-label">STOCK MÍNIMO</div>
                <div class="stat-value">{{ inv.stock_minimo ?? '—' }}</div>
              </q-card-section>
            </q-card>
          </div>
          <div class="col-6 col-sm-3">
            <q-card flat bordered class="stat-card">
              <q-card-section class="q-pa-md">
                <div class="stat-label">PRECIO UNIDAD</div>
                <div class="stat-value">
                  {{ inv.flag_unidad ? 'S/. ' + inv.precio_unidad : '—' }}
                </div>
              </q-card-section>
            </q-card>
          </div>
        </div>

        <!-- ── SECCIÓN: PRODUCTO ──────────────────────── -->
        <q-card flat bordered class="q-mb-md">
          <q-card-section>
            <div class="section-title">Producto</div>
            <div class="info-row">
              <span class="info-label">Nombre</span>
              <span class="info-value">{{ inv.nombre }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Código</span>
              <span class="info-value">{{ inv.codigo ?? '—' }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Presentación</span>
              <span class="info-value">{{ inv.presentacion?.nombre ?? '—' }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Registro sanitario</span>
              <span class="info-value">{{ inv.registro_sanitario ?? '—' }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Lote</span>
              <span class="info-value">{{ inv.lote ?? '—' }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Fecha vencimiento</span>
              <span class="info-value" :class="vencido ? ' text-weight-bold' : proxVencer ? 'text-warning text-weight-bold' : ''">
                {{ inv.fecha_vencimiento ?? '—' }}
              </span>
            </div>
          </q-card-section>
        </q-card>

        <!-- ── SECCIÓN: UBICACIÓN ─────────────────────── -->
        <q-card flat bordered class="q-mb-md">
          <q-card-section>
            <div class="section-title">Ubicación y área</div>
            <div class="info-row">
              <span class="info-label">Área</span>
              <span class="info-value">{{ inv.area?.nombre ?? '—' }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Ubicación</span>
              <span class="info-value">{{ inv.ubicacion ?? '—' }}</span>
            </div>
          </q-card-section>
        </q-card>

        <!-- ── SECCIÓN: PRECIOS ───────────────────────── -->
        <q-card flat bordered class="q-mb-md">
          <q-card-section>
            <div class="section-title">Precios</div>
            <div class="info-row">
              <span class="info-label">Precio oficial</span>
              <span class="info-value text-weight-bold">S/. {{ inv.precio_oficial }}</span>
            </div>
            <div class="info-row" v-if="inv.flag_blister">
              <span class="info-label">Precio blister</span>
              <span class="info-value">S/. {{ inv.precio_blister }} (× {{ inv.cantidad_blister }})</span>
            </div>
            <div class="info-row" v-if="inv.flag_unidad">
              <span class="info-label">Precio unidad</span>
              <span class="info-value">S/. {{ inv.precio_unidad }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Precio referencial</span>
              <span class="info-value">{{ inv.precio ? 'S/. ' + inv.precio : '—' }}</span>
            </div>
          </q-card-section>
        </q-card>

        <!-- ── SECCIÓN: PROVEEDOR Y LAB ──────────────── -->
        <q-card flat bordered class="q-mb-md">
          <q-card-section>
            <div class="section-title">Laboratorio y proveedor</div>
            <div class="info-row">
              <span class="info-label">Laboratorio</span>
              <span class="info-value">{{ inv.laboratorio?.nombre ?? '—' }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Proveedor</span>
              <span class="info-value">{{ inv.proveedor?.razon_social ?? '—' }}</span>
            </div>
          </q-card-section>
        </q-card>

        <!-- ── SECCIÓN: CATEGORÍAS ────────────────────── -->
        <q-card flat bordered class="q-mb-md" v-if="inv.categorias?.length">
          <q-card-section>
            <div class="section-title">Categorías</div>
            <div class="row q-gutter-xs q-pt-xs">
              <q-chip
                v-for="cat in inv.categorias" :key="cat.id"
                dense size="sm" color="primary" text-color="white" class="q-ma-none" style="padding: 10px;"
              >{{ cat.nombre }}</q-chip>
            </div>
          </q-card-section>
        </q-card>

        <!-- ── SECCIÓN: MALESTARES ───────────────────── -->
        <q-card flat bordered class="q-mb-md" v-if="inv.subcategorias?.length">
          <q-card-section>
            <div class="section-title">Malestares</div>
            <div class="row q-gutter-xs q-pt-xs">
              <q-chip
                v-for="sub in inv.subcategorias" :key="sub.id"
                dense size="sm" outline color="primary" text-color="primary" class="q-ma-none" style="padding: 10px;"
              >{{ sub.nombre }}</q-chip>
            </div>
          </q-card-section>
        </q-card>

        <!-- ── GALERÍA ────────────────────────────────── -->
        <q-card flat bordered v-if="inv.archivos?.length">
          <q-card-section>
            <div class="section-title">Fotografías</div>
            <div class="row q-gutter-sm q-pt-xs">
              <div
                v-for="(archivo, i) in inv.archivos" :key="archivo.id"
                class="foto-thumb cursor-pointer"
                @click="abrirFoto(i)"
              >
                <q-img
                  :src="`${API_URL}/storage/${archivo.url}`"
                  fit="cover"
                  style="width:80px;height:80px;border-radius:10px"
                >
                  <template v-slot:loading>
                    <q-spinner-dots color="primary" />
                  </template>
                  <template v-slot:error>
                    <div class="absolute-full flex flex-center bg-grey-3">
                      <q-icon name="broken_image" color="grey-5" size="32px" />
                    </div>
                  </template>
                </q-img>
                <div class="foto-overlay">
                  <q-icon name="zoom_in" color="white" size="20px" />
                </div>
              </div>
            </div>
          </q-card-section>
        </q-card>

        <!-- Acciones -->
        <div class="row justify-end q-gutter-sm q-mt-lg">
          <q-btn flat label="Cerrar" color="grey-7" v-close-popup />
          <q-btn unelevated color="primary" icon="edit" label="Editar"
            @click="$emit('editar', inv.id)" v-close-popup />
        </div>

      </div>
    </q-scroll-area>

  </q-card>

  <!-- ── LIGHTBOX (fuera del q-card principal) ── -->
  <q-dialog v-model="lightbox" maximized transition-show="fade" transition-hide="fade">
    <div
      class="full-height full-width bg-black column flex-center"
      style="position:relative"
    >
      <!-- Botón cerrar -->
      <q-btn
        round flat
        icon="close"
        color="white"
        size="md"
        v-close-popup
        style="position:absolute;top:12px;right:12px;z-index:10;background:rgba(0,0,0,0.4)"
      />

      <!-- Contador -->
      <div
        v-if="inv?.archivos?.length > 1"
        class="text-white text-caption"
        style="position:absolute;top:16px;left:50%;transform:translateX(-50%);z-index:10;background:rgba(0,0,0,0.4);padding:4px 12px;border-radius:20px"
      >
        {{ fotoActual + 1 }} / {{ inv.archivos.length }}
      </div>

      <q-carousel
        v-model="fotoActual"
        animated swipeable infinite
        :arrows="inv?.archivos?.length > 1"
        :navigation="false"
        class="bg-transparent full-width"
        style="height:100vh"
      >
        <q-carousel-slide
          v-for="(archivo, i) in inv?.archivos"
          :key="archivo.id"
          :name="i"
          class="q-pa-none"
        >
          <q-img
            :src="`${API_URL}/storage/${archivo.url}`"
            fit="contain"
            class="full-height full-width"
          >
            <template v-slot:loading>
              <q-spinner-dots color="white" size="40px" />
            </template>
          </q-img>
        </q-carousel-slide>
      </q-carousel>
    </div>
  </q-dialog>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import InventarioService from 'src/services/InventarioService'

const props = defineProps({
  inventarioId: { type: Number, required: true },
})
const emit = defineEmits(['editar'])

const $q      = useQuasar()
const API_URL = process.env.API_BACKEND_URL

const inv        = ref(null)
const loading    = ref(true)
const lightbox   = ref(false)
const fotoActual = ref(0)

onMounted(async () => {
  try {
    inv.value = await InventarioService.get(props.inventarioId)
  } catch {
    $q.notify({ type: 'negative', message: 'No se pudo cargar el producto.', position: 'top' })
  } finally {
    loading.value = false
  }
})

const firstImage = computed(() => {
  const a = inv.value?.archivos?.[0]
  return a?.url ? `${API_URL}/storage/${a.url}` : null
})

const sinStock   = computed(() => (inv.value?.cantidad ?? 0) === 0)
const stockBajo  = computed(() =>
  inv.value?.stock_minimo != null && inv.value.cantidad > 0 && inv.value.cantidad <= inv.value.stock_minimo
)

function diasParaVencer(fecha) {
  if (!fecha) return null
  return (new Date(fecha) - new Date()) / (1000 * 60 * 60 * 24)
}
const vencido    = computed(() => { const d = diasParaVencer(inv.value?.fecha_vencimiento); return d !== null && d < 0 })
const proxVencer = computed(() => { const d = diasParaVencer(inv.value?.fecha_vencimiento); return d !== null && d >= 0 && d <= 30 })

function abrirFoto(i) {
  fotoActual.value = i
  lightbox.value   = true
}
</script>

<style lang="sass" scoped>
.detalle-card
  border-radius: 16px
  overflow: hidden

// Avatar
.avatar-circle
  width: 56px
  height: 56px
  border-radius: 50%
  background: $primary
  display: flex
  align-items: center
  justify-content: center
  flex-shrink: 0
  overflow: hidden

.avatar-initials
  color: white
  font-size: 22px
  font-weight: 700
  font-family: 'Nunito', sans-serif

// Meta line
.meta-line
  font-size: 13px
  display: flex
  flex-wrap: wrap
  align-items: center
  gap: 4px
  .sep
    color: #bdbdbd
    margin: 0 2px

// Stats
.stat-card
  border-radius: 10px !important

.stat-label
  font-size: 10px
  font-weight: 700
  letter-spacing: 0.08em
  text-transform: uppercase
  
  margin-bottom: 6px

.stat-value
  font-size: 22px
  font-weight: 700
  line-height: 1
  

// Info sections
.section-title
  font-size: 10px
  font-weight: 700
  letter-spacing: 0.1em
  text-transform: uppercase
  margin-bottom: 10px

.info-row
  display: flex
  justify-content: space-between
  align-items: baseline
  padding: 5px 0
  border-bottom: 1px solid #fafafa
  &:last-child
    border-bottom: none

.info-label
  font-size: 14px

.info-value
  font-size: 14px
  
  font-weight: 500
  text-align: right
  max-width: 60%

// Galería
.foto-thumb
  position: relative
  border-radius: 10px
  overflow: hidden
  opacity: 0.88
  transition: opacity 0.2s, transform 0.2s
  &:hover
    opacity: 1
    transform: scale(1.05)
    .foto-overlay
      opacity: 1

.foto-overlay
  position: absolute
  inset: 0
  background: rgba(0, 0, 0, 0.35)
  display: flex
  align-items: center
  justify-content: center
  opacity: 0
  transition: opacity 0.2s
  border-radius: 10px
</style>
