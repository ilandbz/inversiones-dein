<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Resumen de Cliente</title>
  <style>
    @page { margin: 22px 26px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11.5px; color:#111827; }
    .text-muted { color:#6b7280; }
    .small { font-size: 10px; }
    .mb-1{margin-bottom:4px;} .mb-2{margin-bottom:8px;} .mb-3{margin-bottom:12px;}
    .mt-2{margin-top:8px;} .mt-3{margin-top:12px;}

    /* Header */
    .header {
      border: 1px solid #e5e7eb;
      background: #f8fafc;
      border-radius: 10px;
      padding: 14px 14px 12px;
    }
    .brand {
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
    }
    .title {
      font-size: 16px;
      font-weight: 800;
      letter-spacing: .2px;
      margin: 0;
      color:#0f172a;
    }
    .badge {
      display:inline-block;
      padding: 3px 9px;
      border-radius: 999px;
      border: 1px solid #d1d5db;
      background: #ffffff;
      font-size: 10px;
      font-weight: 700;
      color:#111827;
    }
    .badge.ok { border-color:#bbf7d0; background:#f0fdf4; color:#166534; }
    .badge.warn { border-color:#fde68a; background:#fffbeb; color:#92400e; }

    /* Layout */
    .grid { width:100%; border-collapse:separate; border-spacing: 12px 10px; }
    .card {
      border: 1px solid #e5e7eb;
      border-radius: 10px;
      padding: 12px;
    }
    .card h3 {
      margin:0 0 8px 0;
      font-size: 12.5px;
      font-weight: 800;
      color:#0f172a;
    }
    .card .subtitle {
      margin-top:-4px;
      font-size:10px;
      color:#6b7280;
    }

    /* “Bootstrap-like” table */
    .kv { width:100%; border-collapse:collapse; }
    .kv td { padding: 6px 6px; vertical-align: top; border-top: 1px solid #f1f5f9; }
    .kv tr:first-child td { border-top:none; }
    .k { width: 30%; color:#334155; font-weight: 700; }
    .v { color:#0f172a; }
    .pill {
      display:inline-block;
      padding: 2px 8px;
      border-radius: 999px;
      border: 1px solid #e5e7eb;
      background:#ffffff;
      font-size: 10px;
      font-weight: 700;
      color:#0f172a;
    }

    /* Footer */
    .footer {
      margin-top: 12px;
      padding-top: 10px;
      border-top: 1px dashed #e5e7eb;
      font-size: 10px;
      color:#6b7280;
    }
    .right { text-align:right; }
    .nowrap { white-space:nowrap; }
  </style>
</head>
<body>

@php
  $p = $cliente->persona ?? null;
  $n = $cliente->negocio ?? null;
  $r = $cliente->referente ?? null;

  $estado = $cliente->estado ?? '—';
  $badgeClass = $estado === 'REGISTRADO' ? 'ok' : 'warn';

  $fecha = $cliente->fecha_reg ?? now()->toDateString();
  $hora  = $cliente->hora_reg ?? '';
@endphp

  <!-- HEADER -->
  <div class="header mb-3">
    <div class="brand">
      <div>
        <p class="title mb-1">Resumen de Registro de Cliente</p>
        <div class="text-muted small">
          Código: <b>#{{ $cliente->id }}</b>
          <span class="nowrap">| Fecha: <b>{{ $fecha }}</b></span>
          @if($hora) <span class="nowrap">| Hora: <b>{{ $hora }}</b></span> @endif
        </div>
      </div>

      <div class="right">
        <span class="badge {{ $badgeClass }}">{{ $estado }}</span>
        <div class="text-muted small mt-2">
          Generado: {{ now()->format('Y-m-d H:i:s') }}
        </div>
      </div>
    </div>
  </div>

  <!-- GRID -->
  <table class="grid">
    <tr>
      <!-- CLIENTE -->
      <td style="width: 50%;">
        <div class="card">
          <h3>Datos del cliente</h3>
          <div class="subtitle mb-2">Información personal registrada</div>

          <table class="kv">
            <tr>
              <td class="k">Apellidos y nombres</td>
              <td class="v">
                {{ $p->ape_pat ?? '' }} {{ $p->ape_mat ?? '' }}
                {{ $p->primernombre ?? '' }} {{ $p->otrosnombres ?? '' }}
              </td>
            </tr>
            <tr>
              <td class="k">DNI</td>
              <td class="v">{{ $p->dni ?? '—' }}</td>
            </tr>
            <tr>
              <td class="k">Fecha nac.</td>
              <td class="v">
                {{ $p->fecha_nac ?? '—' }}
                @if(!empty($p->edad))
                  <span class="pill">{{ $p->edad }} años</span>
                @endif
              </td>
            </tr>
            <tr>
              <td class="k">Género</td>
              <td class="v">{{ $p->genero ?? '—' }}</td>
            </tr>
            <tr>
              <td class="k">Celular</td>
              <td class="v">{{ $p->celular ?? '—' }}</td>
            </tr>
            <tr>
              <td class="k">Dirección</td>
              <td class="v">{{ $p->direccion ?? '—' }}</td>
            </tr>
            <tr>
              <td class="k">Ubigeo dom.</td>
              <td class="v">{{ $p->ubigeo_dom ?? '—' }}</td>
            </tr>
            <tr>
              <td class="k">Estado civil</td>
              <td class="v">{{ $p->estado_civil ?? '—' }}</td>
            </tr>
          </table>
        </div>
      </td>

      <!-- NEGOCIO -->
      <td style="width: 50%;">
        <div class="card">
          <h3>Negocio</h3>
          <div class="subtitle mb-2">Datos comerciales asociados (si aplica)</div>

          @if($n)
            <table class="kv">
              <tr>
                <td class="k">Razón social</td>
                <td class="v">{{ $n->razonsocial ?? '—' }}</td>
              </tr>
              <tr>
                <td class="k">RUC</td>
                <td class="v">{{ $n->ruc ?? '—' }}</td>
              </tr>
              <tr>
                <td class="k">Celular</td>
                <td class="v">{{ $n->celular ?? ($n->tel_cel ?? '—') }}</td>
              </tr>
              <tr>
                <td class="k">Dirección</td>
                <td class="v">{{ $n->direccion ?? '—' }}</td>
              </tr>
              <tr>
                <td class="k">Actividad</td>
                <td class="v">{{ $n->detalle_actividad_id ?? '—' }}</td>
              </tr>
              <tr>
                <td class="k">Inicio actividad</td>
                <td class="v">{{ $n->inicioactividad ?? '—' }}</td>
              </tr>
            </table>
          @else
            <div class="text-muted">No registra negocio asociado.</div>
          @endif
        </div>
      </td>
    </tr>

    <tr>
      <!-- REFERENTE -->
      <td colspan="2">
        <div class="card">
          <h3>Referente</h3>
          <div class="subtitle mb-2">
            Parentesco: <span class="pill">{{ $cliente->referente_parentesco ?? '—' }}</span>
          </div>

          <table class="kv">
            <tr>
              <td class="k">Nombre</td>
              <td class="v">
                @if($r)
                  {{ $r->ape_pat ?? '' }} {{ $r->ape_mat ?? '' }}
                  {{ $r->primernombre ?? '' }} {{ $r->otrosnombres ?? '' }}
                @else
                  —
                @endif
              </td>
            </tr>
            <tr>
              <td class="k">DNI</td>
              <td class="v">{{ $r->dni ?? '—' }}</td>
            </tr>
            <tr>
              <td class="k">Celular</td>
              <td class="v">{{ $r->celular ?? '—' }}</td>
            </tr>
            <tr>
              <td class="k">Dirección</td>
              <td class="v">{{ $r->direccion ?? '—' }}</td>
            </tr>
            <tr>
              <td class="k">Ocupación</td>
              <td class="v">{{ $r->ocupacion ?? '—' }}</td>
            </tr>
          </table>
        </div>
      </td>
    </tr>
  </table>

  <div class="footer">
    Documento generado automáticamente. Si detecta inconsistencias, verifique la ficha del cliente en el sistema.
  </div>
</body>
</html>
