<?php

namespace Database\Seeders;

use App\Models\Departamento;
use App\Models\Distrito;
use App\Models\Provincia;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistritoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = fopen(base_path('archivos/ubigeo.csv'), 'r');
        // $csvFile = fopen(base_path("storage/app/public/archivos-locales/Ubigeo-Peru.csv"),"r");

        $nro_registros = -1;
        while (($datum = fgetcsv($csvFile, 555, ',')) !== false)
        {
            $nro_registros +=1;
        }
        fclose($csvFile);

        $this->command->getOutput()->writeln('Iniciando Importación de Ubigeo...');

        DB::unprepared("SET FOREIGN_KEY_CHECKS = 0;");
        Distrito::truncate();
        DB::statement("SET foreign_key_checks=1");
        $this->command->getOutput()->writeln('Datos Limpiados de Distritos...');

        DB::unprepared("SET FOREIGN_KEY_CHECKS = 0;");
        Provincia::truncate();
        DB::statement("SET foreign_key_checks=1");
        $this->command->getOutput()->writeln('Datos Limpiados de Provincias...');

        DB::unprepared("SET FOREIGN_KEY_CHECKS = 0;");
        Departamento::truncate();
        DB::statement("SET foreign_key_checks=1");
        $this->command->getOutput()->writeln('Datos Limpiados de Departamentos...');


        $csvFile2 = fopen(base_path('archivos/ubigeo.csv'),"r");

        $progressBar = $this->command->getOutput()->createProgressBar($nro_registros);
        $this->command->getOutput()->writeln("Importando Datos de Ubigeo... ");

        $progressBar->start();
        $firstLine = true;

        while(($fila  = fgetcsv($csvFile2,2000,";")) !== false) {
            if(!$firstLine){
                $cod_departamento =substr($fila[0],0,2);
                $cod_provincia = substr($fila[0],0,4);
                $cod_distrito = $fila[0];
                $departamento =Departamento::where('codigo',$cod_departamento)->first();
                if(!$departamento)
                {
                    $departamento = Departamento::Create([
                        'codigo' => $cod_departamento,
                        'nombre' => $fila[3]
                    ]);
                }
                $provincia = Provincia::where('codigo',$cod_provincia)->first();
                if(!$provincia && substr($cod_provincia,-2) != '00')
                {
                    $provincia = Provincia::Create([
                        'codigo' => $cod_provincia,
                        'departamento_id' => $departamento->id,
                        'nombre' => $fila[2]
                    ]);
                }

                $distrito = Distrito::where('ubigeo', $cod_distrito)->first();
                if(!$distrito && substr($cod_distrito,-2) != '00')
                {
                    $distrito  = Distrito::Create([
                        'ubigeo' => $cod_distrito,
                        'provincia_id' => $provincia->id,
                        'nombre' => $fila[1]
                    ]);
                }

                usleep(1000);
                $progressBar->advance();

            }
            $firstLine = false;

        }

        fclose($csvFile2);
        $progressBar->finish();
        $this->command->getOutput()->writeln("");
        $this->command->getOutput()->writeln("Importación Finalizada");
    }
}
