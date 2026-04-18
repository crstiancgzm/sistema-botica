<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'DejaVu Sans', sans-serif;
      font-size: 9pt;
      color: #111;
      background: #fff;
      padding: 12pt 12pt 18pt;
    }

    /* HEADER */
    .header        { text-align: center; margin-bottom: 10pt; }
    .h-name        { font-size: 15pt; font-weight: bold; letter-spacing: 1px; }
    .h-meta        { font-size: 8pt; color: #555; margin-top: 2pt; }

    /* DOC BOX */
    .doc-box       { border: 1pt solid #bbb; border-radius: 3pt; text-align: center; padding: 7pt 10pt; margin: 8pt 0 10pt; }
    .doc-tipo      { font-size: 10pt; font-weight: bold; }
    .doc-num       { font-size: 9pt; font-weight: bold; margin-top: 2pt; }
    .doc-fecha     { font-size: 8pt; color: #777; margin-top: 2pt; }

    /* SECTION LABEL */
    .lbl           { font-size: 8pt; font-weight: bold; margin: 9pt 0 4pt; border-bottom: 1pt solid #eee; padding-bottom: 2pt; }

    /* DATA ROWS */
    .row           { display: flex; margin-bottom: 2pt; }
    .row-label     { font-size: 8.5pt; color: #555; width: 55pt; flex-shrink: 0; }
    .row-value     { font-size: 8.5pt; font-weight: bold; }

    /* SEPARATOR */
    .sep           { border: none; border-top: 1pt dashed #ccc; margin: 9pt 0; }

    /* TABLE */
    table          { width: 100%; border-collapse: collapse; margin-top: 2pt; table-layout: fixed; }

    col.c-desc     { width: 50%; }
    col.c-cant     { width: 10%; }
    col.c-pu       { width: 20%; }
    col.c-total    { width: 20%; }

    thead th       { font-size: 7pt; font-weight: bold; text-transform: uppercase; color: #555; border-bottom: 1.5pt solid #222; padding: 2pt 3pt 4pt; }
    .al            { text-align: left; }
    .ac            { text-align: center; }
    .ar            { text-align: right; }

    tbody td       { padding: 5pt 3pt 4pt; vertical-align: top; border-bottom: 1pt solid #eee; overflow: hidden; }
    .td-name       { font-size: 8pt; font-weight: bold; word-break: break-word; }
    .td-tipo       { font-size: 7pt; color: #777; margin-top: 1pt; }
    .td-c          { font-size: 8.5pt; text-align: center; color: #333; }
    .td-pu         { font-size: 8.5pt; text-align: right; color: #555; }
    .td-tot        { font-size: 8.5pt; text-align: right; font-weight: bold; }

    /* TOTAL */
    .total-wrap    { margin-top: 10pt; border-top: 2pt solid #111; padding-top: 7pt; display: flex; justify-content: space-between; align-items: baseline; }
    .total-label   { font-size: 10pt; font-weight: bold; }
    .total-amount  { font-size: 15pt; font-weight: bold; }

    /* PAGO */
    .pago-row      { display: flex; align-items: center; gap: 6pt; margin-top: 6pt; }
    .pago-lbl      { font-size: 8pt; color: #777; }
    .pago-badge    { font-size: 7.5pt; font-weight: bold; text-transform: uppercase; background: #e8f4fd; color: #1a6fad; border-radius: 3pt; padding: 2pt 8pt; }

    /* FOOTER */
    .footer        { text-align: center; margin-top: 14pt; }
    .ft-thanks     { font-size: 10pt; font-weight: bold; }
    .ft-note       { font-size: 7.5pt; color: #aaa; margin-top: 2pt; }
  </style>
</head>
<body>

  <div class="header">
    <div class="h-name">BOTICAS PUNO</div>
    <div class="h-meta">RUC: {{ config('app.ruc', '-') }}</div>
    <div class="h-meta">{{ config('app.direccion', 'Puno, Peru') }}</div>
  </div>

  <div class="doc-box">
    <div class="doc-tipo">
      @if($venta->tipo_comprobante === 'boleta') BOLETA DE VENTA
      @elseif($venta->tipo_comprobante === 'factura') FACTURA ELECTRONICA
      @else NOTA DE VENTA
      @endif
    </div>
    <div class="doc-num">N&deg; {{ str_pad($venta->id, 8, '0', STR_PAD_LEFT) }}</div>
    <div class="doc-fecha">{{ $venta->created_at->format('d/m/Y H:i') }}</div>
  </div>

  <div class="lbl">CLIENTE</div>
  <div class="row">
    <span class="row-label">Nombre:</span>
    <span class="row-value">{{ $venta->cliente_nombre }}</span>
  </div>
  @if($venta->cliente_dni)
  <div class="row">
    <span class="row-label">DNI:</span>
    <span class="row-value">{{ $venta->cliente_dni }}</span>
  </div>
  @endif
  <div class="row">
    <span class="row-label">Cajero:</span>
    <span class="row-value">{{ $venta->usuario->name }}</span>
  </div>
  <div class="row">
    <span class="row-label">Turno:</span>
    <span class="row-value">{{ ucfirst($venta->caja->turno) }}</span>
  </div>

  <div class="lbl">PRODUCTOS</div>
  <table>
    <colgroup>
      <col class="c-desc">
      <col class="c-cant">
      <col class="c-pu">
      <col class="c-total">
    </colgroup>
    <thead>
      <tr>
        <th class="al">Descripcion</th>
        <th class="ac">Cant.</th>
        <th class="ar">P.U.</th>
        <th class="ar">Total</th>
      </tr>
    </thead>
    <tbody>
      @foreach($venta->items as $item)
      <tr>
        <td>
          <div class="td-name">{{ $item->inventario->nombre }}</div>
          <div class="td-tipo">{{ ucfirst($item->tipo_venta) }}</div>
        </td>
        <td class="td-c">{{ $item->cantidad }}</td>
        <td class="td-pu">S/. {{ number_format($item->precio_venta, 2) }}</td>
        <td class="td-tot">S/. {{ number_format($item->subtotal, 2) }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <hr class="sep">

  <div class="total-wrap">
    <span class="total-label">TOTAL A PAGAR</span>
    <span class="total-amount">S/. {{ number_format($venta->total, 2) }}</span>
  </div>

  <div class="pago-row">
    <span class="pago-lbl">Forma de pago</span>
    <span class="pago-badge">{{ strtoupper($venta->metodo_pago) }}</span>
  </div>

  <hr class="sep">

  <div class="footer">
    <div class="ft-thanks">Gracias por su compra!</div>
    <div class="ft-note">Conserve este comprobante</div>
  </div>

</body>
</html>
