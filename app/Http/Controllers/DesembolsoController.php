<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\Desembolso\StoreDesembolsoRequest;
use App\Http\Requests\Desembolso\UpdateDesembolsoRequest;
use App\Models\Desembolso;
use App\Models\Credito;
use App\Models\Cliente;
use App\Models\CronogramaPago;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Traits\UserFilters;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

class DesembolsoController extends Controller
{
    use UserFilters;
    public function store(StoreDesembolsoRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $desembolso = Desembolso::create([
                    'credito_id'     => $request->credito_id,
                    'fecha'          => $request->fecha,
                    'hora'           => $request->hora,
                    'user_id'        => $request->user_id,
                    'descontado'     => $request->descontado,
                    'totalentregado' => $request->totalentregado,
                ]);
                $credito = Credito::findOrFail($request->credito_id);
                $credito->update(['estado' => 'DESEMBOLSADO']);
                Cliente::where('id', $credito->cliente_id)->update(['estado' => 'VIGENTE']);

                // Generar Cronograma de Pagos
                $monto = $credito->monto;
                $total = $credito->total;
                $plazo = $credito->plazo;
                $frecuencia = $credito->frecuencia;

                $cuota_capital = round($monto / $plazo, 2);
                $cuota_interes = round(($total - $monto) / $plazo, 2);
                $cuota_total = round($total / $plazo, 2);

                $fecha = Carbon::parse($request->fecha);

                for ($i = 1; $i <= $plazo; $i++) {
                    switch ($frecuencia) {
                        case 'DIARIO':
                            $fecha->addDay();
                            break;
                        case 'SEMANAL':
                            $fecha->addWeek();
                            break;
                        case 'QUINCENAL':
                            $fecha->addDays(15);
                            break;
                        case 'MENSUAL':
                            $fecha->addMonth();
                            break;
                    }

                    CronogramaPago::create([
                        'credito_id'        => $credito->id,
                        'nrocuota'      => $i,
                        'fecha_prog' => $fecha->toDateString(),
                        'nombredia' => $fecha->dayName,
                        'cuota'           => $cuota_total,
                        'saldo'             => $monto - ($cuota_capital * $i),
                    ]);
                }
                return response()->json([
                    'ok' => 1,
                    'msg' => 'Desembolso realizado exitosamente',
                    'desembolso' => $desembolso
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'ok' => 0,
                'msg' => 'Error al realizar el desembolso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request)
    {
        $desembolso = Desembolso::with(['credito.cliente.persona', 'user'])->findOrFail($request->id);
        return response()->json($desembolso);
    }

    public function update(UpdateDesembolsoRequest $request)
    {
        try {
            $desembolso = Desembolso::findOrFail($request->id);
            $desembolso->update([
                'credito_id'     => $request->credito_id,
                'fecha'          => $request->fecha,
                'hora'           => $request->hora,
                'user_id'        => $request->user_id,
                'descontado'     => $request->descontado,
                'totalentregado' => $request->totalentregado,
            ]);

            return response()->json([
                'ok' => 1,
                'msg' => 'Desembolso actualizado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => 0,
                'msg' => 'Error al actualizar el desembolso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $desembolso = Desembolso::findOrFail($request->id);
            $desembolso->delete();

            return response()->json([
                'ok' => 1,
                'msg' => 'Desembolso eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => 0,
                'msg' => 'Error al eliminar el desembolso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function todos()
    {
        return response()->json(Desembolso::all());
    }

    public function listar(Request $request)
    {
        $buscar = mb_strtoupper($request->buscar ?? '');
        $paginacion = is_numeric($request->paginacion) ? $request->paginacion : 10;

        $query = Desembolso::with(['credito.cliente.persona', 'user']);

        if (!empty($buscar)) {
            $query->whereHas('credito.cliente.persona', function ($q) use ($buscar) {
                $q->whereRaw("UPPER(dni) LIKE ?", ["%$buscar%"])
                    ->orWhereRaw("UPPER(ape_pat) LIKE ?", ["%$buscar%"])
                    ->orWhereRaw("UPPER(ape_mat) LIKE ?", ["%$buscar%"])
                    ->orWhereRaw("UPPER(primernombre) LIKE ?", ["%$buscar%"])
                    ->orWhereRaw("UPPER(otrosnombres) LIKE ?", ["%$buscar%"]);
            })->orWhere('id', 'LIKE', "%$buscar%");
        }

        return $query->orderBy('id', 'DESC')->paginate($paginacion);
    }

    public function generarPDF(Request $request)
    {
        $tipo = $request->tipo;
        $credito_id = $request->credito_id;
        $filters = $this->getUserFilters();
        $orientacion = 'portrait';
        if ($tipo == 'calendario') {
            $credito = Credito::with([
                'asesor.user:id,name,dni',
                'asesor.user.persona:id,dni,ape_pat,ape_mat,primernombre,otrosnombres,celular',
                'cliente.persona:id,dni,ape_pat,ape_mat,primernombre,otrosnombres,direccion',
                'desembolso:credito_id,fecha,totalentregado,descontado',
                'desembolso.cronograma:credito_id,nrocuota,fecha_prog,nombredia,cuota,saldo',
            ])->where('id', $credito_id)->first();
            $data = [
                'prestamo'          => $credito,
                'cliente'           => $credito->cliente->persona,
                'asesor'            => $credito->asesor->user->persona,
                'desembolso'        => $credito->desembolso,
                'cuotapagos'        => $credito->desembolso->cronograma,
            ];
            if ($credito->frecuencia != 'SEMANAL' && $credito->frecuencia != 'QUINCENAL' && $credito->frecuencia != 'MENSUAL') {
                $orientacion = 'landscape';
            }

            $pdf = Pdf::loadView('pdf/cronogramapagos', $data)->setPaper('a4', $orientacion);
        } elseif ($tipo == 'plan') {
            $desembolso = Desembolso::with([
                'credito:id,monto,frecuencia,estado,asesor_id,total,cliente_id,fecha_reg,tasainteres,plazo,costomora,agencia_id',
                'credito.agencia:id,nombre,direccion,telefono',
                'credito.asesor:id,name,dni',
                'credito.asesor.persona',
                'credito.cliente:id,agencia_id,usuario_id,persona_id',
                'credito.cliente.persona:id,dni,ape_pat,ape_mat,primernombre,otrosnombres,ubicacion_domicilio_id',
                'vistaPagos:credito_id,nrocuota,cuota,nombredia,fecha_prog,saldooriginal,fechapagado,montopagado,moradias,user_id,resta,mediopago',
            ])->where('credito_id', $credito_id)->first();

            $fechamaxpago = $desembolso->ultimoRegistroCalendario()->fecha_prog;
            $fchapagadocompleto = $desembolso->ultimoRegistroPagadoCompleto()?->fechapagado;

            $reg = $desembolso->cuotaDebe();
            if ($reg) {
                $fechacuotadebe = $reg->fecha_prog;

                $diastrasados = $this->calcularDiasAtrasados($desembolso->credito->frecuencia, $fechamaxpago, $fchapagadocompleto, $fechacuotadebe, $desembolso->credito->agencia_id, $credito_id);
                // $diastrasados = $this->calcularDiasAtrasados($desembolso->credito->frecuencia, $fechamaxpago, $fchapagadocompleto, $fechacuotadebe, $filters['agencia_id']);
            } else {
                $diastrasados = 0;
            }

            $data = [
                'desembolso'        => $desembolso,
                'credito'           => $desembolso->credito,
                'cliente'           => $desembolso->credito->cliente,
                'agencia'           => $desembolso->credito->agencia,
                'asesor'            => $desembolso->credito->asesor,
                'cuotaspago'        => $desembolso->vistaPagos,
                'diasretraso'       => $diastrasados,
                'moradias'          => $desembolso->sumamora(),
                'moradiaspagado'    => $desembolso->sumapagomoradias(),
            ];
            $pdf = Pdf::loadView('pdfs/plan', $data);
        } elseif ($tipo == 'kardex') {
            $desembolso = Desembolso::with([
                'credito:id,monto,frecuencia,estado,asesor_id,total,cliente_id,fecha_reg,tasainteres,plazo',
                'credito.agencia:id,nombre,direccion,telefono',
                'credito.asesor:id,name,dni',
                'credito.asesor.persona',
                'credito.cliente:id,agencia_id,usuario_id,persona_id',
                'credito.cliente.persona:id,dni,ape_pat,ape_mat,primernombre,otrosnombres,ubicacion_domicilio_id',
                'kardex',
                'kardex.usuario',
            ])->where('credito_id', $credito_id)->first();

            $data = [
                'desembolso'        => $desembolso,
                'credito'           => $desembolso->credito,
                'cliente'           => $desembolso->credito->cliente,
                'agencia'           => $desembolso->credito->agencia,
                'asesor'            => $desembolso->credito->asesor,
                'kardex'            => $desembolso->kardex,


            ];
            $pdf = Pdf::loadView('pdfs/kardex', $data);
        } else {
            $desembolso = Desembolso::with([
                'credito:id,monto,frecuencia,estado,asesor_id,total,cliente_id,fecha_reg,tasainteres,plazo',
                'credito.agencia:id,nombre,direccion,telefono',
                'credito.asesor:id,name,dni',
                'credito.asesor.persona',
                'credito.cliente:id,agencia_id,usuario_id,persona_id',
                'credito.cliente.persona:id,dni,ape_pat,ape_mat,primernombre,otrosnombres,ubicacion_domicilio_id',
                'pagosmora:credito_id,nro,diasmora,total,fecha,hora,user_id',
                'pagosmora.user:id,name',
            ])->where('credito_id', $credito_id)->first();
            $data = [
                'desembolso'        => $desembolso,
                'credito'           => $desembolso->credito,
                'cliente'           => $desembolso->credito->cliente,
                'agencia'           => $desembolso->credito->agencia,
                'asesor'            => $desembolso->credito->asesor,
                'pagosmora'         => $desembolso->pagosmora,

            ];
            $pdf = Pdf::loadView('pdfs/pagosmora', $data);
        }
        // return $pdf->stream('documento.pdf');
        return Response::make($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="calendario.pdf"',
        ]);
    }
}
