<?php

namespace App\Http\Controllers;

use App\Models\Estacion;
use App\Models\Lectura;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use Symfony\Component\Process\Process;

class DataNormalizationController extends Controller
{
    public function index(Request $request)
    {
        $estaciones = $this->estacionesDisponibles($request);

        return view('dashboard.normalizacion', compact('estaciones'));
    }

    public function normalize(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv,txt|max:20480',
                'estacion_id' => 'nullable|exists:estaciones,id',
            ], [
                'file.required' => 'Por favor, selecciona un archivo.',
                'file.mimes' => 'El archivo debe ser Excel (.xlsx, .xls) o CSV.',
                'file.max' => 'El archivo no debe exceder 20MB.',
            ]);

            $estaciones = $this->estacionesDisponibles($request);
            $estacionId = $request->integer('estacion_id') ?: $estaciones->first()?->id;

            if ($estacionId && ! $estaciones->contains('id', $estacionId)) {
                return back()->with('error', 'No tienes acceso a esa estación.');
            }

            $file = $request->file('file');
            $filename = 'temp_'.time().'_'.$file->getClientOriginalName();
            $filePath = Storage::disk('local')->putFileAs('uploads', $file, $filename);
            $absolutePath = Storage::disk('local')->path($filePath);

            $pythonScript = base_path('python/normalize_data.py');
            $outputFile = Storage::disk('local')->path('uploads/normalized_'.time().'.xlsx');

            // No dependemos de ninguna variable en .env: si el usuario no define
            // PYTHON_BIN, usamos 'python' en Windows y 'python3' en Linux/Mac,
            // que es lo que normalmente existe en cada plataforma por defecto.
            $pythonBin = env('PYTHON_BIN') ?: (PHP_OS_FAMILY === 'Windows' ? 'python' : 'python3');

            $process = new Process([$pythonBin, $pythonScript, $absolutePath, $outputFile]);

            // En Windows, Apache/XAMPP a veces lanza el proceso hijo con un entorno
            // "pelado" que no incluye SystemRoot/TEMP. scikit-learn (via joblib/loky)
            // necesita esas variables para inicializar sockets internos (Winsock);
            // sin ellas falla con OSError WinError 10106. Esto se resuelve leyendo
            // el entorno real del sistema con getenv() -- no requiere tocar .env.
            $process->setEnv(array_filter([
                'SystemRoot' => getenv('SystemRoot'),
                'WINDIR' => getenv('WINDIR'),
                'TEMP' => getenv('TEMP') ?: sys_get_temp_dir(),
                'TMP' => getenv('TMP') ?: sys_get_temp_dir(),
                'PATH' => getenv('PATH'),
                'LOKY_MAX_CPU_COUNT' => '1',
                'JOBLIB_MULTIPROCESSING' => '0',
            ], fn ($value) => $value !== false && $value !== null));
            $process->setTimeout(300);
            $process->run();

            Storage::disk('local')->delete($filePath);

            if (! $process->isSuccessful()) {
                return back()->with('error', 'Error al preprocesar los datos: '.$process->getErrorOutput());
            }

            $result = json_decode($process->getOutput(), true) ?? [];
            if (! empty($result['error'])) {
                return back()->with('error', $result['error']);
            }

            $summary = $result['summary'] ?? [];
            $lastRecord = $result['last_record'] ?? [];
            $outliers = $result['outliers'] ?? [];
            $preview = $result['preview'] ?? [];
            $importados = 0;

            if (($outliers['sklearn_disponible'] ?? true) === false) {
                \Illuminate\Support\Facades\Log::warning('Isolation Forest / DBSCAN no se ejecutaron: sklearn no disponible en el Python usado.', [
                    'python_bin' => $pythonBin,
                    'python_executable' => $result['python_executable'] ?? null,
                    'sklearn_error' => $outliers['sklearn_error'] ?? null,
                ]);
            }

            $csvFile = $result['csv_file'] ?? null;
            if ($estacionId && $csvFile && file_exists($csvFile)) {
                $importados = $this->importarCsv($csvFile, $estacionId);
            }

            session([
                'normalized_file' => $outputFile,
                'normalized_filename' => 'datos_preprocesados_'.date('Y-m-d_H-i-s').'.xlsx',
                'normalization_summary' => $summary,
                'normalized_last_record' => $lastRecord,
                'normalization_outliers' => $outliers,
                'normalization_preview' => $preview,
                'normalization_imported' => $importados,
            ]);

            $mensaje = 'Datos preprocesados exitosamente.';
            if ($importados > 0) {
                $mensaje .= " Se guardaron {$importados} lecturas en la estación seleccionada.";
            }

            return back()->with([
                'success' => $mensaje,
                'summary' => $summary,
            ]);
        } catch (Exception $e) {
            return back()->with('error', 'Error: '.$e->getMessage());
        }
    }

    public function download()
    {
        $file = session('normalized_file');
        $filename = session('normalized_filename');

        if (! $file || ! file_exists($file)) {
            return back()->with('error', 'El archivo no está disponible. Por favor, procesa los datos nuevamente.');
        }

        return response()->download($file, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function estacionesDisponibles(Request $request)
    {
        $user = $request->user();
        $query = Estacion::with('finca')->orderBy('nombre_estacion');

        if ($user->esAgricultor() && ! $user->esAdmin()) {
            $fincaIds = $user->fincas()->pluck('id');
            $query->whereIn('finca_id', $fincaIds);
        }

        return $query->get();
    }

    private function importarCsv(string $csvFile, int $estacionId): int
    {
        $csv = Reader::createFromPath($csvFile, 'r');
        $csv->setHeaderOffset(0);

        $importados = 0;
        $batch = [];
        $now = now();

        foreach ($csv->getRecords() as $record) {
            if (empty($record['fecha_lectura']) || $record['temp_externa'] === '' || $record['temp_externa'] === null) {
                continue;
            }

            $update = [
                'intervalo', 'temp_interna', 'humedad_interna', 'temp_externa', 'humedad_externa',
                'presion_relativa', 'presion_absoluta', 'viento_vel', 'viento_rafaga', 'viento_dir',
                'punto_rocio', 'sensacion_termica', 'lluvia_hora', 'lluvia_dia', 'lluvia_semana',
                'lluvia_mes', 'lluvia_total', 'outlier_iqr', 'outlier_zscore',
                'outlier_isolation_forest', 'cluster_dbscan', 'outlier_consenso', 'updated_at',
            ];

            $batch[] = [
                'estacion_id' => $estacionId,
                'fecha_lectura' => $record['fecha_lectura'],
                'intervalo' => $this->toNumber($record['intervalo'] ?? null, true),
                'temp_interna' => $this->toNumber($record['temp_interna'] ?? null),
                'humedad_interna' => $this->toNumber($record['humedad_interna'] ?? null, true),
                'temp_externa' => $this->toNumber($record['temp_externa'] ?? null),
                'humedad_externa' => $this->toNumber($record['humedad_externa'] ?? null, true),
                'presion_relativa' => $this->toNumber($record['presion_relativa'] ?? null),
                'presion_absoluta' => $this->toNumber($record['presion_absoluta'] ?? null),
                'viento_vel' => $this->toNumber($record['viento_vel'] ?? $record['vel_viento'] ?? null),
                'viento_rafaga' => $this->toNumber($record['viento_rafaga'] ?? $record['rafaga'] ?? null),
                'viento_dir' => $record['viento_dir'] ?? $record['direccion_viento'] ?? null,
                'punto_rocio' => $this->toNumber($record['punto_rocio'] ?? null),
                'sensacion_termica' => $this->toNumber($record['sensacion_termica'] ?? null),
                'lluvia_hora' => $this->toNumber($record['lluvia_hora'] ?? null) ?? 0,
                'lluvia_dia' => $this->toNumber($record['lluvia_dia'] ?? $record['lluvia_24h'] ?? null) ?? 0,
                'lluvia_semana' => $this->toNumber($record['lluvia_semana'] ?? null) ?? 0,
                'lluvia_mes' => $this->toNumber($record['lluvia_mes'] ?? null) ?? 0,
                'lluvia_total' => $this->toNumber($record['lluvia_total'] ?? null) ?? 0,
                'outlier_iqr' => (int) ($record['outlier_iqr'] ?? 0),
                'outlier_zscore' => (int) ($record['outlier_zscore'] ?? 0),
                'outlier_isolation_forest' => (int) ($record['outlier_isolation_forest'] ?? 0),
                'cluster_dbscan' => $this->toNumber($record['cluster_dbscan'] ?? null, true),
                'outlier_consenso' => (int) ($record['outlier_consenso'] ?? 0),
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $importados++;

            if (count($batch) >= 500) {
                Lectura::upsert($batch, ['estacion_id', 'fecha_lectura'], $update);
                $batch = [];
            }
        }

        if ($batch) {
            Lectura::upsert($batch, ['estacion_id', 'fecha_lectura'], $update);
        }

        return $importados;
    }

    private function toNumber($value, bool $integer = false): mixed
    {
        if ($value === null || $value === '' || $value === 'nan') {
            return null;
        }

        return $integer ? (int) $value : (float) $value;
    }
}