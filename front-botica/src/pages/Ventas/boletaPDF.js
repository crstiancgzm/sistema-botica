import { jsPDF } from 'jspdf'

// ─── Constantes ─────────────────────────────────────────────────────────────
const W      = 80   // mm - ticket térmico 80mm
const M      = 4    // margen lateral
const UW     = W - M * 2  // ancho útil: 72mm

// Posiciones X de columnas de items
const COL = {
  cod:    M,
  desc:   M + 14,
  cant:   M + 41,
  punit:  M + 50,
  importe: W - M,
}

// ─── Helpers tipografía ──────────────────────────────────────────────────────
const f = {
  bold:   (pdf, sz) => { pdf.setFont('courier', 'bold');   pdf.setFontSize(sz) },
  normal: (pdf, sz) => { pdf.setFont('courier', 'normal'); pdf.setFontSize(sz) },
  black:  (pdf)     => pdf.setTextColor(0, 0, 0),
  grey:   (pdf)     => pdf.setTextColor(90, 90, 90),
}

// ─── Convertir número a letras (español) ────────────────────────────────────
function _centenas(n) {
  const C = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS',
             'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS']
  if (n === 100) return 'CIEN'
  const h = Math.floor(n / 100)
  const r = n % 100
  return (h ? C[h] + (r ? ' ' : '') : '') + (r ? _menorCien(r) : '')
}
function _menorCien(n) {
  const U = ['', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE',
             'OCHO', 'NUEVE', 'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE',
             'QUINCE', 'DIECISÉIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE']
  const D = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA',
             'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA']
  if (n < 20) return U[n]
  const d = Math.floor(n / 10), u = n % 10
  if (d === 2 && u) return 'VEINTI' + U[u]
  return D[d] + (u ? ' Y ' + U[u] : '')
}
function numToWords(n) {
  if (n === 0) return 'CERO'
  if (n < 100) return _menorCien(n)
  if (n < 1000) return _centenas(n)
  const miles = Math.floor(n / 1000), r = n % 1000
  const prefMiles = miles === 1 ? 'MIL' : numToWords(miles) + ' MIL'
  return prefMiles + (r ? ' ' + numToWords(r) : '')
}
function montoEnLetras(monto) {
  const entero   = Math.floor(monto)
  const centavos = Math.round((monto - entero) * 100)
  return `SON : ${numToWords(entero)} CON ${String(centavos).padStart(2, '0')}/100 SOLES.`
}

// ─── Formateo de fecha ───────────────────────────────────────────────────────
function formatFecha(str) {
  if (!str) return '—'
  const d = new Date(str)
  const pad = n => String(n).padStart(2, '0')
  return `${pad(d.getDate())}/${pad(d.getMonth() + 1)}/${d.getFullYear()} ` +
         `${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`
}

// ─── Línea de guiones ────────────────────────────────────────────────────────
function drawDashed(pdf, y) {
  pdf.setLineDash([1, 1], 0)
  pdf.setDrawColor(120, 120, 120)
  pdf.setLineWidth(0.2)
  pdf.line(M, y, W - M, y)
  pdf.setLineDash([], 0)
  return y + 3
}

// ─── Texto centrado ──────────────────────────────────────────────────────────
function center(pdf, text, y) {
  pdf.text(text, W / 2, y, { align: 'center' })
  return y
}

// ─── Texto izq / der en misma línea ─────────────────────────────────────────
function twoCol(pdf, left, right, y) {
  pdf.text(left, M, y)
  pdf.text(right, W - M, y, { align: 'right' })
}

// ─── Altura dinámica ─────────────────────────────────────────────────────────
function calcHeight(items) {
  const itemLines = items.reduce((acc, item) => {
    const nameLen = (item.inventario?.nombre || '').length
    return acc + (nameLen > 22 ? 3 : 2)  // líneas por item
  }, 0)
  return 30           // header
    + 8               // doc type
    + 18              // info cliente / cajero
    + 4               // separadores
    + 6               // cabecera tabla
    + itemLines * 4.5 // items
    + 4               // separador
    + 20              // totales
    + 8               // monto en letras + pago
    + 4               // separador
    + 18              // footer
    + 10              // margen inferior
}

// ─── SECCIONES ───────────────────────────────────────────────────────────────

function drawHeader(pdf, y) {
  f.bold(pdf, 15)
  f.black(pdf)
  center(pdf, 'BOTICAS PUNO', y)
  y += 5

  f.normal(pdf, 7)
  center(pdf, 'BOTICAS PUNO S.A.C. - RUC: 20600123456', y); y += 3.5
  center(pdf, 'CENTRAL: Jr. Lima Nro. 234 Puno', y);        y += 3.5
  center(pdf, 'TIENDA 001 - PUNO - 051-365000', y);         y += 3.5
  center(pdf, 'Av. El Puerto N|| 120 - 124 (R)', y);        y += 5
  return y
}

function drawDocumentType(pdf, venta, y) {
  const tipos = {
    boleta:          'BOLETA DE VENTA ELECTRONICA',
    factura:         'FACTURA ELECTRONICA',
    sin_comprobante: 'NOTA DE VENTA',
  }
  const series = { boleta: 'BAE7', factura: 'FAE7', sin_comprobante: 'NV01' }

  const tipo   = tipos[venta.tipo_comprobante]  || 'COMPROBANTE'
  const serie  = series[venta.tipo_comprobante] || 'NV01'
  const numero = `${serie}-${String(venta.id).padStart(8, '0')}`

  f.bold(pdf, 7.5)
  center(pdf, tipo, y);    y += 3.5
  center(pdf, numero, y);  y += 5
  return y
}

function drawInfoSection(pdf, venta, y) {
  const fecha = formatFecha(venta.created_at)

  f.normal(pdf, 6.8)
  f.black(pdf)

  twoCol(pdf, `FECHA EMISION: ${fecha}`, '', y); y += 3.5

  const caja   = venta.caja?.turno   ? `CAJA/TURNO: ${venta.caja.turno}` : 'CAJA/TURNO: 001/1'
  const cajero = venta.usuario?.name ? `CAJERO: ${venta.usuario.name.substring(0, 12).toUpperCase()}` : 'CAJERO: —'
  twoCol(pdf, caja, cajero, y); y += 3.5

  if (venta.cliente_dni) {
    pdf.text(`DNI CLIENTE: ${venta.cliente_dni}`, M, y); y += 3.5
  }
  pdf.text(`NOMBRE DE CLIENTE: ${(venta.cliente_nombre || 'Consumidor Final').toUpperCase()}`, M, y)
  y += 4
  return y
}

function drawTableHeader(pdf, y) {
  f.bold(pdf, 6.5)
  f.black(pdf)
  pdf.text('CODIGO',      COL.cod,     y)
  pdf.text('DESCRIPCION', COL.desc,    y)
  pdf.text('CANT.',       COL.cant,    y, { align: 'center' })
  pdf.text('P.UNIT.',     COL.punit,   y, { align: 'center' })
  pdf.text('IMPORTE',     COL.importe, y, { align: 'right' })
  y += 2.5
  return y
}

function drawItems(pdf, items, y) {
  f.normal(pdf, 6.8)
  f.black(pdf)

  for (const item of items) {
    const codigo   = item.inventario?.codigo || String(item.inventario_id || '').padStart(6, '0')
    const nombre   = (item.inventario?.nombre || '').toUpperCase()
    const tipo     = item.tipo_venta === 'blister' ? 'BLST' : 'UND'
    const cant     = String(item.cantidad)
    const pUnit    = Number(item.precio_venta).toFixed(3)
    const importe  = Number(item.subtotal).toFixed(2)

    // Partir nombre en líneas de ~22 chars
    const maxChars = 22
    const lines    = []
    let   tmp      = nombre
    while (tmp.length > maxChars) {
      let cut = tmp.lastIndexOf(' ', maxChars)
      if (cut < 1) cut = maxChars
      lines.push(tmp.substring(0, cut))
      tmp = tmp.substring(cut + 1)
    }
    if (tmp) lines.push(tmp)

    // Primera línea: todo en una fila
    pdf.text(codigo.substring(0, 6), COL.cod, y)
    pdf.text(lines[0] || '',         COL.desc, y)
    pdf.text(cant,                   COL.cant,    y, { align: 'center' })
    pdf.text(pUnit,                  COL.punit,   y, { align: 'center' })
    pdf.text(importe,                COL.importe, y, { align: 'right' })
    y += 3.5

    // Segunda línea: resto del nombre + tipo de venta
    const lineaDesc = (lines.slice(1).join(' ') + (lines.length > 1 ? ' ' : '') + tipo).trim()
    if (lineaDesc) {
      f.grey(pdf)
      pdf.text(lineaDesc, COL.desc, y)
      f.black(pdf)
      y += 3.5
    }
  }

  return y + 1
}

function drawTotals(pdf, venta, y) {
  const total       = Number(venta.total)
  const gravadas    = +(total / 1.18).toFixed(2)
  const igv         = +(total - gravadas).toFixed(2)

  const labelX = W - M - 26
  const valueX = W - M

  f.normal(pdf, 7)
  f.black(pdf)

  // Alinear columna label/valor
  function totalRow(label, value) {
    pdf.text(label + ':', labelX, y, { align: 'right' })
    pdf.text(`S/ ${value.toFixed(2)}`, valueX, y, { align: 'right' })
    y += 3.8
  }

  totalRow('OP.GRAVADAS', gravadas)
  totalRow(`IGV-18%`,     igv)

  // IMPORTE TOTAL — más grande y negrita
  f.bold(pdf, 8)
  pdf.text('IMPORTE TOTAL:', labelX, y, { align: 'right' })
  pdf.text(`S/ ${total.toFixed(2)}`, valueX, y, { align: 'right' })
  y += 5

  // Monto en letras
  f.normal(pdf, 6.5)
  const letras = montoEnLetras(total)
  const letraLines = pdf.splitTextToSize(letras, UW)
  letraLines.forEach(l => { pdf.text(l, M, y); y += 3.2 })

  y += 1

  // Método de pago
  const metodos = { efectivo: 'EFECTIVO', tarjeta: 'MASTERCARD PINPAD', transferencia: 'TRANSFERENCIA' }
  const metodo  = metodos[venta.metodo_pago] || venta.metodo_pago?.toUpperCase() || 'EFECTIVO'
  twoCol(pdf, metodo, `${total.toFixed(2)}`, y)
  y += 4

  return y
}

function drawFooter(pdf, y) {
  f.normal(pdf, 6.2)
  f.black(pdf)

  const lines = [
    'Guarda tu comprobante. Es el sustento para',
    'validar tu compra. Representacion impresa de',
    'la BOLETA DE VENTA ELECTRONICA puede ser',
    'consultado en cpe.sunat.gob.pe',
    'Cambios y devoluciones segun Ley 29571.',
  ]
  for (const line of lines) {
    center(pdf, line, y); y += 3.2
  }

  y += 2
  f.bold(pdf, 6.5)
  center(pdf, 'www.boticaspuno.pe', y); y += 4

  f.normal(pdf, 6)
  f.grey(pdf)
  const cajero = 'CAJA: 001'
  center(pdf, cajero, y)

  return y + 6
}

// ─── Export principal ────────────────────────────────────────────────────────
export function generarBoletaPDF(venta) {
  const items  = venta.items || []
  const height = calcHeight(items)

  const pdf = new jsPDF({
    orientation: 'portrait',
    unit:        'mm',
    format:      [W, height],
  })

  pdf.setDrawColor(0)
  pdf.setLineWidth(0.2)

  let y = 7

  y = drawHeader(pdf, y)
  y = drawDashed(pdf, y)
  y = drawDocumentType(pdf, venta, y)
  y = drawInfoSection(pdf, venta, y)
  y = drawDashed(pdf, y)
  y = drawTableHeader(pdf, y)
  y = drawDashed(pdf, y)
  y = drawItems(pdf, items, y)
  y = drawDashed(pdf, y)
  y = drawTotals(pdf, venta, y)
  y = drawDashed(pdf, y)
      drawFooter(pdf, y)

  return pdf
}

export function abrirBoletaPDF(venta) {
  const pdf  = generarBoletaPDF(venta)
  const blob = pdf.output('blob')
  const url  = URL.createObjectURL(blob)
  window.open(url, '_blank')
  setTimeout(() => URL.revokeObjectURL(url), 15000)
}
