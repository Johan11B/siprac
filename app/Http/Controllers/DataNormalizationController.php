<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Exception;

class DataNormalizationController extends Controller
{
    /**
     * Muestra la vista de normalización de datos
     */
    public function index()
    {
        return view('dashboard.normalizacion');
    }

    /**
     * Procesa el archivo Excel y normaliza los datos
     */
    public function normalize(Request $request)
    {
        try {
            // Validar que se ha subido un archivo
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv|max:10240', // Máximo 10MB
            ], [
                'file.required' => 'Por favor, selecciona un archivo.',
                'file.mimes' => 'El archivo debe ser Excel (.xlsx, .xls) o CSV.',
                'file.max' => 'El archivo no debe exceder 10MB.',
            ]);

            // Guardar el archivo temporalmente
            $file = $request->file('file');
            $filename = 'temp_' . time() . '_' . $file->getClientOriginalName();
            $filePath = Storage::disk('local')->putFileAs('uploads', $file, $filename);
            $absolutePath = Storage::disk('local')->path($filePath);

            // Ejecutar el script Python
            $pythonScript = base_path('python/normalize_data.py');
            $outputFile = Storage::disk('local')->path('uploads/normalized_' . time() . '.xlsx');
            
            // Usar python.exe directamente (Windows compatible)
            $pythonExecutable = 'python';
            
            $process = new Process([$pythonExecutable, $pythonScript, $absolutePath, $outputFile]);
            $process->setTimeout(300); // 5 minutos timeout
            $process->run();

            // Verificar si el script se ejecutó correctamente
            if (!$process->isSuccessful()) {
                // Limpiar archivo temporal
                Storage::disk('local')->delete($filePath);
                
                return back()->with('error', 'Error al normalizar los datos: ' . $process->getErrorOutput());
            }

            // Leer los datos normalizados para mostrar resumen
            $resultJson = $process->getOutput();
            $result = json_decode($resultJson, true);
            $summary = $result['summary'] ?? [];
            $lastRecord = $result['last_record'] ?? [];

            // Limpiar archivo temporal
            Storage::disk('local')->delete($filePath);

            // Guardar información en sesión para la descarga y últimas condiciones
            session([
                'normalized_file' => $outputFile,
                'normalized_filename' => 'datos_normalizados_' . date('Y-m-d_H-i-s') . '.xlsx',
                'normalization_summary' => $summary,
                'normalized_last_record' => $lastRecord,
            ]);

            return back()->with([
                'success' => 'Datos normalizados exitosamente.',
                'summary' => $summary,
            ]);

        } catch (Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Descarga el archivo Excel normalizado
     */
    public function download()
    {
        $file = session('normalized_file');
        $filename = session('normalized_filename');

        if (!$file || !file_exists($file)) {
            return back()->with('error', 'El archivo no está disponible. Por favor, normaliza datos nuevamente.');
        }

        return response()->download($file, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
