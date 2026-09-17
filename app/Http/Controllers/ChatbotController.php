<?php

namespace App\Http\Controllers;

use App\Models\Lectura;
use App\Services\WeatherPredictionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    public function message(Request $request, WeatherPredictionService $predictionService): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $message = trim($validated['message']);
        $apiKey = config('services.ai.key');
        $prediction = $predictionService->forecast(3);

        if (! $apiKey) {
            return response()->json([
                'reply' => $this->localReply($message),
                'source' => 'local',
            ]);
        }

        try {
            $response = Http::timeout(25)
                ->withToken($apiKey)
                ->acceptJson()
                ->post(config('services.ai.url'), [
                    'model' => config('services.ai.model'),
                    'temperature' => 0.2,
                    'max_tokens' => 500,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $this->systemPrompt($prediction),
                        ],
                        [
                            'role' => 'user',
                            'content' => $message,
                        ],
                    ],
                ]);

            $reply = $response->json('choices.0.message.content');

            if ($response->successful() && is_string($reply) && trim($reply) !== '') {
                return response()->json([
                    'reply' => trim($reply),
                    'source' => 'ai',
                ]);
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        return response()->json([
            'reply' => $this->localReply($message),
            'source' => 'local',
        ]);
    }

    private function systemPrompt(array $prediction): string
    {
        $latest = Lectura::latest('fecha_lectura')->first();
        $currentData = $latest
            ? sprintf(
                "\n\nÚltima lectura registrada disponible (no es un pronóstico): fecha %s, temperatura %s °C, humedad %s %%, viento %s m/s, lluvia 24h %s mm.",
                $latest->fecha_lectura->timezone(config('app.timezone'))->format('d/m/Y H:i'),
                $latest->temp_externa ?? 'sin dato',
                $latest->humedad_externa ?? 'sin dato',
                $latest->viento_vel ?? 'sin dato',
                $latest->lluvia_dia ?? 'sin dato'
            )
            : "\n\nNo hay lecturas registradas disponibles en este momento.";

        $instructions = <<<'PROMPT'
Eres SIPRAC Asistente, el asistente técnico de SIPRAC. Responde en español claro, breve y práctico.

SIPRAC es un sistema inteligente de protección agroclimática para agricultores de la provincia de Ubaté, Colombia. Usa sensores IoT para monitorear temperatura, humedad relativa, lluvia y humedad del suelo; analiza datos climáticos, anticipa riesgos como heladas y lluvias fuertes, genera alertas y puede activar mecanismos automatizados de protección de cultivos.

Puedes explicar el funcionamiento del proyecto, sus módulos, datos, sensores, normalización, alertas y conceptos de meteorología aplicada. No inventes lecturas actuales, pronósticos, alertas ni acciones ejecutadas. Si preguntan por datos en tiempo real, indica que deben consultarse en el dashboard o en las lecturas registradas. No afirmes que activaste mecanismos ni que sustituyes a un meteorólogo o agrónomo. Para decisiones de riesgo, recomienda validar las condiciones locales y seguir los protocolos del agricultor.
PROMPT;

        $forecastData = $prediction['available']
            ? "\n\nEstimación local de SIPRAC para las próximas horas: " . collect($prediction['items'])
                ->map(fn (array $item): string => sprintf('%s: %s °C, %s%% humedad, %s', $item['label'], $item['temperature'] ?? 'sin dato', $item['humidity'] ?? 'sin dato', $item['risk']))
                ->implode('; ') . ". Explícala como estimación, no como certeza."
            : "\n\nNo hay suficientes datos para una estimación horaria local.";

        return $instructions.$currentData.$forecastData;
    }

    private function localReply(string $message): string
    {
        $text = Str::lower(Str::ascii($message));

        if (Str::contains($text, ['helada', 'heladas'])) {
            return 'Una helada ocurre cuando la temperatura del aire desciende lo suficiente para afectar los cultivos. SIPRAC combina temperatura, humedad, viento y condiciones locales para identificar riesgo. Revisa las alertas y activa el mecanismo de protección solo según el protocolo de tu finca.';
        }

        if (Str::contains($text, ['lluvia', 'lluvias', 'precipitacion', 'precipitaciones'])) {
            return 'SIPRAC monitorea la lluvia por hora y acumulada para detectar condiciones de precipitación intensa. La lluvia, el suelo y la topografía de cada finca deben analizarse juntos antes de decidir acciones de drenaje o protección.';
        }

        if (Str::contains($text, ['humedad', 'humedad del suelo'])) {
            return 'La humedad relativa describe el vapor de agua en el aire; la humedad del suelo indica la disponibilidad de agua para el cultivo. Ambas variables ayudan a interpretar estrés hídrico, riesgo de enfermedades y condiciones favorables para heladas.';
        }

        if (Str::contains($text, ['sensor', 'iot', 'estacion', 'estacion meteorologica'])) {
            return 'Los sensores IoT de SIPRAC envían variables como temperatura, humedad, lluvia y humedad del suelo. Las estaciones pertenecen a una finca y sus lecturas se usan para observar condiciones, generar alertas y apoyar decisiones agrícolas.';
        }

        if (Str::contains($text, ['como funciona', 'que es siprac', 'siprac', 'proyecto'])) {
            return 'SIPRAC es un sistema de protección agroclimática para Ubaté. Recibe datos de sensores, los normaliza, analiza riesgos de heladas y lluvias fuertes, muestra información en el dashboard y genera alertas para reducir pérdidas en los cultivos.';
        }

        if (Str::contains($text, ['temperatura', 'clima', 'meteorologia', 'meteorologia'])) {
            return 'La temperatura debe interpretarse junto con humedad, viento, lluvia y etapa del cultivo. En SIPRAC, estas variables permiten vigilar cambios bruscos y construir alertas agroclimáticas; el dashboard muestra las lecturas disponibles para cada estación.';
        }

        return 'Puedo ayudarte con SIPRAC, sensores IoT, estaciones, lecturas climáticas, temperatura, humedad, lluvia, humedad del suelo, heladas, alertas y normalización de datos. Pregunta, por ejemplo: “¿Cómo detecta SIPRAC una helada?”';
    }
}
