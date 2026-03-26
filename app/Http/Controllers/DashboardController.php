<?php

namespace App\Http\Controllers;

use App\Models\Credito;
use App\Models\Ahorro;
use App\Models\Caja;
use App\Models\AuditoriaLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Estadísticas de Cartera
        $totalCartera = Credito::whereIn('estado', ['DESEMBOLSADO', 'DORMIDO', 'ACTIVO'])->sum('monto');
        $creditosMora = Credito::where('estado', 'VENCIDO')->count();
        
        // 2. Ahorros
        $totalAhorros = Ahorro::where('estado', 'ACTIVO')->sum('saldo');
        
        // 3. Caja
        $cajaAbierta = Caja::where('estado', 'ABIERTO')->first();
        $saldoCaja = $cajaAbierta ? $cajaAbierta->saldo_final : 0;

        // 4. Actividad Reciente (Auditoría)
        $actividad = AuditoriaLog::with('user')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'stats' => [
                'total_cartera' => (float)$totalCartera,
                'total_ahorros' => (float)$totalAhorros,
                'saldo_caja' => (float)$saldoCaja,
                'creditos_mora' => $creditosMora,
                'caja_abierta' => (bool)$cajaAbierta
            ],
            'actividad' => $actividad
        ]);
    }
}
