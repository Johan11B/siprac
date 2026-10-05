<?php

namespace App\Http\Controllers;

use App\Models\Lectura;
use App\Services\WeatherPredictionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    /**
     * Prioridad de proveedores:
     *  1. Gemini (Google AI Studio)  -> si existe GEMINI_API_KEY
     *  2. OpenAI                     -> si existe AI_API_KEY
     *  3. Respuestas locales         -> si no hay ninguna key o la IA falla
     */
    public function message(Request $request, WeatherPredictionService $predictionService): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $message = trim($validated['message']);
        $prediction = $predictionService->forecast(3);
        $systemPrompt = $this->systemPrompt($prediction);

        try {
            $reply = null;
            $source = 'local';

            if (config('services.gemini.key')) {
                $reply = $this->askGemini($systemPrompt, $message);
                $source = 'gemini';
            } elseif (config('services.ai.key')) {
                $reply = $this->askOpenAi($systemPrompt, $message);
                $source = 'openai';
            }

            if (is_string($reply) && trim($reply) !== '') {
                return response()->json([
                    'reply' => trim($reply),
                    'source' => $source,
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

    /**
     * Llamada nativa a Google AI Studio (generateContent).
     */
    private function askGemini(string $systemPrompt, string $message): ?string
    {
        $response = Http::timeout(25)
            ->withHeaders(['x-goog-api-key' => config('services.gemini.key')])
            ->acceptJson()
            ->post(config('services.gemini.url'), [
                'systemInstruction' => [
                    'parts' => [['text' => $systemPrompt]],
                ],
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [['text' => $message]],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'maxOutputTokens' => 800,
                ],
            ]);

        if (! $response->successful()) {
            Log::warning('Gemini respondió con error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        return $response->json('candidates.0.content.parts.0.text');
    }

    /**
     * Llamada a OpenAI (la configuración original del proyecto).
     */
    private function askOpenAi(string $systemPrompt, string $message): ?string
    {
        $response = Http::timeout(25)
            ->withToken(config('services.ai.key'))
            ->acceptJson()
            ->post(config('services.ai.url'), [
                'model' => config('services.ai.model'),
                'temperature' => 0.2,
                'max_tokens' => 500,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $message],
                ],
            ]);

        if (! $response->successful()) {
            Log::warning('OpenAI respondió con error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        return $response->json('choices.0.message.content');
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
Eres SIPRAC Asistente, el asistente técnico de SIPRAC.

IDIOMA: detecta el idioma del mensaje del usuario y responde siempre en ese mismo idioma (español, inglés, portugués, francés, italiano, alemán, etc.). Si el usuario cambia de idioma, cambia tú también. Sé claro, breve y práctico.

SIPRAC es un sistema inteligente de protección agroclimática para agricultores de la provincia de Ubaté, Colombia. Usa sensores IoT para monitorear temperatura, humedad relativa, lluvia y humedad del suelo; analiza datos climáticos, anticipa riesgos como heladas y lluvias fuertes, genera alertas y puede activar mecanismos automatizados de protección de cultivos.

Puedes explicar el funcionamiento del proyecto, sus módulos, datos, sensores, normalización, alertas y conceptos de meteorología aplicada. No inventes lecturas actuales, pronósticos, alertas ni acciones ejecutadas. Si preguntan por datos en tiempo real, indica que deben consultarse en el dashboard o en las lecturas registradas. No afirmes que activaste mecanismos ni que sustituyes a un meteorólogo o agrónomo. Para decisiones de riesgo, recomienda validar las condiciones locales y seguir los protocolos del agricultor.

ALCANCE: si la pregunta no se relaciona con SIPRAC, agricultura, clima o meteorología, indica amablemente (en el idioma del usuario) que solo puedes ayudar con esos temas.
PROMPT;

        $forecastData = $prediction['available']
            ? "\n\nEstimación local de SIPRAC para las próximas horas: ".collect($prediction['items'])
                ->map(fn (array $item): string => sprintf('%s: %s °C, %s%% humedad, %s', $item['label'], $item['temperature'] ?? 'sin dato', $item['humidity'] ?? 'sin dato', $item['risk']))
                ->implode('; ').'. Explícala como estimación, no como certeza.'
            : "\n\nNo hay suficientes datos para una estimación horaria local.";

        return $instructions.$currentData.$forecastData;
    }

    /**
     * Respuestas de respaldo cuando no hay IA disponible.
     * Responde en español o inglés según el mensaje.
     */
    private function localReply(string $message): string
    {
        $text = Str::lower(Str::ascii($message));
        $english = Str::contains($text, [' the ', 'how ', 'what ', 'does ', 'frost', 'rain', 'sensor', 'weather', 'humidity', ' is ', ' are ']);

        if (Str::contains($text, ['helada', 'heladas', 'frost'])) {
            return $english
                ? 'Frost happens when air temperature drops enough to damage crops. SIPRAC combines temperature, humidity, wind and local conditions to identify risk. Check the alerts and activate protection only according to your farm protocol.'
                : 'Una helada ocurre cuando la temperatura del aire desciende lo suficiente para afectar los cultivos. SIPRAC combina temperatura, humedad, viento y condiciones locales para identificar riesgo. Revisa las alertas y activa el mecanismo de protección solo según el protocolo de tu finca.';
        }

        if (Str::contains($text, ['lluvia', 'lluvias', 'precipitacion', 'precipitaciones', 'rain'])) {
            return $english
                ? 'SIPRAC monitors hourly and accumulated rainfall to detect heavy precipitation. Rain, soil and terrain must be analyzed together before deciding on drainage or protection actions.'
                : 'SIPRAC monitorea la lluvia por hora y acumulada para detectar condiciones de precipitación intensa. La lluvia, el suelo y la topografía de cada finca deben analizarse juntos antes de decidir acciones de drenaje o protección.';
        }

        if (Str::contains($text, ['humedad', 'humidity', 'moisture'])) {
            return $english
                ? 'Relative humidity describes water vapor in the air; soil moisture indicates water availability for the crop. Both help interpret water stress, disease risk and frost-prone conditions.'
                : 'La humedad relativa describe el vapor de agua en el aire; la humedad del suelo indica la disponibilidad de agua para el cultivo. Ambas variables ayudan a interpretar estrés hídrico, riesgo de enfermedades y condiciones favorables para heladas.';
        }

        if (Str::contains($text, ['sensor', 'iot', 'estacion'])) {
            return $english
                ? 'SIPRAC IoT sensors send variables such as temperature, humidity, rainfall and soil moisture. Stations belong to a farm and their readings are used to generate alerts and support agricultural decisions.'
                : 'Los sensores IoT de SIPRAC envían variables como temperatura, humedad, lluvia y humedad del suelo. Las estaciones pertenecen a una finca y sus lecturas se usan para observar condiciones, generar alertas y apoyar decisiones agrícolas.';
        }

        if (Str::contains($text, ['como funciona', 'que es siprac', 'siprac', 'proyecto', 'how does', 'what is siprac'])) {
            return $english
                ? 'SIPRAC is an agroclimatic protection system for Ubaté. It receives sensor data, normalizes it, analyzes frost and heavy-rain risk, shows information on the dashboard and generates alerts to reduce crop losses.'
                : 'SIPRAC es un sistema de protección agroclimática para Ubaté. Recibe datos de sensores, los normaliza, analiza riesgos de heladas y lluvias fuertes, muestra información en el dashboard y genera alertas para reducir pérdidas en los cultivos.';
        }

        if (Str::contains($text, ['temperatura', 'clima', 'meteorologia', 'temperature', 'weather'])) {
            return $english
                ? 'Temperature should be interpreted together with humidity, wind, rain and crop stage. In SIPRAC these variables help watch sudden changes and build agroclimatic alerts; the dashboard shows the readings for each station.'
                : 'La temperatura debe interpretarse junto con humedad, viento, lluvia y etapa del cultivo. En SIPRAC, estas variables permiten vigilar cambios bruscos y construir alertas agroclimáticas; el dashboard muestra las lecturas disponibles para cada estación.';
        }

        return $english
            ? 'The full AI assistant is currently unavailable. I can still help with SIPRAC, IoT sensors, stations, weather readings, frost, rain, humidity and alerts. Try: "How does SIPRAC detect frost?"'
            : 'El asistente con IA no está disponible en este momento. Aun así puedo ayudarte con SIPRAC, sensores IoT, estaciones, lecturas climáticas, heladas, lluvias, humedad y alertas. Pregunta, por ejemplo: “¿Cómo detecta SIPRAC una helada?”';
    }
}
