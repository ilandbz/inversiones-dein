<?php

namespace Database\Seeders;

use App\Models\GrupoMenu;
use App\Models\Menu;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
DB::transaction(function () {

            $data = [
                [
                    "title" => "Principal",
                    "items" => [
                        ["name" => "Dashboard", "key" => "/", "icon" => "speedometer2", "url" => "/"],
                    ],
                ],
                [
                    "title" => "Operaciones",
                    "items" => [
                        [
                            "name" => "Préstamos",
                            "key" => "prestamos",
                            "icon" => "cash-stack",
                            "submenu" => [
                                ["name" => "Registrar Préstamo", "key" => "registrar-prestamo", "url" => "/prestamos/registrar"],
                                ["name" => "Simulación", "key" => "simulacion", "url" => "/prestamos/simulacion"],
                                ["name" => "Cronograma de Pagos", "key" => "cronograma-de-pagos", "url" => "/prestamos/cronograma-de-pagos"],
                                ["name" => "Historial de Préstamos", "key" => "historial-de-prestamos", "url" => "/prestamos/historial-de-prestamos"],
                            ],
                        ],
                        [
                            "name" => "Ahorros",
                            "key" => "ahorros",
                            "icon" => "piggy-bank",
                            "submenu" => [
                                ["name" => "Apertura de Cuenta", "key" => "apertura-de-cuenta", "url" => "/ahorros/apertura-de-cuenta"],
                                ["name" => "Depósitos / Retiros", "key" => "depositos-retiros", "url" => "/ahorros/depositos-retiros"],
                                ["name" => "Estado de Cuenta", "key" => "estado-de-cuenta", "url" => "/ahorros/estado-de-cuenta"],
                            ],
                        ],
                        [
                            "name" => "Caja",
                            "key" => "caja",
                            "icon" => "safe-fill",
                            "submenu" => [
                                ["name" => "Cobros", "key" => "cobros", "url" => "/caja/cobros"],
                                ["name" => "Pagos", "key" => "pagos", "url" => "/caja/pagos"],
                                ["name" => "Cierre de Caja", "key" => "cierre-de-caja", "url" => "/caja/cierre-de-caja"],
                            ],
                        ],
                    ],
                ],
                [
                    "title" => "Gestión Comercial",
                    "items" => [
                        [
                            "name" => "Asesores",
                            "key" => "asesores",
                            "icon" => "people-fill",
                            "submenu" => [
                                ["name" => "Metas", "key" => "metas", "url" => "/asesores/metas"],
                                ["name" => "Cartilla de Cobranza", "key" => "cartilla-de-cobranza", "url" => "/asesores/cartilla-de-cobranza"],
                                ["name" => "Histórico del Asesor", "key" => "historico-del-asesor", "url" => "/asesores/historico-del-asesor"],
                            ],
                        ],
                        [
                            "name" => "Pagos",
                            "key" => "pagos",
                            "icon" => "people-fill",
                            "submenu" => [
                                ["name" => "Reportes", "key" => "reportes", "url" => "/pagos/reportes"],
                                ["name" => "Estadísticas de Ingresos", "key" => "estadisticas-de-ingresos", "url" => "/pagos/estadisticas-de-ingresos"],
                            ],
                        ],
                        [
                            "name" => "Clientes",
                            "key" => "clientes",
                            "icon" => "person-badge-fill",
                            "submenu" => [
                                ["name" => "Registro de Clientes", "key" => "registro-de-clientes", "url" => "/clientes/registro-de-clientes"],
                                ["name" => "Posición del Cliente", "key" => "posicion-del-cliente", "url" => "/clientes/posicion-del-cliente"],
                                ["name" => "Historial del Cliente", "key" => "historial-del-cliente", "url" => "/clientes/historial-del-cliente"],
                            ],
                        ],
                        [
                            "name" => "Actividad de Negocio",
                            "key" => "actividad-de-negocio",
                            "icon" => "person-badge-fill",
                            "url" => "/actividadnegocio",
                        ],
                    ],
                ],
                [
                    "title" => "Control y Riesgos",
                    "items" => [
                        [
                            "name" => "Gestión de Riesgos",
                            "key" => "riesgos",
                            "icon" => "exclamation-triangle-fill",
                            "submenu" => [
                                ["name" => "Mora y Castigos", "key" => "mora-y-castigos", "url" => "/riesgos/mora-y-castigos"],
                                ["name" => "Bloqueo de Pagos", "key" => "bloqueo-de-pagos", "url" => "/riesgos/bloqueo-de-pagos"],
                            ],
                        ],
                        [
                            "name" => "Gerencia",
                            "key" => "gerencia",
                            "icon" => "shield-lock-fill",
                            "submenu" => [
                                ["name" => "Reportes Gerenciales", "key" => "reportes-gerenciales", "url" => "/gerencia/reportes-gerenciales"],
                                ["name" => "Autorizaciones", "key" => "autorizaciones", "url" => "/gerencia/autorizaciones"],
                            ],
                        ],
                    ],
                ],
            ];

            $createdMenuIds = [];

            foreach ($data as $gIndex => $grupo) {
                $grupoModel = GrupoMenu::firstOrCreate(
                    ['titulo' => $grupo['title']]
                );

                $ordenPadre = 1;

                foreach ($grupo['items'] as $item) {
                    $parentSlug = Str::slug($item['key'] ?? $item['name']);

                    // Padre
                    $menuPadre = Menu::updateOrCreate(
                        [
                            'slug' => $parentSlug,
                            'grupo_menu_id' => $grupoModel->id,
                            'padre_menu_id' => null,
                        ],
                        [
                            'nombre' => $item['name'],
                            'url'    => $item['url'] ?? null,   // normalmente null en padres con submenu
                            'icono'  => $item['icon'] ?? null,
                            'orden'  => $ordenPadre++,
                        ]
                    );

                    $createdMenuIds[] = $menuPadre->id;

                    // Hijos
                    if (!empty($item['submenu'])) {
                        $ordenHijo = 1;

                        foreach ($item['submenu'] as $sub) {
                            $childSlug = $parentSlug . '.' . Str::slug($sub['key'] ?? $sub['name']);

                            $menuHijo = Menu::updateOrCreate(
                                [
                                    'slug' => $childSlug,
                                    'grupo_menu_id' => $grupoModel->id,
                                    'padre_menu_id' => $menuPadre->id,
                                ],
                                [
                                    'nombre' => $sub['name'],
                                    'url'    => $sub['url'] ?? null,
                                    'icono'  => null,
                                    'orden'  => $ordenHijo++,
                                ]
                            );

                            $createdMenuIds[] = $menuHijo->id;
                        }
                    }
                }
            }

            // Sync rol Super Usuario
            $role = Role::where('nombre', 'Super Usuario')->first();
            if ($role) {
                $role->menus()->sync(array_values(array_unique($createdMenuIds)));
            }
        });

    }
}
