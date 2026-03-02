<?php

namespace App\Http\Requests\KardexCredito;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKardexCreditoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'credito_id'  => ['required', 'integer', 'exists:creditos,id'],
            'nro'         => ['required', 'integer', 'min:1'],
            'montopagado' => ['required', 'numeric', 'min:0.01'],
            'mediopago'   => ['required', Rule::in(['EFECTIVO', 'TRANSFERENCIA', 'DEPOSITO'])],

            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.cronograma_id'  => ['required', 'integer'],
            'detalles.*.capital_pagado' => ['nullable', 'numeric', 'min:0'],
            'detalles.*.interes_pagado' => ['nullable', 'numeric', 'min:0'],
            'detalles.*.mora_pagada'    => ['nullable', 'numeric', 'min:0'],
            'detalles.*.observacion'    => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'detalles.required' => 'Debe enviar al menos un detalle.',
            'detalles.min'      => 'Debe enviar al menos un detalle.',
        ];
    }
}
