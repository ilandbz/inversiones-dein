<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Desembolso\StoreDesembolsoRequest;
use App\Http\Traits\UserFilters;
use App\Models\Credito;
use App\Models\CronogramaPago;
use App\Models\Desembolso;
use App\Services\DesembolsoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class DesembolsoController extends Controller
{
    use UserFilters;

    public function __construct(
        private readonly DesembolsoService $desembolsoService
    ) {}

    // ──────────────────────────────────────────────────────────────────────────
    // Desembolsar crédito aprobado
    // ──────────────────────────────────────────────────────────────────────────

    public function store(StoreDesembolsoRequest $request): JsonResponse
    {
        try {
            $desembolso = $this->desembolsoService->desembolsar($request->validated());

            return response()->json([
                'ok'         => 1,
                'msg'        => 'Desembolso realizado exitosamente',
                'desembolso' => $desembolso,
            ]);
        } catch (\LogicException $e) {
            return response()->json(['ok' => 0, 'msg' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'ok'    => 0,
                'msg'   => 'Error al realizar el desembolso',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request): JsonResponse
    {
        $desembolso = Desembolso::with(['credito.cliente.persona', 'user'])
            ->findOrFail($request->id);

        return response()->json($desembolso);
    }

    public function listar(Request $request): mixed
    {
        $buscar     = mb_strtoupper($request->buscar ?? '');
        $perPage    = is_numeric($request->paginacion) ? (int) $request->paginacion : 15;

        $query = Desembolso::with(['credito.cliente.persona', 'user']);

        if ($buscar !== '') {
            $query->whereHas('credito.cliente.persona', fn($q) => $q
                ->whereRaw('UPPER(dni) LIKE ?', ["%$buscar%"])
                ->orWhereRaw('UPPER(ape_pat) LIKE ?', ["%$buscar%"])
                ->orWhereRaw('UPPER(ape_mat) LIKE ?', ["%$buscar%"])
                ->orWhereRaw('UPPER(primernombre) LIKE ?', ["%$buscar%"])
            );
        }

        return $query->orderByDesc('id')->paginate($perPage);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PDF: calendario, plan de pagos, kardex, mora
    // ──────────────────────────────────────────────────────────────────────────

    public function generarPDF(Request $request): mixed
    {
        $tipo       = $request->tipo;
        $credito_id = $request->credito_id;
        $filters    = $this->getUserFilters();
        $orientacion = 'portrait';

        if ($tipo === 'calendario') {
            $credito = Credito::with([
                'asesor.user:id,name,dni',
                'asesor.user.persona:id,dni,ape_pat,ape_mat,primernombre,otrosnombres,celular',
                'cliente.persona:id,dni,ape_pat,ape_mat,primernombre,otrosnombres,direccion',
                'desembolso:credito_id,fecha,totalentregado,descontado',
                'desembolso.cronograma:credito_id,nrocuota,fecha_prog,nombredia,cuota,saldo',
            ])->findOrFail($credito_id);

            if (!in_array($credito->frecuencia, ['SEMANAL', 'QUINCENAL', 'MENSUAL'], true)) {
                $orientacion = 'landscape';
            }

            $pdf = Pdf::loadView('pdf/cronogramapagos', [
                'prestamo'   => $credito,
                'cliente'    => $credito->cliente->persona,
                'asesor'     => $credito->asesor->user->persona,
                'desembolso' => $credito->desembolso,
                'cuotapagos' => $credito->desembolso->cronograma,
            ])->setPaper('a4', $orientacion);
        } else {
            $desembolso = Desembolso::with([
                'credito:id,monto,frecuencia,estado,asesor_id,total,cliente_id,fecha_reg,tasainteres,plazo,costomora,agencia_id,mencion',
                'credito.agencia:id,nombre,direccion,telefono',
                'credito.asesor.user:id,name',
                'credito.asesor.user.persona',
                'credito.cliente.persona',
                'kardex',
                'kardex.user:id,name',
            ])->where('credito_id', $credito_id)->firstOrFail();

            $viewName = match ($tipo) {
                'plan'   => 'pdfs/plan',
                'kardex' => 'pdfs/kardex',
                default  => 'pdfs/pagosmora',
            };

            $pdf = Pdf::loadView($viewName, [
                'desembolso' => $desembolso,
                'credito'    => $desembolso->credito,
                'cliente'    => $desembolso->credito->cliente,
                'agencia'    => $desembolso->credito->agencia,
                'asesor'     => $desembolso->credito->asesor,
            ]);
        }

        return Response::make($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="documento.pdf"',
        ]);
    }
}
