<?php

namespace App\Services;

use App\Models\Lectura;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WeatherPredictionService
{
    public function forecast(int $hours = 12): array
    {
        $timezone = config('app.timezone', 'America/Bogota');
        $now = Carbon::now($timezone)->startOfHour();
        $readings = Lectura::query()
            ->where('fecha_lectura', '>=', $now->copy()->subDays(14)->setTimezone('UTC'))
            ->whereNotNull('temp_externa')
            ->orderBy('fecha_lectura')
            ->get([
                'fecha_lectura',
                'temp_externa',
                'humedad_externa',
                'viento_vel',
                'lluvia_hora',
            ]);

        if ($readings->count() < 6) {
            return [
                'available' => false,
                'message' => 'Se necesitan al menos 6 lecturas climáticas con temperatura para estimar el pronóstico.',
                'generated_at' => $now->toIso8601String(),
                'timezone' => $timezone,
                'data_points' => $readings->count(),
                'items' => [],
                'source' => 'database',
            ];
        }

        $localReadings = $readings->map(function (Lectura $reading) use ($timezone): array {
            return [
                'date' => $reading->fecha_lectura->copy()->setTimezone($timezone),
                'temp' => $this->number($reading->temp_externa),
                'humidity' => $this->number($reading->humedad_externa),
                'wind' => $this->number($reading->viento_vel),
                'rain' => $this->number($reading->lluvia_hora),
            ];
        });

        $recent = $localReadings->slice(-6);
        $items = collect();

        for ($offset = 1; $offset <= $hours; $offset++) {
            $target = $now->copy()->addHours($offset);
            $sameHour = $localReadings->filter(fn (array $reading): bool => $reading['date']->hour === $target->hour);
            $sample = $sameHour->isNotEmpty() ? $sameHour : $recent;
            $historicalWeight = $sameHour->isNotEmpty() ? 0.7 : 0.35;
            $recentWeight = 1 - $historicalWeight;

            $items->push([
                'datetime' => $target->toIso8601String(),
                'label' => $target->format('H:i'),
                'date_label' => $target->format('d/m'),
                'temperature' => $this->blend($sample, $recent, 'temp', $historicalWeight, $recentWeight),
                'humidity' => $this->blend($sample, $recent, 'humidity', $historicalWeight, $recentWeight),
                'wind' => $this->blend($sample, $recent, 'wind', $historicalWeight, $recentWeight),
                'rain' => $this->blend($sample, $recent, 'rain', $historicalWeight, $recentWeight),
                'risk' => $this->risk($sample, $recent, $historicalWeight, $recentWeight),
                'confidence' => $sameHour->count() >= 3 ? 'Media' : 'Baja',
            ]);
        }

        return [
            'available' => true,
            'message' => 'Estimación estadística basada en lecturas cargadas durante los últimos 14 días.',
            'generated_at' => $now->toIso8601String(),
            'timezone' => $timezone,
            'data_points' => $readings->count(),
            'items' => $items->all(),
            'source' => 'database',
        ];
    }

    private function blend(Collection $historical, Collection $recent, string $key, float $historicalWeight, float $recentWeight): ?float
    {
        $historicalAverage = $this->average($historical, $key);
        $recentAverage = $this->average($recent, $key);

        if ($historicalAverage === null) {
            return $recentAverage;
        }

        if ($recentAverage === null) {
            return $historicalAverage;
        }

        return round(($historicalAverage * $historicalWeight) + ($recentAverage * $recentWeight), 1);
    }

    private function average(Collection $readings, string $key): ?float
    {
        $values = $readings->pluck($key)->filter(fn ($value): bool => $value !== null);

        return $values->isNotEmpty() ? round((float) $values->avg(), 1) : null;
    }

    private function risk(Collection $historical, Collection $recent, float $historicalWeight, float $recentWeight): string
    {
        $temperature = $this->blend($historical, $recent, 'temp', $historicalWeight, $recentWeight);
        $rain = $this->blend($historical, $recent, 'rain', $historicalWeight, $recentWeight);

        if ($temperature !== null && $temperature <= 2) {
            return 'Riesgo de helada';
        }

        if ($rain !== null && $rain >= 10) {
            return 'Lluvia intensa';
        }

        return 'Condición estable';
    }

    private function number(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }
}
