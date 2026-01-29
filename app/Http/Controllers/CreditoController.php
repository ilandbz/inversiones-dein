<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Credito;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CreditoController extends Controller
{
    public function store(Request $request)
    {

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

        try {
            $request->validate([
                'cliente_id' => 'required|exists:clientes,id',
                'asesor_id' => 'required|exists:asesors,id',
                'aval_id' => 'nullable|exists:personas,id',
                'tipo' => 'required',
                'monto' => 'required|numeric|min:0',
                'origen_financiamiento_id' => 'required|exists:origen_financiamientos,id',
                'frecuencia' => 'required|in:DIARIO,SEMANAL,QUINCENAL,MENSUAL',
                'plazo' => 'required|integer|min:1',
                'tasainteres' => 'required|numeric|min:0',
                'costomora' => 'required|numeric|min:0',
                'total' => 'required|numeric|min:0',
            ]);

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
}
