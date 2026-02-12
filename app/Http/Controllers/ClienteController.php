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
            'persona.conyugePersona',
            'usuario:id,name',
            'aval:id,dni,ape_pat,ape_mat,primernombre,otrosnombres',
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
            'persona:id,dni,ape_pat,ape_mat,primernombre,otrosnombres,ubicacion_domicilio_id',
            'persona.ubicacion:id,tipo,ubigeo,tipovia,nombrevia,nro,interior,mz,lote,tipozona,nombrezona,referencia',
            'negocios',
            'negocios.tipo_actividad:id,nombre',
        ])->whereHas('persona', function ($q) use ($dni) {
            $q->where('dni', $dni);
        })->first();
        return $cliente;
    }
    public function datosCreditoJuntaPorDni(Request $request)
    {
        $dni = $request->dni;
        $cliente = Cliente::with('persona:id,dni,ape_pat,ape_mat,primernombre,otrosnombres')
            ->whereHas('persona', function ($q) use ($dni) {
                $q->where('dni', $dni);
            })->first();
        $creditos = Desembolso::obtenerResumenPagosPorCliente($cliente->id, 'DESEMBOLSADO');
        $juntas = Junta::obtenerResumenPagosPorCliente($cliente->id);
        return response()->json([
            'cliente'    => $cliente,
            'creditos'   => $creditos,
            'juntas'     => $juntas,
        ], 200);
    }
    public function update(UpdateClienteRequest $request)
    {
        $cliente = Cliente::where('id', $request->id)->first();
        $cliente->aval_id = $request->aval_id;
        $cliente->save();

        $esconyugue = trim($request->estado_civil) === 'Casado' || trim($request->estado_civil) === 'Conviviente';
        if ($esconyugue && empty($request->conyugue_id)) {
            return response()->json([
                'errors' => [
                    'dniconyugue' => ['DNI conyugue es necesario']
                ]
            ], 422);
        }
        $file = $request->file('foto');
        $persona = Persona::where('id', $request->persona_id)->first();
        $persona->ape_pat = $request->ape_pat;
        $persona->ape_mat = $request->ape_mat;
        $persona->primernombre = $request->primernombre;
        $persona->otrosnombres = $request->otrosnombres;
        $persona->fecha_nac = $request->fecha_nac;
        $persona->ubigeo_nac = $request->ubigeo;
        $persona->genero = $request->genero;
        $persona->celular = $request->celular;
        $persona->email = $request->email;
        $persona->ruc = $request->ruc;
        $persona->estado_civil = $request->estado_civil;
        $persona->profesion = $request->profesion;
        $persona->nacionalidad = $request->nacionalidad;
        $persona->grado_instr = $request->grado_instr;
        $persona->tipo_trabajador = $request->tipo_trabajador;
        $persona->ocupacion = $request->ocupacion;
        $persona->institucion_lab = $request->institucion_lab;
        $persona->conyugue  = $esconyugue ? $request->conyugue_id : null;
        $persona->save();



        if ($persona->ubicacion_domicilio_id) {
            $domicilio = Ubicacion::find($persona->ubicacion_domicilio_id);
        } else {
            $domicilio = new Ubicacion();
        }

        $domicilio->tipo        = $request->tipodomicilio ?? 'NDF';
        $domicilio->ubigeo      = $request->ubigeodomicilio;
        $domicilio->tipovia     = $request->tipovia ?? 'S/N';
        $domicilio->nombrevia   = $request->nombrevia;
        $domicilio->nro         = $request->nro ?? 'S/N';
        $domicilio->interior    = $request->interior ?? 'S/N';
        $domicilio->mz          = $request->mz ?? 'S/N';
        $domicilio->lote        = $request->lote ?? 'S/N';
        $domicilio->tipozona    = $request->tipozona;
        $domicilio->nombrezona  = $request->nombrezona;
        $domicilio->referencia  = $request->referencia;
        $domicilio->latitud     = $request->latitud;
        $domicilio->longitud    = $request->longitud;
        $domicilio->save();
        if (!$persona->ubicacion_domicilio_id) {
            $persona->ubicacion_domicilio_id = $domicilio->id;
            $persona->save();
        }
        if ($file) {
            $errores = [];
            if ($file->getSize() > 2048 * 1024) {
                $errores['foto'][] = 'El tamaño máximo permitido es 2MB.';
            }
            $manager = new ImageManager(new Driver());

            $image = $manager->read($file);

            if ($image->width() > 800 || $image->height() > 1000) {
                $image->resize(800, 1000, function ($constraint) {
                    $constraint->aspectRatio(); // conserva proporción original
                    $constraint->upsize();      // evita agrandar imágenes pequeñas
                });
            }

            if (!empty($errores)) {
                return response()->json(['errors' => $errores], 422);
            }

            $nombre_archivo = $request->dni . '.webp';
            Storage::disk('fotos')->makeDirectory('clientes');
            Storage::disk('fotos')->put('clientes/' . $nombre_archivo, (string) $image->toWebp());
        }
        return response()->json([
            'ok' => 1,
            'mensaje' => 'Cliente modificado satisfactoriamente'
        ], 200);
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

        // $clientes = Cliente::query()
        //     ->with([
        //         'persona:id,dni,ape_pat,ape_mat,primernombre,otrosnombres,fecha_nac,ubigeo_nac,genero,celular,celular2,email,ruc,estado_civil,profesion,grado_instr,origen_labor,ocupacion,institucion_lab,conyugue,direccion',
        //         'referente:id,dni,ape_pat,ape_mat,primernombre,otrosnombres,fecha_nac',
        //         'usuario:id,name',
        //     ])
        //     ->select([
        //         'id',
        //         'usuario_id',
        //         'persona_id',
        //         'estado',
        //         'fecha_reg',
        //         'hora_reg',
        //         'referente_id',
        //         'referente_parentesco',
        //     ])
        //     ->get();

        return $clientes;
        // $clientes = Cliente::with([
        //     'usuario:id,name',
        //     'persona:id,dni,ape_pat,ape_mat,primernombre,otrosnombres'
        // ])->get();



        return $clientes;
    }
    public function listar(Request $request)
    {
        $filters = $this->getUserFilters();
        $buscar = mb_strtoupper($request->buscar);
        $paginacion = $request->paginacion ?? 10;
        $query = Cliente::with([
            'usuario:id,name',
            'persona:id,dni,ape_pat,ape_mat,primernombre,otrosnombres,fecha_nac',
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
