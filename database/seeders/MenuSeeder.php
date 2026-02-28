<?php

namespace Database\Seeders;

use App\Models\GrupoMenu;
use App\Models\Menu;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $data = [
                [
                    "titulo" => "Principal",
                    "items" => [
                        ["nombre" => "Dashboard", "slug" => "/", "icono" => "fas fa-tachometer-alt", "url" => "/"],
                    ],
                ],
                [
                    "titulo" => "Gestión Comercial",
                    "items" => [
                        [
                            "nombre" => "Clientes",
                            "slug" => "clientes",
                            "icono" => "fas fa-users",
                            "submenu" => [
                                ["nombre" => "Registro de Clientes", "slug" => "registro-de-clientes", "icono" => "fas fa-user-plus", "url" => "/clientes/registro-de-clientes"],
                                ["nombre" => "Listado de Clientes", "slug" => "listado-de-clientes", "icono" => "fas fa-list", "url" => "/clientes/listado-de-clientes"],
                                ["nombre" => "Historial del Cliente", "slug" => "historial-del-cliente", "icono" => "fas fa-history", "url" => "/clientes/historial-del-cliente"],
                            ],
                        ],
                        [
                            "nombre" => "Asesores",
                            "slug" => "asesores",
                            "icono" => "fas fa-user-tie",
                            "submenu" => [
                                ["nombre" => "Metas", "slug" => "metas", "icono" => "fas fa-bullseye", "url" => "/asesores/metas"],
                                ["nombre" => "Cartilla de Cobranza", "slug" => "cartilla-de-cobranza", "icono" => "fas fa-clipboard-list", "url" => "/asesores/cartilla-de-cobranza"],
                                ["nombre" => "Histórico del Asesor", "slug" => "historico-del-asesor", "icono" => "fas fa-chart-line", "url" => "/asesores/historico-del-asesor"],
                            ],
                        ],
                        [
                            "nombre" => "Pagos",
                            "slug" => "pagos",
                            "icono" => "fas fa-money-bill-wave",
                            "submenu" => [
                                ["nombre" => "Reportes", "slug" => "reportes", "icono" => "fas fa-file-alt", "url" => "/pagos/reportes"],
                                ["nombre" => "Estadísticas de Ingresos", "slug" => "estadisticas-de-ingresos", "icono" => "fas fa-chart-bar", "url" => "/pagos/estadisticas-de-ingresos"],
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
                            "icono" => "fas fa-hand-holding-usd",
                            "submenu" => [
                                ["nombre" => "Registrar Préstamo", "slug" => "registrar-prestamo", "icono" => "fas fa-plus-circle", "url" => "/prestamos/registrar"],
                                ["nombre" => "Simulación", "slug" => "simulacion", "icono" => "fas fa-calculator", "url" => "/prestamos/simulacion"],
                                ["nombre" => "Cronograma de Pagos", "slug" => "cronograma-de-pagos", "icono" => "fas fa-calendar-alt", "url" => "/prestamos/cronograma-de-pagos"],
                                ["nombre" => "Historial de Préstamos", "slug" => "historial-de-prestamos", "icono" => "fas fa-archive", "url" => "/prestamos/historial-de-prestamos"],
                                ["nombre" => "Evaluación", "slug" => "evaluacion", "icono" => "fas fa-clipboard-check", "url" => "/prestamos/evaluacion"],
                                ["nombre" => "Desembolso", "slug" => "desembolso", "icono" => "fas fa-coins", "url" => "/prestamos/desembolso"],
                            ],
                        ],
                        [
                            "nombre" => "Ahorros",
                            "slug" => "ahorros",
                            "icono" => "fas fa-piggy-bank",
                            "submenu" => [
                                ["nombre" => "Apertura de Cuenta", "slug" => "apertura-de-cuenta", "icono" => "fas fa-folder-open", "url" => "/ahorros/apertura-de-cuenta"],
                                ["nombre" => "Depósitos / Retiros", "slug" => "depositos-retiros", "icono" => "fas fa-right-left", "url" => "/ahorros/depositos-retiros"],
                                ["nombre" => "Estado de Cuenta", "slug" => "estado-de-cuenta", "icono" => "fas fa-file-invoice-dollar", "url" => "/ahorros/estado-de-cuenta"],
                            ],
                        ],
                        [
                            "nombre" => "Caja",
                            "slug" => "caja",
                            "icono" => "fas fa-cash-register",
                            "submenu" => [
                                ["nombre" => "Cobros", "slug" => "cobros", "icono" => "fas fa-file-invoice", "url" => "/caja/cobros"],
                                ["nombre" => "Pagos", "slug" => "pagos", "icono" => "fas fa-money-check-alt", "url" => "/caja/pagos"],
                                ["nombre" => "Cierre de Caja", "slug" => "cierre-de-caja", "icono" => "fas fa-lock", "url" => "/caja/cierre-de-caja"],
                                ["nombre" => "Desembolsar", "slug" => "desembolsar", "icono" => "fas fa-donate", "url" => "/caja/desembolsar"],
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
                            "icono" => "fas fa-triangle-exclamation",
                            "submenu" => [
                                ["nombre" => "Mora y Castigos", "slug" => "mora-y-castigos", "icono" => "fas fa-hourglass-half", "url" => "/riesgos/mora-y-castigos"],
                                ["nombre" => "Bloqueo de Pagos", "slug" => "bloqueo-de-pagos", "icono" => "fas fa-ban", "url" => "/riesgos/bloqueo-de-pagos"],
                            ],
                        ],
                        [
                            "nombre" => "Gerencia",
                            "slug" => "gerencia",
                            "icono" => "fas fa-briefcase",
                            "submenu" => [
                                ["nombre" => "Reportes Gerenciales", "slug" => "reportes-gerenciales", "icono" => "fas fa-chart-pie", "url" => "/gerencia/reportes-gerenciales"],
                                ["nombre" => "Autorizaciones", "slug" => "autorizaciones", "icono" => "fas fa-key", "url" => "/gerencia/autorizaciones"],
                            ],
                        ],
                    ],
                ],
                [
                    "titulo" => "Administración",
                    "items" => [
                        ["nombre" => "Usuarios", "slug" => "usuarios", "icono" => "fas fa-users-cog", "url" => "/usuarios"],
                        ["nombre" => "Roles", "slug" => "roles", "icono" => "fas fa-user-shield", "url" => "/roles"],
                        ["nombre" => "Permisos", "slug" => "permisos", "icono" => "fas fa-shield-alt", "url" => "/permisos"],
                        ["nombre" => "Configuración", "slug" => "configuracion", "icono" => "fas fa-cogs", "url" => "/configuracion"],
                        ["nombre" => "Propiedades", "slug" => "propiedades", "icono" => "fas fa-wallet", "url" => "/propiedades"],
                        ["nombre" => "Actividad de Negocio", "slug" => "actividad-negocio", "icono" => "fas fa-building", "url" => "/actividad-negocio"],
                    ],
                ],
            ];

            $allProcessedMenuIds = [];
            $role = Role::where('nombre', 'SUPER USUARIO')->first();

            foreach ($data as $grupoData) {
                $grupo = GrupoMenu::firstOrCreate(['titulo' => $grupoData['titulo']]);

                foreach ($grupoData['items'] as $itemData) {
                    $menu = Menu::updateOrCreate(
                        ['slug' => $itemData['slug']],
                        [
                            'nombre' => $itemData['nombre'],
                            'icono' => $itemData['icono'] ?? null,
                            'url' => $itemData['url'] ?? null,
                            'grupo_menu_id' => $grupo->id,
                            'padre_menu_id' => null,
                        ]
                    );

                    $allProcessedMenuIds[] = $menu->id;

                    if ($role) {
                        $role->menus()->syncWithoutDetaching([$menu->id]);
                    }

                    if (!empty($itemData['submenu'])) {
                        foreach ($itemData['submenu'] as $subData) {
                            $subMenu = Menu::updateOrCreate(
                                ['slug' => $subData['slug']],
                                [
                                    'nombre' => $subData['nombre'],
                                    'icono' => $subData['icono'] ?? 'fas fa-circle',
                                    'url' => $subData['url'],
                                    'padre_menu_id' => $menu->id,
                                    'grupo_menu_id' => $grupo->id,
                                ]
                            );

                            $allProcessedMenuIds[] = $subMenu->id;

                            if ($role) {
                                $role->menus()->syncWithoutDetaching([$subMenu->id]);
                            }
                        }
                    }
                }
            }

            // Pruning: elimina menús que ya no están en el array
            Menu::whereNotIn('id', $allProcessedMenuIds)->delete();
        });
    }
}
