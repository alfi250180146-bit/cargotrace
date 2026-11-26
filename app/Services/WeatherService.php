<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    // Kode cuaca WMO yang dipakai Open-Meteo
    private const CODES = [
        0 => 'Clear sky',
        1 => 'Mainly clear',
        2 => 'Partly cloudy',
        3 => 'Overcast',
        45 => 'Fog',
        48 => 'Rime fog',
        51 => 'Light drizzle',
        53 => 'Drizzle',
        55 => 'Dense drizzle',
        61 => 'Light rain',
        63 => 'Rain',
        65 => 'Heavy rain',
        71 => 'Light snow',
        73 => 'Snow',
        75 => 'Heavy snow',
        80 => 'Rain showers',
        81 => 'Rain showers',
        82 => 'Violent rain showers',
        95 => 'Thunderstorm',
        96 => 'Thunderstorm with hail',
        99 => 'Thunderstorm with heavy hail',
    ];

    /**
     * Cuaca saat ini di satu koordinat.
     * Selalu mengembalikan array: ['ok' => true, ...data] atau ['ok' => false, 'message' => ...].
     * Hanya hasil sukses yang disimpan di cache (10 menit).
     */
    public function current(float $latitude, float $longitude): array
    {
        $key = sprintf('weather:%.2f:%.2f', $latitude, $longitude);

        if ($cached = Cache::get($key)) {
            return $cached;
        }

        try {
            $response = Http::baseUrl(config('weather.url'))
                ->timeout(config('weather.timeout'))
                ->connectTimeout(3)
                ->acceptJson()
                ->get('/v1/forecast', [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'current' => 'temperature_2m,wind_speed_10m,weather_code',
                    'timezone' => 'auto',
                ]);
        } catch (ConnectionException $e) {
            Log::warning('Weather service unreachable: '.$e->getMessage());

            return ['ok' => false, 'message' => 'Weather service is unreachable or too slow right now.'];
        }

        if ($response->failed()) {
            Log::warning('Weather service error: HTTP '.$response->status());

            return ['ok' => false, 'message' => 'Weather service returned an error (HTTP '.$response->status().').'];
        }

        $current = $response->json('current');

        if (! is_array($current) || ! isset($current['temperature_2m'])) {
            return ['ok' => false, 'message' => 'No weather data is available for this location.'];
        }

        $result = [
            'ok' => true,
            'temperature' => (float) $current['temperature_2m'],
            'wind_speed' => isset($current['wind_speed_10m']) ? (float) $current['wind_speed_10m'] : null,
            'summary' => self::CODES[$current['weather_code'] ?? -1] ?? 'Unknown conditions',
            'observed_at' => $current['time'] ?? null,
        ];

        Cache::put($key, $result, now()->addMinutes(10));

        return $result;
    }
}