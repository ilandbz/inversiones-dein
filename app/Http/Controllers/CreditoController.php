<?php

namespace App\Http\Controllers;

use App\Http\Requests\Credito\StoreCreditoRequest;
use App\Http\Requests\Credito\UpdateCreditoRequest;
use App\Models\Cliente;
use App\Models\Credito;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Traits\UserFilters;
use App\Models\CronogramaPago;

class CreditoController extends Controller
{

    use UserFilters;

    public function store(StoreCreditoRequest $request)
    {
        $fechaInicio = Carbon::now();
        switch (strtoupper($request->frecuencia)) {
            case 'DIARIA':
                $fechaVenc = $fechaInicio->addDays($request->plazo);
                break;

            case 'SEMANAL':
                $fechaVenc = $fechaInicio->addWeeks($request->plazo);
                break;

            case 'QUINCENAL':
                $fechaVenc = $fechaInicio->addDays($request->plazo * 15);
                break;

            case 'MENSUAL':
                $fechaVenc = $fechaInicio->addMonths($request->plazo);
                break;

            default:
                throw new \Exception('Frecuencia no válida');
        }

        try {

            $credito = Credito::create([
                'cliente_id' => $request->cliente_id,
                'asesor_id' => $request->asesor_id,
                'aval_id' => $request->aval_id,
                'tipo' => $request->tipo,
                'monto' => $request->monto,
                'origen_financiamiento_id' => $request->origen_financiamiento_id,
                'frecuencia' => $request->frecuencia,
                'plazo' => $request->plazo,
                'tasainteres' => $request->tasainteres,
                'costomora' => $request->costomora,
                'total' => $request->total,
                'fecha_reg' => now(),
                'fecha_venc' => $fechaVenc,
                'estado' => 'PENDIENTE',
            ]);

            Cliente::where('id', $request->cliente_id)->update([
                'estado' => 'PENDIENTE',
            ]);

            return response()->json([
                'ok' => 1,
                'msg' => 'Crédito registrado exitosamente',
                'credito' => $credito,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'ok' => 0,
                'msg' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => 0,
                'msg' => 'Error al registrar crédito',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function show(Request $request)
    {
        $credito = Credito::with([
            'cliente:id,estado,persona_id',
            'asesor.user:id,name',
            'cliente.persona:id,dni,ape_pat,ape_mat,primernombre,otrosnombres',
            'cliente.creditos' => function ($query) {
                $query->where('estado', 'DESEMBOLSADO')
                    ->orwhere('estado', 'PAGAR POR RCS');
            },
        ])->where('id', $request->id)->first();
        return $credito;
    }
    public function update(UpdateCreditoRequest $request)
    {

        $monto = $request->monto;


        $fechaInicio = Carbon::now();
        switch (strtoupper($request->frecuencia)) {
            case 'DIARIO':
                $fechaVenc = $fechaInicio->addDays($request->plazo);
                break;

            case 'SEMANAL':
                $fechaVenc = $fechaInicio->addWeeks($request->plazo);
                break;

            case 'QUINCENAL':
                $fechaVenc = $fechaInicio->addDays($request->plazo * 15);
                break;

            case 'MENSUAL':
                $fechaVenc = $fechaInicio->addMonths($request->plazo);
                break;

            default:
                throw new \Exception('Frecuencia no válida');
        }

        $credito = Credito::where('id', $request->id)->first();
        $credito->cliente_id                       = $request->cliente_id;
        $credito->asesor_id                        = $request->asesor_id;
        $credito->aval_id                          = $request->aval_id;
        $credito->tipo                             = $request->tipo;
        $credito->monto                            = $monto;
        $credito->origen_financiamiento_id         = $request->origen_financiamiento_id;
        $credito->frecuencia                       = $request->frecuencia;
        $credito->plazo                            = $request->plazo;
        $credito->tasainteres                      = $request->tasainteres ?? 0.00;
        $credito->costomora                        = $request->costomora;
        $credito->total                            = $request->total ?? 0.00;
        $credito->fecha_venc                       = $fechaVenc;

        $credito->save();


        return response()->json([
            'ok' => 1,
            'mensaje' => 'Credito modificado satisfactoriamente'
        ], 200);
    }
    public function cambiarEstado(Request $request)
    {
        Credito::where('id', $request->id)->update([
            'estado' => $request->estado,
        ]);
        return response()->json([
            'ok' => 1,
            'mensaje' => 'Estado Actualizado'
        ], 200);
    }
    public function obtenerTiposCreditoPorCiente(Request $request)
    {
        $estados = Credito::where('cliente_id', $request->cliente_id)
            ->whereIn('estado', ['DESEMBOLSADO', 'FINALIZADO'])
            ->pluck('estado')
            ->toArray();

        // Obtener instancia del cliente
        $cliente = Cliente::find($request->cliente_id);

        // Verificar si existe y si tiene créditos con mora o vigentes
        if ($cliente && $cliente->creditosVigentesConMora()->isNotEmpty()) {
            return response()->json(['Recurrente Con Saldo', 'Paralelo'], 200);
        }
        if (in_array('DESEMBOLSADO', $estados)) {
            return response()->json(['Recurrente Con Saldo', 'Paralelo'], 200);
        }
        if (in_array('FINALIZADO', $estados)) {
            return response()->json(['Recurrente Sin Saldo'], 200);
        }
        return response()->json(['Nuevo'], 200);
    }
    public function todosTipoCreditos()
    {
        return response()->json([
            'Nuevo',
            'Recurrente Sin Saldo',
            'Recurrente Con Saldo',
            'Paralelo'
        ], 200);
    }
    public function destroy(Request $request)
    {
        $credito = Credito::where('id', $request->id)->first();
        $credito->delete();
        return response()->json([
            'ok' => 1,
            'mensaje' => 'Credito eliminado satisfactoriamente'
        ], 200);
    }
    public function todos()
    {
        $creditos = Credito::get();
        return $creditos;
    }
    public function listar(Request $request)
    {
        $filters = $this->getUserFilters();
        $estado = $request->estado;
        $buscar = mb_strtoupper($request->buscar ?? '');
        $paginacion = is_numeric($request->paginacion) ? $request->paginacion : 10; // Valor por defecto 10
        $query = Credito::with([
            'cliente:id,estado,persona_id',
            'asesor.user:id,name',
            'cliente.persona:id,dni,ape_pat,ape_mat,primernombre,otrosnombres,celular,ruc',
        ]);
        if (!empty($estado)) {
            if (is_array($estado)) {
                $query->whereIn('estado', $estado);
            } else {
                $query->where('estado', $estado);
            }
        }
        if (!empty($buscar)) {
            $query->whereHas('cliente.persona', function ($q) use ($buscar) {
                $q->whereRaw("UPPER(dni) LIKE ?", ["%$buscar%"])
                    ->orWhereRaw("UPPER(ape_pat) LIKE ?", ["%$buscar%"])
                    ->orWhereRaw("UPPER(ape_mat) LIKE ?", ["%$buscar%"])
                    ->orWhereRaw("UPPER(primernombre) LIKE ?", ["%$buscar%"])
                    ->orWhereRaw("UPPER(otrosnombres) LIKE ?", ["%$buscar%"])
                    ->orWhereRaw("UPPER(CONCAT(personas.ape_pat, ' ', personas.ape_mat, ' ', personas.primernombre, ' ', IFNULL(personas.otrosnombres, ''))) LIKE ?", ['%' . $buscar . '%']);;
            })->orwhere('id', $buscar);
        }
        if ($filters['role'] === 'ASESOR') {
            $query->where('asesor_id', $filters['user_id']);
        }
        return $query->orderBy('id', 'DESC')->paginate($paginacion);
    }
    public function cargarEvaluacionAnterior(Request $request)
    {
        $credito_id = $request->credito_id;
        $cliente_id = $request->cliente_id;
        $credito = Credito::with(['analisis', 'balance', 'perdidas', 'propuesta'])
            ->where('cliente_id', $cliente_id)
            ->where('estado', '!=', 'OBSERVADO')
            ->where('estado', '!=', 'PENDIENTE')
            ->orderByDesc('id')
            ->first();
        if (!$credito) {
            return response()->json(['error' => 'No se encontró un crédito válido'], 404);
        }
        $relaciones = [
            'analisis' => AnalisisCualitativo::class,
            'balance' => Balance::class,
            'perdidas' => PerdidaGanancia::class,
            'propuesta' => PropuestaCredito::class,
        ];
        foreach ($relaciones as $relacion => $modelo) {
            if ($credito->$relacion) {
                $modelo::where('credito_id', $credito_id)->delete();
                $nuevoRegistro = $credito->$relacion->replicate();
                $nuevoRegistro->credito_id = $credito_id;
                $nuevoRegistro->save();
            }
            if ($relacion === 'balance') {
                $this->replicarHijosBalance($credito->$relacion, $credito_id);
            }
            if ($relacion === 'perdidas') {
                $this->replicarHijosPerdidas($credito->$relacion, $credito_id);
            }
        }
        return response()->json([
            'ok' => 1,
            'mensaje' => 'Evaluación anterior copiada correctamente',
        ], 200);
    }
    public function validarParaEvaluacion(Request $request)
    {
        $solicitud = Credito::where('id', $request->id)->first();
        if (!$solicitud) {
            return response()->json([
                'ok' => 0,
                'mensaje' => 'Solicitud no encontrada',
            ], 404);
        }
        if ($solicitud->tieneTodosLosRegistros()) {
            $rsta = 1;
        } else {
            $rsta = 0;
        }
        return response()->json([
            'ok' => $rsta,
            'mensaje' => 'Validacion Realizada',
        ], 200);
    }
    public function generarPDF(Request $request)
    {
        $tipo = $request->tipo;
        $id = $request->credito_id;
        if ($tipo == 'solicitud') {
            $credito = Credito::with([
                'cliente:id,persona_id,estado,aval_id',
                'cliente.persona:id,dni,ape_pat,ape_mat,primernombre,otrosnombres,genero,fecha_nac,nacionalidad,grado_instr,estado_civil,tipo_trabajador,ubicacion_domicilio_id,conyugue,celular',
                'asesor:id,dni,name',
                'asesor.persona:id,dni,ape_pat,ape_mat,primernombre,otrosnombres',
                'cliente.persona.ubicacion:id,tipo,ubigeo,tipovia,nombrevia,nro,interior,mz,lote,tipozona,nombrezona,referencia',
                'cliente.persona.ubicacion.distrito:id,ubigeo,nombre,provincia_id',
                'cliente.persona.ubicacion.distrito.provincia:id,nombre,departamento_id',
                'cliente.persona.ubicacion.distrito.provincia.departamento:id,nombre',
                'cliente.persona.conyugePersona:id,id,dni,ape_pat,ape_mat,primernombre,otrosnombres',
                'cliente.negocio:id,cliente_id,razonsocial,ruc,tel_cel,tipo_actividad_id,descripcion,inicioactividad,ubicacion_id',
                'cliente.negocio.ubicacion:id,tipo,ubigeo,tipovia,nombrevia,nro,interior,mz,lote,tipozona,nombrezona,referencia',
                'cliente.negocio.ubicacion.distrito:id,ubigeo,nombre,provincia_id',
                'cliente.negocio.ubicacion.distrito.provincia:id,nombre,departamento_id',
                'cliente.negocio.ubicacion.distrito.provincia.departamento:id,nombre',
                'cliente.aval:id,dni,ape_pat,ape_mat,primernombre,otrosnombres',
            ])
                ->where('id', $id)->first();

            $data = [
                'credito'           => $credito,
                'cliente'           => $credito->cliente,
                'domicilio'         => $credito->cliente->persona->ubicacion,
                'asesor'            => $credito->asesor,
                'negocio'           => $credito->cliente->negocio,
                'ubicacionnegocio'  => optional($credito->cliente->negocio)->ubicacion,
                'conyugue'          =>  $credito->cliente->persona->conyugePersona,
                'aval'              =>  $credito->cliente->aval,
            ];

            $pdf = Pdf::loadView('pdfs/solicitud', $data);
        } elseif ($tipo == 'Estados Financieros') {
            $credito = Credito::with([
                'cliente:id,persona_id,estado',
                'cliente.persona:id,dni,ape_pat,ape_mat,primernombre,otrosnombres,genero,fecha_nac,nacionalidad,grado_instr,estado_civil,tipo_trabajador,ubicacion_domicilio_id,celular',
                'asesor.persona:id,dni,ape_pat,ape_mat,primernombre,otrosnombres',
                'balance:credito_id,total_activo,total_pasivo,patrimonio,activocaja,activobancos,activoctascobrar,activoinventarios,pasivodeudaprove,pasivodeudaent,pasivodeudaempre,activomueble,activootrosact,activodepre,pasivolargop,otrascuentaspagar,totalacorriente,totalpcorriente,totalancorriente,totalpncorriente,fecha',
                'perdidas:credito_id,ventas,costo,utilidad,costonegocio,utiloperativa,otrosing,otrosegr,gast_fam,utilidadneta,utilnetdiaria',
                'perdidas.venta:credito_id,tot_ing_mensual,tot_cosprimo_m,margen_tot,ventas_cred,irrecuperable,cantproductos',
                'perdidas.venta.detalles',
                'perdidas.gastosnegocio:credito_id,alquiler,servicios,personal,sunat,transporte,gastosfinancieros,otros',
                'perdidas.gastosfamiliar:credito_id,alimentacion,alquileres,educacion,servicios,transporte,salud,otros',
            ])
                ->where('id', $id)->first();
            $data = [
                'credito'           => $credito,
                'cliente'           => $credito->cliente,
                'balance'           => $credito->balance,
                'perdidasganancias' => $credito->perdidas,
                'ventaspyg'         => $credito->perdidas->venta,
                'detventas_pyg'     => $credito->perdidas->venta?->detalles,
                'gastosnegocios'    => $credito->perdidas->gastosnegocio,
                'gastosfamiliares'    => $credito->perdidas->gastosfamiliar,
                'ventaspyg_anterior' => $credito->creditoAnterior()?->perdidas,

            ];
            //return $data['gastos'];
            $pdf = Pdf::loadView('pdfs/estadosfinancieros', $data);
        } elseif ($tipo == 'Analisis Cualitativo') {
            $credito = Credito::with([
                'cliente:id,persona_id,estado',
                'cliente.persona:id,dni,ape_pat,ape_mat,primernombre,otrosnombres,genero,fecha_nac,nacionalidad,grado_instr,estado_civil,tipo_trabajador,ubicacion_domicilio_id',
                'analisis:credito_id,tipogarantia,cargafamiliar,riesgoedadmax,antecedentescred,recorpagoult,niveldesarr,tiempo_neg,control_ingegre,vent_totdec,compsubsector,totunidfamiliar,totunidempresa,total'

            ])
                ->where('id', $id)->first();
            $data = [
                'analisiscualitativo'           => $credito->analisis,
            ];
            $pdf = Pdf::loadView('pdfs/analisis', $data);
        } elseif ($tipo == 'Seguro') {
            $credito = Credito::with([
                'cliente:id,persona_id,estado',
                'cliente.persona:id,dni,ape_pat,ape_mat,primernombre,otrosnombres,genero,fecha_nac,nacionalidad,grado_instr,estado_civil,tipo_trabajador,ubicacion_domicilio_id',
                'seguro:credito_id,monto'

            ])
                ->where('id', $id)->first();
            $data = [
                'cliente'           => $credito->cliente,
                'credito'           => $credito,
                'poliza'           => $credito->seguro,
            ];
            $pdf = Pdf::loadView('pdfs/seguro', $data);
        } elseif ($tipo == 'Propuesta') {
            $credito = Credito::with([
                'cliente:id,persona_id,estado',
                'cliente.persona:id,dni,ape_pat,ape_mat,primernombre,otrosnombres,genero,fecha_nac,nacionalidad,grado_instr,estado_civil,tipo_trabajador,ubicacion_domicilio_id',
                'propuesta:credito_id,unidad_familiar,experiencia_cred,destino_prest,referencias'
            ])
                ->where('id', $id)->first();
            $data = [
                'cliente'           => $credito->cliente,
                'credito'           => $credito,
                'propuesta'         => $credito->propuesta,
            ];
            $pdf = Pdf::loadView('pdfs/propuestacredito', $data);
        }
        return Response::make($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="documento.pdf"',
        ]);
    }
    public function obtenerDatosCreditoEvaluar(Request $request)
    {
        $credito = Credito::query()
            ->with([
                'cliente:id,estado,persona_id',
                'agencia:id,nombre',
                'asesor:id,name',
                'cliente.persona:id,dni,ape_pat,ape_mat,primernombre,otrosnombres',
            ])
            ->findOrFail($request->id);

        $desembolsosCancelar = CreditosCancelar::query()
            ->with(['desembolso']) // incluye 'id' (clave primaria) + lo que necesite tu método
            ->where('credito_id', $request->id)
            ->limit(4)
            ->get();

        $detalles = $desembolsosCancelar->map(function ($row) {
            if (!$row->desembolso) return null;

            $resumen   = $row->desembolso->getResumenPagosByCredito();
            $saldo     = (float) ($resumen->Saldo ?? 0);
            $costoMora = (float) ($resumen->costomora ?? 0);
            $retraso   = (float) ($resumen->retraso ?? 0);
            $moraPag   = (float) ($resumen->saldo_mora ?? 0);
            $moraTot   = $moraPag + ($retraso * $costoMora);

            return [
                'id'         => $resumen->id ?? null,
                'fecha_reg'  => $resumen->fecha_reg ?? null,
                'monto'      => (float) ($resumen->monto ?? 0),
                'frecuencia' => $resumen->frecuencia ?? null,
                'total'      => (float) ($resumen->total ?? 0),
                'saldopagar' => $saldo,
                'tipo'       => $resumen->tipo ?? null,
                'costomora'  => $costoMora,
                'moradias'   => (int) ($resumen->saldo_mora_dias ?? 0),
                'morapagar'  => $moraPag,
                'retraso'    => $retraso,
                'estado'     => $resumen->estado ?? null,
                'mencion'    => $resumen->mencion ?? null,
                '_mora_total_item' => $moraTot,
            ];
        })->filter();

        $total_saldo = round($detalles->sum('saldopagar'), 2);
        $total_mora  = round($detalles->sum('_mora_total_item'), 2);

        // ✅ reutiliza el cliente ya cargado (no hagas otra consulta)
        $registrosdeudas = $credito->cliente
            ? $credito->cliente->creditosVigentesConMora()
            : collect(); // por seguridad, si no hubiese cliente

        return response()->json([
            'credito' => $credito,
            'creditos_cancelar' => $detalles->map(function ($d) {
                unset($d['_mora_total_item']);
                return $d;
            })->values(),
            'deuda' => [
                'total_saldo' => $total_saldo,
                'total_mora'  => $total_mora,
                'total_deuda' => round($total_saldo + $total_mora, 2),
            ],
            'vigentes_con_mora' => $registrosdeudas,
        ], 200);
    }
    public function obtenerSolicitudesCancelar(Request $request)
    {
        $desembolsos = CreditosCancelar::with('desembolso')
            ->where('credito_id', $request->credito_id)
            ->get();
        $detalles = [];
        $total_saldo = 0;
        $total_mora = 0;
        foreach ($desembolsos as $row) {
            $resumen = $row->desembolso->getResumenPagosByCredito();

            $fila = [
                'id'          => $resumen->id,
                'fecha_reg'   => $resumen->fecha_reg,
                'monto'       => $resumen->monto,
                'frecuencia'  => $resumen->frecuencia,
                'total'       => $resumen->total,
                'saldopagar'  => $resumen->Saldo,
                'tipo'        => $resumen->tipo,
                'costomora'   => $resumen->costomora,
                'moradias'    => $resumen->saldo_mora_dias,
                'morapagar'   => $resumen->saldo_mora,
                'retraso'     => $resumen->retraso,
                'estado'      => $resumen->estado,
                'mencion'     => $resumen->mencion,
            ];

            $total_saldo += $fila['saldopagar'];
            $total_mora += round(floatval($fila['morapagar']) + floatval($resumen->retraso * $resumen->costomora), 2);
            $detalles[] = $fila;
        }

        return response()->json([
            'solicitudes' => $detalles,
            'deuda' => [
                'total_saldo' => $total_saldo,
                'total_mora'  => $total_mora,
                'total_deuda' => $total_saldo + $total_mora,
            ],
        ]);
    }

    public function cronograma(Request $request)
    {
        return response()->json(CronogramaPago::where('credito_id', $request->credito_id)->orderBy('nrocuota')->get());
    }
}
