<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cliente\StoreClienteRequest;
use App\Http\Requests\Cliente\UpdateClienteRequest;
use App\Http\Traits\UserFilters;
use App\Models\Cliente;
use App\Models\Credito;
use App\Models\Negocio;
use App\Models\Persona;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Intervention\Image\Drivers\Gd\Driver;

class ClienteController extends Controller
{
    use UserFilters;
    public function store(StoreClienteRequest $request)
    {
        $filters = $this->getUserFilters();
        $data = $request->validated();

        DB::beginTransaction();
        try {

            // Foto
            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $manager = new ImageManager(new Driver());
                $image = $manager->read($file)->scaleDown(width: 800, height: 1000);

                $nombre_archivo = $data['dni'] . '.webp';
                Storage::disk('fotos')->makeDirectory('clientes');
                Storage::disk('fotos')->put('clientes/' . $nombre_archivo, (string) $image->toWebp(80));
            }

            // Persona Cliente
            $personaCliente = Persona::updateOrCreate(
                ['dni' => $data['dni']],
                [
                    'ape_pat' => $data['ape_pat'],
                    'ape_mat' => $data['ape_mat'],
                    'primernombre' => $data['primernombre'],
                    'otrosnombres' => $data['otrosnombres'] ?? null,
                    'fecha_nac' => $data['fecha_nac'],
                    'ubigeo_nac' => $data['ubigeo_nac'],
                    'ubigeo_dom' => $data['ubigeo_dom'],
                    'genero' => $data['genero'],
                    'celular' => $data['celular'],
                    'celular2' => $data['celular2'] ?? null,
                    'email' => $data['email'] ?? null,
                    'ruc' => $data['ruc'] ?? null,
                    'estado_civil' => $data['estado_civil'],
                    'profesion' => $data['profesion'] ?? null,
                    'grado_instr' => $data['grado_instr'],
                    'origen_labor' => $data['origen_labor'],
                    'ocupacion' => $data['ocupacion'] ?? null,
                    'institucion_lab' => $data['institucion_lab'] ?? null,
                    'latitud_longitud' => $data['latitud_longitud'] ?? null,
                    'direccion' => $data['direccion'],
                ]
            );

            // Referente
            $ref = $data['referente'];

            $personaReferente = Persona::updateOrCreate(
                ['dni' => $ref['dni'] ?? null],
                [
                    'ape_pat'      => $ref['ape_pat'],
                    'ape_mat'      => $ref['ape_mat'],
                    'primernombre' => $ref['primernombre'],
                    'otrosnombres' => $ref['otrosnombres'] ?? null,
                    'celular'      => $ref['celular'],
                    'email'        => $ref['email'] ?? null,
                    'direccion'    => $ref['direccion'],

                    // defaults
                    'fecha_nac'    => '2000-01-01',
                    'genero'       => 'M',
                    'estado_civil' => 'SOLTERO',
                    'origen_labor' => 'INDEPENDIENTE',
                ]
            );

            // Cliente
            $cliente = Cliente::create([
                'usuario_id'  => $filters['user_id'],
                'persona_id'  => $personaCliente->id,
                'referente_id' => $personaReferente->id,
                'referente_parentesco' => $ref['parentesco'],
                'fecha_reg' => now()->toDateString(),
                'hora_reg'  => now()->toTimeString(),
            ]);

            // Negocio
            if ($data['origen_labor'] === 'INDEPENDIENTE') {
                $neg = $data['negocio']; // ya viene validado y existe

                Negocio::create([
                    'cliente_id'           => $cliente->id,
                    'razonsocial'          => $neg['razonsocial'] ?? null,
                    'ruc'                  => $neg['ruc'] ?? null,
                    'celular'              => $neg['celular'] ?? null,
                    'detalle_actividad_id' => $neg['detalle_actividad_id'],
                    'inicioactividad'      => $neg['inicioactividad'] ?? null,
                    'direccion'            => $neg['direccion'] ?? null,
                ]);
            }

            $cliente = $cliente->load('persona');

            DB::commit();
            return response()->json([
                'ok' => 1,
                'mensaje' => 'Cliente Registrado satisfactoriamente',
                'cliente' => $cliente
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'ok' => 0,
                'mensaje' => 'Error al registrar cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function show(Request $request)
    {
        $persona = Cliente::with(
            'persona',
            'referente',
            'negocio',
            'negocio.detalle_actividad',
            'persona.conyugePersona',
            'usuario:id,name',
        )->where('id', $request->id)->first();

        return $persona;
    }
    public function asignarAsesorMasivo(Request $request)
    {
        $nuevo_asesor_id = $request->asesor_id;
        $clientes = $request->selected;
        $convigentes = filter_var($request->convigentes, FILTER_VALIDATE_BOOLEAN); // asegura booleano real

        foreach ($clientes as $row) {
            if ($convigentes) {
                Credito::where('cliente_id', $row['id'])
                    ->where('estado', 'DESEMBOLSADO')
                    ->update([
                        'asesor_id' => $nuevo_asesor_id
                    ]);
            }

            Cliente::where('id', $row['id'])->update([
                'usuario_id' => $nuevo_asesor_id
            ]);
        }

        return response()->json([
            'ok' => 1,
            'mensaje' => 'Cliente modificado satisfactoriamente'
        ], 200);
    }
    public function getDatosParaNuevoCredito(Request $request)
    {
        $dni = $request->dni;
        $cliente = Cliente::whereHas('persona', function ($q) use ($dni) {
            $q->where('dni', $dni);
        })->with([
            'persona:id,dni,ape_pat,ape_mat,primernombre,otrosnombres,ubicacion_domicilio_id',
            'persona.ubicacion:id,tipo,ubigeo,tipovia,nombrevia,nro,interior,mz,lote,tipozona,nombrezona,referencia',
            'negocios',
            'negocios.tipo_actividad:id,nombre'
        ])->first();
        $datos = [
            'cliente'   => $cliente,
            'creditos'  => $cliente?->creditosVigentesConMora(),
        ];
        return $datos;
    }
    public function mostrarPorDni(Request $request)
    {
        $dni = $request->dni;
        $cliente = Cliente::with([
            'persona:id,dni,ape_pat,ape_mat,primernombre,otrosnombres',
        ])->whereHas('persona', function ($q) use ($dni) {
            $q->where('dni', $dni);
        })
            ->with('ahorros', 'creditos')
            ->first();
        return $cliente;
    }
    public function mostrarPorBusqueda(Request $request)
    {
        $busqueda = $request->busqueda;
        $cliente = Cliente::with([
            'persona:id,dni,ape_pat,ape_mat,primernombre,otrosnombres',
        ])->whereHas('persona', function ($q) use ($busqueda) {
            $q->whereRaw('UPPER(dni) LIKE ?', ['%' . $busqueda . '%'])
                ->orWhereRaw('UPPER(ape_pat) LIKE ?', ['%' . $busqueda . '%'])
                ->orWhereRaw('UPPER(ape_mat) LIKE ?', ['%' . $busqueda . '%'])
                ->orWhereRaw('UPPER(primernombre) LIKE ?', ['%' . $busqueda . '%'])
                ->orWhereRaw('UPPER(otrosnombres) LIKE ?', ['%' . $busqueda . '%'])
                ->orWhereRaw("UPPER(CONCAT(ape_pat, ' ', ape_mat, ' ', primernombre, ' ', IFNULL(otrosnombres, ''))) LIKE ?", ['%' . $busqueda . '%']);
        })
            ->with('ahorros', 'creditos')
            ->first();
        return $cliente;
    }

    public function update(UpdateClienteRequest $request)
    {
        $filters = $this->getUserFilters();
        $data = $request->validated();

        $cliente = Cliente::with(['persona', 'negocio']) // ajusta relaciones si el nombre cambia
            ->where('id', $data['id']) // o $request->id
            ->firstOrFail();

        DB::beginTransaction();
        try {

            // 1) Foto (igual que store)
            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $manager = new ImageManager(new Driver());
                $image = $manager->read($file)->scaleDown(width: 800, height: 1000);

                $nombre_archivo = $data['dni'] . '.webp';
                Storage::disk('fotos')->makeDirectory('clientes');
                Storage::disk('fotos')->put('clientes/' . $nombre_archivo, (string) $image->toWebp(80));
            }

            // 2) Persona Cliente (aquí NO uses updateOrCreate por dni, actualiza por persona_id real)
            $personaCliente = Persona::findOrFail($cliente->persona_id);
            $personaCliente->update([
                'dni' => $data['dni'], // si permites cambiar DNI
                'ape_pat' => $data['ape_pat'],
                'ape_mat' => $data['ape_mat'],
                'primernombre' => $data['primernombre'],
                'otrosnombres' => $data['otrosnombres'] ?? null,
                'fecha_nac' => $data['fecha_nac'],
                'ubigeo_nac' => $data['ubigeo_nac'],
                'ubigeo_dom' => $data['ubigeo_dom'],
                'genero' => $data['genero'],
                'celular' => $data['celular'],
                'celular2' => $data['celular2'] ?? null,
                'email' => $data['email'] ?? null,
                'ruc' => $data['ruc'] ?? null,
                'estado_civil' => $data['estado_civil'],
                'profesion' => $data['profesion'] ?? null,
                'grado_instr' => $data['grado_instr'],
                'origen_labor' => $data['origen_labor'],
                'ocupacion' => $data['ocupacion'] ?? null,
                'institucion_lab' => $data['institucion_lab'] ?? null,
                'latitud_longitud' => $data['latitud_longitud'] ?? null,
                'direccion' => $data['direccion'],
            ]);

            // 3) Referente (igual que store, pero OJO con dni null)
            $ref = $data['referente'];

            // Si tu referente.dni es nullable, NO uses updateOrCreate con ['dni' => null]
            // porque te puede terminar "pisando" siempre el mismo registro null.
            // Mejor: si viene DNI => updateOrCreate; si no viene => create nuevo.
            if (!empty($ref['dni'])) {
                $personaReferente = Persona::updateOrCreate(
                    ['dni' => $ref['dni']],
                    [
                        'ape_pat'      => $ref['ape_pat'],
                        'ape_mat'      => $ref['ape_mat'],
                        'primernombre' => $ref['primernombre'],
                        'otrosnombres' => $ref['otrosnombres'] ?? null,
                        'celular'      => $ref['celular'],
                        'email'        => $ref['email'] ?? null,
                        'direccion'    => $ref['direccion'],

                        // defaults
                        'fecha_nac'    => '2000-01-01',
                        'genero'       => 'M',
                        'estado_civil' => 'SOLTERO',
                        'origen_labor' => 'INDEPENDIENTE',
                    ]
                );
            } else {
                $personaReferente = Persona::create([
                    'dni' => null,
                    'ape_pat'      => $ref['ape_pat'],
                    'ape_mat'      => $ref['ape_mat'],
                    'primernombre' => $ref['primernombre'],
                    'otrosnombres' => $ref['otrosnombres'] ?? null,
                    'celular'      => $ref['celular'],
                    'email'        => $ref['email'] ?? null,
                    'direccion'    => $ref['direccion'],

                    // defaults
                    'fecha_nac'    => '2000-01-01',
                    'genero'       => 'M',
                    'estado_civil' => 'SOLTERO',
                    'origen_labor' => 'INDEPENDIENTE',
                ]);
            }

            // 4) Cliente (actualiza campos propios)
            $cliente->update([
                'usuario_id' => $filters['user_id'], // si quieres registrar quién editó
                'referente_id' => $personaReferente->id,
                'referente_parentesco' => $ref['parentesco'],
                'estado' => $data['estado'] ?? $cliente->estado, // si lo manejas
            ]);

            // 5) Negocio (upsert / delete según origen_labor)
            if ($data['origen_labor'] === 'INDEPENDIENTE') {
                $neg = $data['negocio'];

                Negocio::updateOrCreate(
                    ['cliente_id' => $cliente->id],
                    [
                        'razonsocial'          => $neg['razonsocial'] ?? null,
                        'ruc'                  => $neg['ruc'] ?? null,
                        'celular'              => $neg['celular'] ?? null,
                        'detalle_actividad_id' => $neg['detalle_actividad_id'],
                        'inicioactividad'      => $neg['inicioactividad'] ?? null,
                        'direccion'            => $neg['direccion'] ?? null,
                    ]
                );
            } else {
                // Si ahora es DEPENDIENTE, opcional: borrar negocio existente
                Negocio::where('cliente_id', $cliente->id)->delete();
            }

            $cliente = $cliente->fresh()->load(['persona']); // si quieres devolver todo

            DB::commit();
            return response()->json([
                'ok' => 1,
                'mensaje' => 'Cliente modificado satisfactoriamente',
                'cliente' => $cliente
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'ok' => 0,
                'mensaje' => 'Error al modificar cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function destroy(Request $request)
    {
        $persona = Cliente::where('id', $request->id)->first();
        $persona->delete();
        return response()->json([
            'ok' => 1,
            'mensaje' => 'Cliente eliminado satisfactoriamente'
        ], 200);
    }
    public function todos()
    {
        $clientes = Cliente::query()
            ->join('personas', 'clientes.persona_id', '=', 'personas.id')
            ->select([
                'clientes.id',
                'clientes.usuario_id',
                'clientes.persona_id',
                'clientes.estado',
                'clientes.fecha_reg',
                'clientes.hora_reg',
                'clientes.referente_id',
                'clientes.referente_parentesco',

                'personas.dni',
                'personas.ape_pat',
                'personas.ape_mat',
                'personas.primernombre',
                'personas.otrosnombres',
                'personas.fecha_nac',
                'personas.ubigeo_nac',
                'personas.genero',
                'personas.celular',
                'personas.celular2',
                'personas.email',
                'personas.ruc',
                'personas.estado_civil',
                'personas.profesion',
                'personas.grado_instr',
                'personas.origen_labor',
                'personas.ocupacion',
                'personas.institucion_lab',
                'personas.conyugue',
                'personas.direccion',

                DB::raw("CONCAT(
                    personas.ape_pat,' ',
                    personas.ape_mat,' ',
                    personas.primernombre,' ',
                    IFNULL(personas.otrosnombres,'')
                ) AS apenom")
            ])
            ->get();

        return $clientes;
    }
    public function clientesPorEstado(Request $request)
    {
        $estado = $request->estado;
        $buscar = $request->buscar;
        $clientes = Cliente::query()
            ->join('personas', 'clientes.persona_id', '=', 'personas.id')
            ->where('clientes.estado', $estado)
            ->where('personas.dni', 'like', '%' . $buscar . '%')
            ->orWhere('personas.ape_pat', 'like', '%' . $buscar . '%')
            ->orWhere('personas.ape_mat', 'like', '%' . $buscar . '%')
            ->orWhere('personas.primernombre', 'like', '%' . $buscar . '%')
            ->orWhere('personas.otrosnombres', 'like', '%' . $buscar . '%')
            ->select([
                'clientes.id',
                'clientes.usuario_id',
                'clientes.persona_id',
                'clientes.estado',
                'clientes.fecha_reg',
                'clientes.hora_reg',
                'clientes.referente_id',
                'clientes.referente_parentesco',

                'personas.dni',
                'personas.ape_pat',
                'personas.ape_mat',
                'personas.primernombre',
                'personas.otrosnombres',
                'personas.fecha_nac',
                'personas.ubigeo_nac',
                'personas.genero',
                'personas.celular',
                'personas.celular2',
                'personas.email',
                'personas.ruc',
                'personas.estado_civil',
                'personas.profesion',
                'personas.grado_instr',
                'personas.origen_labor',
                'personas.ocupacion',
                'personas.institucion_lab',
                'personas.conyugue',
                'personas.direccion',

                DB::raw("CONCAT(
                    personas.ape_pat,' ',
                    personas.ape_mat,' ',
                    personas.primernombre,' ',
                    IFNULL(personas.otrosnombres,'')
                ) AS apenom")
            ])
            ->get();

        return $clientes;
    }
    public function listar(Request $request)
    {
        $filters = $this->getUserFilters();
        $buscar = mb_strtoupper($request->buscar);
        $paginacion = $request->paginacion ?? 10;
        $query = Cliente::with([
            'usuario:id,name',
            'persona:id,dni,ape_pat,ape_mat,primernombre,otrosnombres,fecha_nac,celular,ruc,email',
        ])
            ->join('personas', 'clientes.persona_id', '=', 'personas.id')
            ->where(function ($query) use ($buscar) {
                $query->whereRaw('UPPER(personas.dni) LIKE ?', ['%' . $buscar . '%'])
                    ->orWhereRaw('UPPER(personas.ape_pat) LIKE ?', ['%' . $buscar . '%'])
                    ->orWhereRaw('UPPER(personas.ape_mat) LIKE ?', ['%' . $buscar . '%'])
                    ->orWhereRaw('UPPER(personas.primernombre) LIKE ?', ['%' . $buscar . '%'])
                    ->orWhereRaw('UPPER(personas.otrosnombres) LIKE ?', ['%' . $buscar . '%'])
                    ->orWhereRaw("UPPER(CONCAT(personas.ape_pat, ' ', personas.ape_mat, ' ', personas.primernombre, ' ', IFNULL(personas.otrosnombres, ''))) LIKE ?", ['%' . $buscar . '%']);
            })
            ->select([
                'clientes.id',
                'clientes.usuario_id',
                'clientes.persona_id',
                'clientes.estado',
                'clientes.fecha_reg',
                'clientes.hora_reg',
                DB::raw("CONCAT(personas.ape_pat, ' ', personas.ape_mat, ' ', personas.primernombre, ' ', IFNULL(personas.otrosnombres, '')) AS apenom")
            ]);
        if ($filters['role'] === 'ASESOR') {
            $query->where('usuario_id', $filters['user_id']);
        }

        return $query->orderBy('apenom', 'asc')->paginate($paginacion);
    }
    public function listarClientesPosicion(Request $request)
    {
        $filters = $this->getUserFilters();
        $buscar = mb_strtoupper($request->buscar);
        $paginacion = $request->paginacion ?? 10;
        $query = Cliente::join('personas', 'clientes.persona_id', '=', 'personas.id')
            ->with([
                'usuario:id,name',
                'agencia:id,nombre',
                'persona:id,dni,ape_pat,ape_mat,primernombre,otrosnombres,fecha_nac',
            ])
            ->withCount([
                'creditos as creditos_vigentes' => function ($q) {
                    $q->where('estado', 'DESEMBOLSADO');
                },
                'juntas as juntas_vigentes' => function ($q) {
                    $q->where('estado', 'APROBADO');
                },
                'creditos as creditos_totales' => function ($q) {
                    $q->whereHas('desembolso');
                },
                'juntas as juntas_totales'
            ])
            ->where(function ($q) use ($buscar) {
                $q->whereRaw('UPPER(personas.dni) LIKE ?', ['%' . $buscar . '%'])
                    ->orWhereRaw('UPPER(personas.ape_pat) LIKE ?', ['%' . $buscar . '%'])
                    ->orWhereRaw('UPPER(personas.ape_mat) LIKE ?', ['%' . $buscar . '%'])
                    ->orWhereRaw('UPPER(personas.primernombre) LIKE ?', ['%' . $buscar . '%'])
                    ->orWhereRaw('UPPER(personas.otrosnombres) LIKE ?', ['%' . $buscar . '%'])
                    ->orWhereRaw("UPPER(CONCAT(personas.ape_pat, ' ', personas.ape_mat, ' ', personas.primernombre, ' ', IFNULL(personas.otrosnombres, ''))) LIKE ?", ['%' . $buscar . '%'])
                    ->orWhereHas('usuario', function ($q3) use ($buscar) {
                        $q3->whereRaw('UPPER(name) LIKE ?', ['%' . $buscar . '%']);
                    });;
            });

        if ($filters['role'] === 'ASESOR') {
            $query->where('usuario_id', $filters['user_id']);
        }
        // if ($filters['role'] === 'GERENTE AGENCIA') {
        //     $query->where('agencia_id', $filters['agencia_id']);
        // }
        return $query->orderBy('personas.ape_pat', 'asc')
            ->paginate($paginacion);
    }
    public function obtenerClienteReciente(Request $request)
    {
        $filters = $this->getUserFilters();
        $cliente = Cliente::with('persona:id,dni,ape_pat,ape_mat,primernombre,otrosnombres,fecha_nac')
            ->where('usuario_id', $filters['user_id'])
            ->where('estado', 'REGISTRADO')
            ->orderBy('id', 'desc')
            ->first();
        return $cliente;
    }
    public function obtenerClienteRecientePdf(Request $request)
    {
        $request->validate([
            'cliente_id' => ['required', 'integer']
        ]);

        $cliente = Cliente::with([
            'persona',
            'negocio',
            'referente',
        ])->findOrFail($request->cliente_id);

        // Render PDF con Blade
        $pdf = Pdf::loadView('pdf.cliente_resumen', [
            'cliente' => $cliente
        ])->setPaper('A4', 'portrait');

        return $pdf->stream("cliente_{$cliente->id}.pdf");
    }
}
