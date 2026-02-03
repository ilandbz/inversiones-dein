<?php

namespace App\Http\Controllers;

use App\Http\Requests\Balance\StoreUpdateBalanceRequest;
use App\Models\Balance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BalanceController extends Controller
{

    public function store(StoreUpdateBalanceRequest $request)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {
            // si es 1 balance por crédito
            $balance = Balance::updateOrCreate(
                ['credito_id' => $data['credito_id']],
                $this->onlyBalanceFields($data)
            );

            // 1–1 inventario
            if (isset($data['detinventarios'])) {
                $balance->inventario()->updateOrCreate(
                    ['balance_id' => $balance->id],
                    $data['detinventarios']
                );
            }

            // 1–N sync muebles
            $this->syncHasMany($balance->muebles(), $data['muebles'] ?? []);

            // 1–N sync deudas
            $this->syncHasMany($balance->deudas(), $data['deudas'] ?? []);

            return response()->json([
                'ok' => 1,
                'balance' => $balance->load(['inventario', 'muebles', 'deudas']),
            ], 200);
        });
    }

    public function update(StoreUpdateBalanceRequest $request, Balance $balance)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($balance, $data) {
            $balance->update($this->onlyBalanceFields($data));

            if (isset($data['detinventarios'])) {
                $balance->inventario()->updateOrCreate(
                    ['balance_id' => $balance->id],
                    $data['detinventarios']
                );
            }

            $this->syncHasMany($balance->muebles(), $data['muebles'] ?? []);
            $this->syncHasMany($balance->deudas(), $data['deudas'] ?? []);

            return response()->json([
                'ok' => 1,
                'balance' => $balance->load(['inventario', 'muebles', 'deudas']),
            ], 200);
        });
    }

    private function onlyBalanceFields(array $data): array
    {
        return collect($data)->only([
            'credito_id',
            'fecha',
            'estado',
            'activocaja',
            'activobancos',
            'activoctascobrar',
            'activoinventarios',
            'activomueble',
            'activootrosact',
            'activodepre',
            'pasivodeudaprove',
            'pasivodeudaent',
            'pasivolargop',
            'otrascuentaspagar',
            'totalacorriente',
            'totalancorriente',
            'total_activo',
            'totalpcorriente',
            'totalpncorriente',
            'total_pasivo',
            'patrimonio',
            'paspatrimonio',
            'captrabajo',
        ])->toArray();
    }

    /**
     * Sync genérico para hasMany:
     * - crea/actualiza por id
     * - elimina los que no están en payload
     */
    private function syncHasMany($relation, array $items): void
    {
        $idsEnviados = collect($items)
            ->pluck('id')
            ->filter()
            ->values()
            ->all();

        // Borra los que ya no vienen
        $relation->whereNotIn('id', $idsEnviados)->delete();

        foreach ($items as $row) {
            $id = $row['id'] ?? null;
            $payload = collect($row)->except('id')->toArray();

            if ($id) {
                $relation->where('id', $id)->update($payload);
            } else {
                // evita filas vacías
                if (count(array_filter($payload, fn($v) => $v !== null && $v !== '')) > 0) {
                    $relation->create($payload);
                }
            }
        }
    }
}
