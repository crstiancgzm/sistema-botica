<template>
  <q-dialog ref="dialogRef" persistent maximized>
    <q-card class="column" style="max-width: 100vw">

      <!-- Header -->
      <q-card-section class="bg-primary text-white row items-center q-py-sm">
        <q-icon name="bi-upc-scan" size="24px" class="q-mr-sm" />
        <span class="text-h6 text-weight-bold">Generador de Códigos de Barras</span>
        <q-space />
        <q-btn flat round dense icon="close" color="white" @click="onDialogCancel" />
      </q-card-section>

      <!-- Toolbar -->
      <q-card-section class="q-py-sm q-px-md row items-center q-gutter-sm">
        <q-input
          v-model="search"
          standout="bg-primary text-white"
          dense debounce="500"
          placeholder="Buscar producto..."
          style="min-width: 250px"
        >
          <template v-slot:prepend><q-icon name="search" color="white" /></template>
          <template v-slot:append>
            <q-icon v-if="search" name="close" class="cursor-pointer" @click="search = ''" />
          </template>
        </q-input>

        <q-space />

        <div class="text-caption text-grey-6">
          {{ seleccionados.length }} seleccionado{{ seleccionados.length !== 1 ? 's' : '' }}
          · {{ totalEtiquetas }} etiqueta{{ totalEtiquetas !== 1 ? 's' : '' }}
        </div>

        <q-btn v-if="seleccionados.length > 0" flat dense no-caps
          color="grey-7" icon="bi-x-circle" label="Limpiar selección"
          @click="seleccionadosMap = {}" />

        <q-select
          v-model="tamano"
          :options="opcionesTamano"
          label="Tamaño etiqueta"
          dense outlined
          style="min-width: 160px"
          emit-value map-options
        />

        <q-btn
          color="primary"
          icon="bi-file-earmark-pdf"
          label="Generar PDF"
          :disable="seleccionados.length === 0 || generando"
          :loading="generando"
          @click="generarPDF"
        />
      </q-card-section>

      <q-separator />

      <!-- Tabla server-side -->
      <q-card-section class="col q-pa-none" style="overflow: auto">
        <q-table
          flat
          :rows="rows"
          :columns="columns"
          row-key="id"
          v-model:pagination="pagination"
          :loading="cargando"
          :filter="search"
          binary-state-sort
          :rows-per-page-options="[10, 25, 50]"
          class="full-height-table"
          @request="onRequest"
        >
          <!-- Checkbox header — selecciona/deselecciona página actual -->
          <template v-slot:header-cell-sel="props">
            <q-th :props="props" style="width: 50px">
              <q-checkbox
                :model-value="todosPaginaSeleccionados"
                :indeterminate="algunosPaginaSeleccionados"
                @update:model-value="toggleTodosPagina"
              />
            </q-th>
          </template>

          <!-- Checkbox fila -->
          <template v-slot:body-cell-sel="props">
            <q-td :props="props">
              <q-checkbox
                :model-value="!!seleccionadosMap[props.row.id]"
                @update:model-value="(v) => toggleFila(props.row, v)"
              />
            </q-td>
          </template>

          <!-- Código -->
          <template v-slot:body-cell-codigo="props">
            <q-td :props="props">
              <span class="text-mono text-weight-bold">{{ props.row.codigo || '—' }}</span>
            </q-td>
          </template>

          <!-- Cantidad de etiquetas -->
          <template v-slot:body-cell-cantidad_etiquetas="props">
            <q-td :props="props">
              <q-input
                :model-value="seleccionadosMap[props.row.id]?._qty ?? 1"
                type="number"
                dense outlined
                min="1" max="100"
                style="width: 80px"
                :disable="!seleccionadosMap[props.row.id]"
                @update:model-value="(v) => updateQty(props.row.id, v)"
              />
            </q-td>
          </template>

          <template v-slot:no-data>
            <div class="full-width column flex-center q-pa-xl text-grey-5">
              <q-icon name="mdi-package-variant-remove" size="48px" class="q-mb-sm" />
              <div>Sin productos</div>
            </div>
          </template>
        </q-table>
      </q-card-section>

    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useDialogPluginComponent, useQuasar } from 'quasar'
import JsBarcode from 'jsbarcode'
import { jsPDF } from 'jspdf'
import InventarioService from 'src/services/InventarioService'

defineEmits([...useDialogPluginComponent.emits])

const { dialogRef, onDialogCancel } = useDialogPluginComponent()
const $q = useQuasar()

// ─── Estado tabla ────────────────────────────────────────────────────────────
const cargando = ref(false)
const generando = ref(false)
const search   = ref('')
const rows     = ref([])
const pagination = ref({
  sortBy: 'nombre', descending: false, page: 1, rowsPerPage: 10, rowsNumber: 0,
})

// ─── Selección persistente entre páginas ─────────────────────────────────────
// Clave: id del producto → { ...datosProducto, _qty }
const seleccionadosMap = ref({})

const seleccionados  = computed(() => Object.values(seleccionadosMap.value))
const totalEtiquetas = computed(() => seleccionados.value.reduce((s, r) => s + (r._qty || 1), 0))

const todosPaginaSeleccionados = computed(() =>
  rows.value.length > 0 && rows.value.every(r => !!seleccionadosMap.value[r.id])
)
const algunosPaginaSeleccionados = computed(() =>
  rows.value.some(r => !!seleccionadosMap.value[r.id]) && !todosPaginaSeleccionados.value
)

// ─── Tamaño de etiqueta ───────────────────────────────────────────────────────
const tamano = ref('pequeno')
const opcionesTamano = [
  { label: 'Pequeño  (38×19 mm)', value: 'pequeno' },
  { label: 'Mediano  (60×30 mm)', value: 'mediano' },
  { label: 'Grande   (90×45 mm)', value: 'grande'  },
]

// ─── Columnas ────────────────────────────────────────────────────────────────
const columns = [
  { name: 'sel',               label: '',            field: 'id',              align: 'center', style: 'width:50px' },
  { name: 'codigo',            label: 'Código',      field: 'codigo',          align: 'left',   sortable: true },
  { name: 'nombre',            label: 'Producto',    field: 'nombre',          align: 'left',   sortable: true },
  { name: 'laboratorio',       label: 'Laboratorio', field: r => r.laboratorio?.nombre ?? '—', align: 'left' },
  { name: 'precio_oficial',    label: 'Precio',      field: 'precio_oficial',  align: 'right',  sortable: true,
    format: v => `S/. ${Number(v || 0).toFixed(2)}` },
  { name: 'cantidad_etiquetas', label: 'Etiquetas',  field: 'id',              align: 'center' },
]

// ─── Selección ────────────────────────────────────────────────────────────────
function toggleFila(row, val) {
  if (val) {
    seleccionadosMap.value[row.id] = {
      ...row,
      _qty: seleccionadosMap.value[row.id]?._qty || 1,
    }
  } else {
    delete seleccionadosMap.value[row.id]
  }
}

function updateQty(id, val) {
  const entry = seleccionadosMap.value[id]
  if (entry) entry._qty = Math.max(1, Number(val) || 1)
}

function toggleTodosPagina(val) {
  rows.value.forEach(r => {
    if (val) {
      if (!seleccionadosMap.value[r.id])
        seleccionadosMap.value[r.id] = { ...r, _qty: 1 }
    } else {
      delete seleccionadosMap.value[r.id]
    }
  })
}

// ─── Request server-side ─────────────────────────────────────────────────────
async function onRequest(props) {
  const { page, rowsPerPage, sortBy, descending } = props.pagination
  cargando.value = true

  try {
    const order_by = descending ? '-' + sortBy : sortBy
    const { data, total = 0 } = await InventarioService.getData({
      params: { rowsPerPage, page, search: props.filter, order_by },
    })
    rows.value = data
    pagination.value = {
      ...pagination.value,
      rowsNumber: total || data.length,
      page, rowsPerPage, sortBy, descending,
    }
  } finally {
    cargando.value = false
  }
}

onMounted(() => onRequest({ pagination: pagination.value, filter: search.value }))

// ─── PDF ─────────────────────────────────────────────────────────────────────
const configsTamano = {
  pequeno: { w: 38, h: 19, fontSize: 5, barcodeH: 20, cols: 5 },
  mediano: { w: 60, h: 30, fontSize: 7, barcodeH: 35, cols: 3 },
  grande:  { w: 90, h: 45, fontSize: 9, barcodeH: 55, cols: 2 },
}

function generarSVGBarcode(codigo) {
  const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg')
  try {
    JsBarcode(svg, codigo, { format: 'CODE128', displayValue: false, margin: 0, width: 1.5, height: 40 })
    return new XMLSerializer().serializeToString(svg)
  } catch { return null }
}

function svgToDataURL(svgStr) {
  return 'data:image/svg+xml,' + encodeURIComponent(svgStr)
}

async function imgFromDataURL(dataURL) {
  return new Promise((resolve, reject) => {
    const img = new Image()
    img.onload  = () => resolve(img)
    img.onerror = reject
    img.src = dataURL
  })
}

async function drawBarcodeToCanvas(codigo, widthPx, heightPx) {
  const svgStr = generarSVGBarcode(codigo)
  if (!svgStr) return null
  const canvas = document.createElement('canvas')
  canvas.width = widthPx; canvas.height = heightPx
  const ctx = canvas.getContext('2d')
  ctx.fillStyle = '#ffffff'
  ctx.fillRect(0, 0, widthPx, heightPx)
  const img = await imgFromDataURL(svgToDataURL(svgStr))
  ctx.drawImage(img, 0, 0, widthPx, heightPx)
  return canvas.toDataURL('image/png')
}

async function generarPDF() {
  if (seleccionados.value.length === 0) return
  generando.value = true

  try {
    const cfg    = configsTamano[tamano.value]
    const margen = 5
    const pageH  = 297
    const pdf    = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' })

    let col = 0, row = 0

    const etiquetas = []
    for (const prod of seleccionados.value)
      for (let i = 0; i < (prod._qty || 1); i++) etiquetas.push(prod)

    const barcodeAreaH = cfg.h * 0.55
    const textAreaH    = cfg.h * 0.45

    for (let i = 0; i < etiquetas.length; i++) {
      const prod = etiquetas[i]
      const x    = margen + col * (cfg.w + 2)
      const y    = margen + row * (cfg.h + 2)

      pdf.setFillColor(255, 255, 255)
      pdf.setDrawColor(180, 180, 180)
      pdf.setLineWidth(0.2)
      pdf.roundedRect(x, y, cfg.w, cfg.h, 1, 1, 'FD')

      const codigo  = prod.codigo || String(prod.id).padStart(8, '0')
      const pxW     = Math.round(cfg.w * 3.78)
      const pxH     = Math.round(barcodeAreaH * 3.78)
      const imgData = await drawBarcodeToCanvas(codigo, pxW, pxH)
      if (imgData) pdf.addImage(imgData, 'PNG', x + 1, y + 1, cfg.w - 2, barcodeAreaH - 1)

      const textY = y + barcodeAreaH + 1
      pdf.setFontSize(cfg.fontSize)
      pdf.setTextColor(30, 30, 30)
      pdf.setFont('helvetica', 'bold')
      const nombre = pdf.splitTextToSize(prod.nombre || '', cfg.w - 2)
      pdf.text(nombre[0], x + cfg.w / 2, textY + cfg.fontSize * 0.4, { align: 'center' })

      pdf.setFont('helvetica', 'normal')
      pdf.setFontSize(cfg.fontSize - 1)
      pdf.setTextColor(80, 80, 80)
      pdf.text(codigo, x + cfg.w / 2, textY + cfg.fontSize * 0.4 + cfg.fontSize * 0.8, { align: 'center' })

      if (textAreaH > 12) {
        pdf.setFont('helvetica', 'bold')
        pdf.setFontSize(cfg.fontSize + 1)
        pdf.setTextColor(0, 100, 0)
        pdf.text(
          `S/. ${Number(prod.precio_oficial || 0).toFixed(2)}`,
          x + cfg.w / 2,
          textY + cfg.fontSize * 0.4 + cfg.fontSize * 1.7,
          { align: 'center' }
        )
      }

      col++
      if (col >= cfg.cols) {
        col = 0
        row++
        const maxRows = Math.floor((pageH - margen * 2) / (cfg.h + 2))
        if (row >= maxRows && i < etiquetas.length - 1) {
          pdf.addPage()
          row = 0
        }
      }
    }

    pdf.save(`codigos-barras-${new Date().toISOString().slice(0, 10)}.pdf`)
    $q.notify({ type: 'positive', message: 'PDF generado exitosamente.', position: 'top-right', timeout: 2000 })
  } catch (err) {
    console.error(err)
    $q.notify({ type: 'negative', message: 'Error al generar el PDF.', position: 'top-right', timeout: 3000 })
  } finally {
    generando.value = false
  }
}
</script>

<style scoped>
.text-mono { font-family: monospace; }
.full-height-table { height: 100%; }
</style>
