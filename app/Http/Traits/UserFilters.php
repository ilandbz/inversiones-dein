<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;

trait UserFilters
{
    public function getUserFilters()
    {
        return [
            'role' => auth()->user()->role->nombre,
            'role_id' => auth()->user()->role->id,
            'user_id' => auth()->user()->id,
        ];
    }
}