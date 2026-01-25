<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Resumen de Cliente</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#111; }
    .title { font-size: 16px; font-weight: 700; margin-bottom: 8px; }
    .muted { color:#666; }
    .box { border: 1px solid #ddd; padding: 10px; border-radius: 6px; margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; }
    td { padding: 6px 4px; vertical-align: top; }
    .label { width: 28%; color:#333; font-weight: 700; }
    .hr { border-top:1px solid #eee; margin: 10px 0; }
  </style>
</head>
<body>
  <div class="title">Resumen de Registro de Cliente</div>
  <div class="muted">Código: #{{ $cliente->id }} | Fecha: {{ $cliente->fecha_reg ?? now()->toDateString() }}</div>

  <div class="hr"></div>

  <div class="box">
    <div class="title" style="font-size:14px;">Datos del Cliente</div>
    <table>
      <tr>
        <td class="label">Apellidos y nombres</td>
        <td>
          {{ $cliente->persona->ape_pat ?? '' }}
          {{ $cliente->persona->ape_mat ?? '' }}
          {{ $cliente->persona->primernombre ?? '' }}
          {{ $cliente->persona->otrosnombres ?? '' }}
        </td>
      </tr>
      <tr>
        <td class="label">DNI</td>
        <td>{{ $cliente->persona->dni ?? '-' }}</td>
      </tr>
      <tr>
        <td class="label">Celular</td>
        <td>{{ $cliente->persona->celular ?? '-' }}</td>
      </tr>
      <tr>
        <td class="label">Dirección</td>
        <td>{{ $cliente->persona->direccion ?? '-' }}</td>
      </tr>
    </table>
  </div>

  @if(!empty($cliente->negocio))
  <div class="box">
    <div class="title" style="font-size:14px;">Negocio</div>
    <table>
      <tr>
        <td class="label">Razón social</td>
        <td>{{ $cliente->negocio->razonsocial ?? '-' }}</td>
      </tr>
      <tr>
        <td class="label">RUC</td>
        <td>{{ $cliente->negocio->ruc ?? '-' }}</td>
      </tr>
      <tr>
        <td class="label">Celular</td>
        <td>{{ $cliente->negocio->tel_cel ?? $cliente->negocio->celular ?? '-' }}</td>
      </tr>
      <tr>
        <td class="label">Dirección</td>
        <td>{{ $cliente->negocio->direccion ?? '-' }}</td>
      </tr>
    </table>
  </div>
  @endif

  <div class="box">
    <div class="title" style="font-size:14px;">Referente</div>
    @php $ref = $cliente->referente ?? null; @endphp
    <table>
      <tr>
        <td class="label">Nombre</td>
        <td>
          {{ $ref->ape_pat ?? '-' }} {{ $ref->ape_mat ?? '' }}
          {{ $ref->primernombre ?? '' }} {{ $ref->otrosnombres ?? '' }}
        </td>
      </tr>
      <tr>
        <td class="label">DNI</td>
        <td>{{ $ref->dni ?? '-' }}</td>
      </tr>
      <tr>
        <td class="label">Celular</td>
        <td>{{ $ref->celular ?? '-' }}</td>
      </tr>
    </table>
  </div>

  <div class="muted" style="margin-top:10px;">
    Documento generado automáticamente.
  </div>
</body>
</html>
