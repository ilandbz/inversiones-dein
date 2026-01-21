<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrupoMenu extends Model
{
    protected $fillable =['titulo'];

    use HasFactory;

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class, 'grupo_menu_id');
    }
}
