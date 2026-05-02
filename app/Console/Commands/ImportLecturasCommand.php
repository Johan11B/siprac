<?php

namespace App\Console\Commands;

use App\Models\Estacion;
use App\Models\Lectura;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class ImportLecturasCommand extends Command
{
    protected $signature = 'import:lecturas {estacion_id}';

    protected $description = 'Importa lecturas climáticas desde CSV limpio';

    public function handle()
    {
        $estacionId = $this->argument('estacion_id');
        $archivo = storage_path('app/imports/lecturas_limpias.csv');

        $this->info('========================================');
        $this->info('IMPORTACIÓN DE LECTURAS - SIPRAC');
        $this->info('========================================');

        // 1. Validar que la estación existe
        $estacion = Estacion::find($estacionId);
        if (! $estacion) {
            $this->error("Estación con ID {$estacionId} no encontrada.");
            $this->info('Ejecuta: php artisan tinker y crea una estación.');

            return 1;
        }
        $this->info(" Estación: {$estacion->nombre_estacion} (ID: {$estacionId})");

        // 2. Validar que el archivo existe
        if (! file_exists($archivo)) {
            $this->error("Archivo no encontrado: {$archivo}");
            $this->info('Asegúrate de copiar el CSV a storage/app/imports/');

            return 1;
        }
        $this->info('Archivo encontrado: '.basename($archivo));

        // 3. Leer el CSV
        $this->info('Leyendo archivo CSV...');
        $csv = Reader::createFromPath($archivo, 'r');
        $csv->setHeaderOffset(0);
        $records = $csv->getRecords();
        $total = iterator_count($records);
        $this->info("Total de registros a importar: {$total}");

        // 4. Reiniciar el puntero del CSV
        $csv = Reader::createFromPath($archivo, 'r');
        $csv->setHeaderOffset(0);
        $records = $csv->getRecords();

        // 5. Insertar en lotes
        $batch = [];
        $batchSize = 500;
        $importados = 0;
        $fallidos = 0;

        DB::beginTransaction();

        try {
            foreach ($records as $record) {
                // Validar datos mínimos
                if (empty($record['fecha_lectura']) || empty($record['temp_externa'])) {
                    $fallidos++;

                    continue;
                }

                $batch[] = [
                    'estacion_id' => $estacionId,
                    'fecha_lectura' => $record['fecha_lectura'],
                    'intervalo' => ! empty($record['intervalo']) ? $record['intervalo'] : null,
                    'temp_interna' => ! empty($record['temp_interna']) ? (float) $record['temp_interna'] : null,
                    'humedad_interna' => ! empty($record['humedad_interna']) ? (int) $record['humedad_interna'] : null,
                    'temp_externa' => (float) $record['temp_externa'],
                    'humedad_externa' => ! empty($record['humedad_externa']) ? (int) $record['humedad_externa'] : null,
                    'presion_relativa' => ! empty($record['presion_relativa']) ? (float) $record['presion_relativa'] : null,
                    'presion_absoluta' => ! empty($record['presion_absoluta']) ? (float) $record['presion_absoluta'] : null,
                    'viento_vel' => ! empty($record['viento_vel']) ? (float) $record['viento_vel'] : null,
                    'viento_rafaga' => ! empty($record['viento_rafaga']) ? (float) $record['viento_rafaga'] : null,
                    'viento_dir' => ! empty($record['viento_dir']) ? $record['viento_dir'] : null,
                    'punto_rocio' => ! empty($record['punto_rocio']) ? (float) $record['punto_rocio'] : null,
                    'sensacion_termica' => ! empty($record['sensacion_termica']) ? (float) $record['sensacion_termica'] : null,
                    'lluvia_hora' => ! empty($record['lluvia_hora']) ? (float) $record['lluvia_hora'] : 0,
                    'lluvia_dia' => ! empty($record['lluvia_dia']) ? (float) $record['lluvia_dia'] : 0,
                    'lluvia_semana' => ! empty($record['lluvia_semana']) ? (float) $record['lluvia_semana'] : 0,
                    'lluvia_mes' => ! empty($record['lluvia_mes']) ? (float) $record['lluvia_mes'] : 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $importados++;

                if (count($batch) >= $batchSize) {
                    Lectura::insert($batch);
                    $this->info("Procesadas {$importados} lecturas...");
                    $batch = [];
                }
            }

            // Insertar el último lote
            if (! empty($batch)) {
                Lectura::insert($batch);
            }

            DB::commit();

            $this->info('========================================');
            $this->info('IMPORTACIÓN COMPLETADA');
            $this->info("mportadas: {$importados}");
            $this->info("Fallidas: {$fallidos}");
            $this->info('========================================');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error en la importación: '.$e->getMessage());

            return 1;
        }

        return 0;
    }
}
