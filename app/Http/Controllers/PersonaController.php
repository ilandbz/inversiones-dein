<?php

namespace App\Http\Controllers;
use App\Http\Traits\UserFilters;
use App\Http\Requests\Persona\UpdatePersonaRequest;
use App\Http\Requests\Persona\StorePersonaRequest;
use Illuminate\Http\Request;
use App\Models\Persona;
use Illuminate\Support\Facades\Storage;
class PersonaController extends Controller
{
    use UserFilters;
    public function mostrarPorDniConApi(Request $request)
    {
        $persona = Persona::where('dni', $request->dni)->first();
        if($persona){
            return $persona;
        }
        // $response = obtenerDatosDniRuc('dni', $request->dni);
        // if($response->status() == 200){
        //     $data = $response->json();
        //     $nombreArray = explode(' ', $data['first_name'], 2);
        //     $primernombre = $nombreArray[0];
        //     $otrosnombres = isset($nombreArray[1]) ? $nombreArray[1] : '';             
        //     $persona = [
        //         'dni' => $data['document_number'],
        //         'ape_pat' => $data['first_last_name'],
        //         'ape_mat' => $data['second_last_name'],
        //         'primernombre' => $primernombre,
        //         'otrosnombres' => $otrosnombres,
        //     ];
        //     return response()->json($persona, 201);
        // }
    }
    public function mostrarPorDni(Request $request)
    {
        $persona = Persona::with([
            'cliente'
            ])
        ->where('dni', $request->dni)->first();
        return $persona;
    }    
    public function store(StorePersonaRequest $request)
    {
        $request->validated();
        $file = $request->file('foto');
        if ($file) {
            $nombre_archivo = $request->dni.".webp";
            Storage::disk('personas')->put($nombre_archivo,File::get($file));
        }
        $persona = Persona::create([
            'dni' => $request->dni,
            'ape_pat' => $request->ape_pat,
            'ape_mat' => $request->ape_mat,
            'primernombre' => $request->primernombre,
            'otrosnombres' => $request->otrosnombres,
            'fecha_nac' => $request->fecha_nac,
            'ubigeo_nac' => $request->ubigeo_nac,
            'genero' => $request->genero,
            'celular' => $request->celular,
            'email' => $request->email,
            'ruc' => $request->ruc,
            'estado_civil' => $request->estado_civil ?? 'Soltero',
            'profesion' => $request->profesion,
            'grado_instr' => $request->grado_instr,
            'origen_labor' => $request->origen_labor ?? 'DEPENDIENTE',
            'ocupacion' => $request->ocupacion ?? 'NINGUNO',
            'institucion_lab' => $request->institucion_lab ?? 'NINGUNO',
            'direccion' => $request->direccion ?? '',
        ]);
        return response()->json([
            'ok' => 1,
            'mensaje' => 'Persona Registrado satisfactoriamente',
        ],200);
    }
    public function show(Request $request)
    {
        $persona = Persona::where('id', $request->id)->first();
        return $persona;
    }
    public function update(UpdatePersonaRequest $request)
    {
        $persona = Persona::where('id',$request->id)->first();
        $persona->dni = $request->dni;
        $persona->ape_pat = $request->ape_pat;
        $persona->ape_mat = $request->ape_mat;
        $persona->primernombre = $request->primernombre;
        $persona->otrosnombres = $request->otrosnombres;
        $persona->fecha_nac = $request->fecha_nac;
        $persona->ubigeo_nac = $request->ubigeo_nac;
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
        $persona->ubicacion_domicilio_id = $request->ubicacion_domicilio_id;

        $persona->save();

        return response()->json([
            'ok' => 1,
            'mensaje' => 'Persona modificado satisfactoriamente'
        ],200);
    }

    public function actualizarCelular(Request $request)
    {
        $request->validate([
            'dni' => 'required|exists:personas,dni',
            'celular' => 'required|string|max:15',
        ]);
        $persona = Persona::where('dni',$request->dni)->update(['celular' => $request->celular]);
        //$persona->celular = $request->celular;
        //$persona->save();

        return response()->json([
            'ok' => 1,
            'mensaje' => 'Celular modificado satisfactoriamente'
        ],200);
    }

    public function destroy(Request $request)
    {
        $persona = Persona::where('id', $request->id)->first();
        $persona->delete();
        return response()->json([
            'ok' => 1,
            'mensaje' => 'Persona eliminado satisfactoriamente'
        ],200);
    }
    public function todos(){
        $personas = Persona::with('grupo:id,titulo')->get();
        return $personas;
    }
    public function listar(Request $request){
        $buscar = mb_strtoupper($request->buscar);
        $paginacion = $request->paginacion;
        return Persona::with(['padre:id,nombre', 'grupo:id,titulo'])->whereRaw('UPPER(nombre) LIKE ?', ['%'.$buscar.'%'])
            ->paginate($paginacion);
    }
}
