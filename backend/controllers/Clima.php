<?php

namespace Controllers;

use Core\Controller;

class Clima extends Controller
{
    /**
     * Obtener clima actual de CDMX
     * Endpoint: /clima/cdmx
     */
    public function cdmx()
    {
        header('Content-Type: application/json');
        
        // Coordenadas de CDMX
        $lat = 19.4326;
        $lon = -99.1332;
        
        // Open-Meteo API (gratuita, sin API key)
        $url = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lon}&current=temperature_2m,weather_code&timezone=America/Mexico_City";
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'ignore_errors' => true
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            // Intentar con wttr.in como alternativa
            $wttrUrl = "https://wttr.in/Mexico+City?format=j1";
            $wttrResponse = @file_get_contents($wttrUrl, false, $context);
            
            if ($wttrResponse !== false) {
                $wttrData = json_decode($wttrResponse, true);
                if (isset($wttrData['current_condition'][0])) {
                    $current = $wttrData['current_condition'][0];
                    echo json_encode([
                        'success' => true,
                        'source' => 'wttr.in',
                        'temperature' => intval($current['temp_C']),
                        'weather_code' => $this->wttrToWmoCode($current['weatherCode']),
                        'description' => $current['lang_es'][0]['value'] ?? $current['weatherDesc'][0]['value'] ?? ''
                    ]);
                    return;
                }
            }
            
            echo json_encode(['success' => false, 'error' => 'No se pudo obtener el clima']);
            return;
        }
        
        $data = json_decode($response, true);
        
        if (isset($data['current'])) {
            echo json_encode([
                'success' => true,
                'source' => 'open-meteo',
                'temperature' => round($data['current']['temperature_2m']),
                'weather_code' => $data['current']['weather_code']
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
        }
    }
    
    /**
     * Convertir código de wttr.in a código WMO
     */
    private function wttrToWmoCode($wttrCode)
    {
        $map = [
            '113' => 0,  // Sunny/Clear
            '116' => 1,  // Partly cloudy
            '119' => 3,  // Cloudy
            '122' => 3,  // Overcast
            '143' => 45, // Mist
            '176' => 61, // Patchy rain
            '179' => 71, // Patchy snow
            '182' => 66, // Patchy sleet
            '185' => 66, // Patchy freezing drizzle
            '200' => 95, // Thundery outbreaks
            '227' => 77, // Blowing snow
            '230' => 75, // Blizzard
            '248' => 45, // Fog
            '260' => 45, // Freezing fog
            '263' => 51, // Patchy light drizzle
            '266' => 53, // Light drizzle
            '281' => 56, // Freezing drizzle
            '284' => 57, // Heavy freezing drizzle
            '293' => 61, // Patchy light rain
            '296' => 61, // Light rain
            '299' => 63, // Moderate rain
            '302' => 63, // Moderate rain
            '305' => 65, // Heavy rain
            '308' => 65, // Heavy rain
            '311' => 66, // Light freezing rain
            '314' => 67, // Heavy freezing rain
            '317' => 66, // Light sleet
            '320' => 67, // Moderate/heavy sleet
            '323' => 71, // Patchy light snow
            '326' => 71, // Light snow
            '329' => 73, // Patchy moderate snow
            '332' => 73, // Moderate snow
            '335' => 75, // Heavy snow
            '338' => 75, // Heavy snow
            '350' => 77, // Ice pellets
            '353' => 80, // Light rain shower
            '356' => 82, // Heavy rain shower
            '359' => 82, // Torrential rain
            '362' => 85, // Light sleet showers
            '365' => 86, // Heavy sleet showers
            '368' => 85, // Light snow showers
            '371' => 86, // Heavy snow showers
            '374' => 77, // Light ice pellets
            '377' => 77, // Heavy ice pellets
            '386' => 95, // Thundery rain
            '389' => 99, // Heavy thundery rain
            '392' => 95, // Thundery snow
            '395' => 99, // Heavy thundery snow
        ];
        
        return $map[$wttrCode] ?? 0;
    }
}
