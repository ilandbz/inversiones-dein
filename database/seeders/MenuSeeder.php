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
                    "titulo" => "Principal",
                    "items" => [
                        ["nombre" => "Dashboard", "slug" => "/", "icono" => "speedometer2", "url" => "/"],
                    ],
                ],
                [
                    "titulo" => "Gestión Comercial",
                    "items" => [
                        [
                            "nombre" => "Clientes",
                            "slug" => "clientes",
                            "icono" => "person-badge-fill",
                            "submenu" => [
                                ["nombre" => "Registro de Clientes", "slug" => "registro-de-clientes", "url" => "/clientes/registro-de-clientes"],
                                ["nombre" => "Listado de Clientes", "slug" => "listado-de-clientes", "url" => "/clientes/listado-de-clientes"],
                                ["nombre" => "Posición del Cliente", "slug" => "posicion-del-cliente", "url" => "/clientes/posicion-del-cliente"],
                                ["nombre" => "Historial del Cliente", "slug" => "historial-del-cliente", "url" => "/clientes/historial-del-cliente"],
                            ],
                        ],
                        [
                            "nombre" => "Asesores",
                            "slug" => "asesores",
                            "icono" => "people-fill",
                            "submenu" => [
                                ["nombre" => "Metas", "slug" => "metas", "url" => "/asesores/metas"],
                                ["nombre" => "Cartilla de Cobranza", "slug" => "cartilla-de-cobranza", "url" => "/asesores/cartilla-de-cobranza"],
                                ["nombre" => "Histórico del Asesor", "slug" => "historico-del-asesor", "url" => "/asesores/historico-del-asesor"],
                            ],
                        ],
                        [
                            "nombre" => "Pagos",
                            "slug" => "pagos",
                            "icono" => "people-fill",
                            "submenu" => [
                                ["nombre" => "Reportes", "slug" => "reportes", "url" => "/pagos/reportes"],
                                ["nombre" => "Estadísticas de Ingresos", "slug" => "estadisticas-de-ingresos", "url" => "/pagos/estadisticas-de-ingresos"],
                            ],
                        ],
                    ],
                ],
                [
                    "titulo" => "Operaciones",
                    "items" => [
                        [
                            "nombre" => "Préstamos",
                            "slug" => "prestamos",
                            "icono" => "cash-stack",
                            "submenu" => [
                                ["nombre" => "Registrar Préstamo", "slug" => "registrar-prestamo", "url" => "/prestamos/registrar"],
                                ["nombre" => "Simulación", "slug" => "simulacion", "url" => "/prestamos/simulacion"],
                                ["nombre" => "Cronograma de Pagos", "slug" => "cronograma-de-pagos", "url" => "/prestamos/cronograma-de-pagos"],
                                ["nombre" => "Historial de Préstamos", "slug" => "historial-de-prestamos", "url" => "/prestamos/historial-de-prestamos"],
                                ["nombre" => "Evaluación", "slug" => "evaluacion", "url" => "/prestamos/evaluacion"],
                                ["nombre" => "Desembolso", "slug" => "desembolso", "url" => "/prestamos/desembolso"],
                            ],
                        ],
                        [
                            "nombre" => "Ahorros",
                            "slug" => "ahorros",
                            "icono" => "piggy-bank",
                            "submenu" => [
                                ["nombre" => "Apertura de Cuenta", "slug" => "apertura-de-cuenta", "url" => "/ahorros/apertura-de-cuenta"],
                                ["nombre" => "Depósitos / Retiros", "slug" => "depositos-retiros", "url" => "/ahorros/depositos-retiros"],
                                ["nombre" => "Estado de Cuenta", "slug" => "estado-de-cuenta", "url" => "/ahorros/estado-de-cuenta"],
                            ],
                        ],
                        [
                            "nombre" => "Caja",
                            "slug" => "caja",
                            "icono" => "safe-fill",
                            "submenu" => [
                                ["nombre" => "Cobros", "slug" => "cobros", "url" => "/caja/cobros"],
                                ["nombre" => "Pagos", "slug" => "pagos", "url" => "/caja/pagos"],
                                ["nombre" => "Cierre de Caja", "slug" => "cierre-de-caja", "url" => "/caja/cierre-de-caja"],
                            ],
                        ],
                    ],
                ],
                [
                    "titulo" => "Control y Riesgos",
                    "items" => [
                        [
                            "nombre" => "Gestión de Riesgos",
                            "slug" => "riesgos",
                            "icono" => "exclamation-triangle-fill",
                            "submenu" => [
                                ["nombre" => "Mora y Castigos", "slug" => "mora-y-castigos", "url" => "/riesgos/mora-y-castigos"],
                                ["nombre" => "Bloqueo de Pagos", "slug" => "bloqueo-de-pagos", "url" => "/riesgos/bloqueo-de-pagos"],
                            ],
                        ],
                        [
                            "nombre" => "Gerencia",
                            "slug" => "gerencia",
                            "icono" => "shield-lock-fill",
                            "submenu" => [
                                ["nombre" => "Reportes Gerenciales", "slug" => "reportes-gerenciales", "url" => "/gerencia/reportes-gerenciales"],
                                ["nombre" => "Autorizaciones", "slug" => "autorizaciones", "url" => "/gerencia/autorizaciones"],
                            ],
                        ],
                    ],
                ],
                [
                    "titulo" => "Administración",
                    "items" => [
                        [
                            "nombre" => "Usuarios",
                            "slug" => "usuarios",
                            "icono" => "person-fill",
                            "url" => "/usuarios",
                        ],
                        [
                            "nombre" => "Roles",
                            "slug" => "roles",
                            "icono" => "shield-fill",
                            "url" => "/roles",
                        ],
                        [
                            "nombre" => "Permisos",
                            "slug" => "permisos",
                            "icono" => "shield-lock-fill",
                            "url" => "/permisos",
                        ],
                        [
                            "nombre" => "Configuración",
                            "slug" => "configuracion",
                            "icono" => "gear-fill",
                            "url" => "/configuracion",
                        ],
                        [
                            "nombre" => "Propiedades",
                            "slug" => "propiedades",
                            "icono" => "fas fa-wallet",
                            "url" => "/propiedades",
                        ],
                        [
                            "nombre" => "Actividad de Negocio",
                            "slug" => "actividad-negocio",
                            "icono" => "person-badge-fill",
                            "url" => "/actividad-negocio",
                        ],
                        [
                            "nombre" => "Propiedades",
                            "slug" => "propiedades",
                            "icono" => "fas fa-wallet",
                            "url" => "/propiedades",
                        ],
                    ],
                ],
            ];

            $createdMenuIds = [];
            // Sync rol Super Usuario
            $role = Role::where('nombre', 'SUPER USUARIO')->first();

            foreach ($data as $grupoData) {
                $grupo = GrupoMenu::firstOrCreate(['titulo' => $grupoData['titulo']]);

                foreach ($grupoData['items'] as $itemData) {
                    $menu = Menu::updateOrCreate(
                        ['slug' => $itemData['slug']],
                        [
                            'nombre' => $itemData['nombre'],
                            'icono' => $itemData['icono'],
                            'url' => $itemData['url'] ?? null,
                            'grupo_menu_id' => $grupo->id
                        ]
                    );

                    if ($role) {
                        $role->menus()->syncWithoutDetaching([$menu->id]);
                    }

                    if (!empty($itemData['submenu'])) {
                        foreach ($itemData['submenu'] as $subData) {
                            $subMenu = Menu::updateOrCreate(
                                ['slug' => $subData['slug']],
                                [
                                    'nombre' => $subData['nombre'],
                                    'url' => $subData['url'],
                                    'padre_menu_id' => $menu->id,
                                    'grupo_menu_id' => $grupo->id
                                ]
                            );

                            if ($role) {
                                $role->menus()->syncWithoutDetaching([$subMenu->id]);
                            }
                        }
                    }
                }
            }
        });
    }
}
