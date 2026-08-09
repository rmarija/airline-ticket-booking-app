<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class FlightService
{
    protected function apiHeaders()
    {
        return [
            'x-rapidapi-host' => env('RAPIDAPI_FLIGHT_HOST', 'sky-scrapper.p.rapidapi.com'),
            'x-rapidapi-key' => env('RAPIDAPI_KEY'),
        ];
    }

   
    public function resolveLocation($iataCode)
    {
        $cacheKey = 'skyscanner_location_' . strtoupper($iataCode);

        return Cache::remember($cacheKey, now()->addDay(), function () use ($iataCode) {
            $response = Http::withHeaders($this->apiHeaders())
                         ->timeout(10)
                         ->connectTimeout(5)
                         ->withOptions(['force_ip_resolve' => 'v4']) 
                         ->get('https://sky-scrapper.p.rapidapi.com/api/v1/flights/searchAirport', [
                    'query' => $iataCode,
                    'locale' => 'en-US',
                ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json('data', []);

            if (empty($data)) {
                return null;
            }

            $flightParams = $data[0]['navigation']['relevantFlightParams'] ?? [];

            return [
                  'skyId' => $flightParams['skyId'] ?? $iataCode,
                'entityId' => $flightParams['entityId'] ?? null,
            ];
        });
    }

  
    public function searchFlights($originSkyId, $destinationSkyId, $date)
    {
        $origin = $this->resolveLocation($originSkyId);
        $destination = $this->resolveLocation($destinationSkyId);

        if (!$origin || !$origin['entityId'] || !$destination || !$destination['entityId']) {
            return ['error' => 'Nije moguće pronaći aerodrom za dati kod.', 'data' => ['itineraries' => []]];
        }

        $response = Http::withHeaders($this->apiHeaders())
        ->timeout(10)
    ->connectTimeout(5)
    ->withOptions(['force_ip_resolve' => 'v4'])
            ->get('https://sky-scrapper.p.rapidapi.com/api/v2/flights/searchFlights', [
                'originSkyId' => $origin['skyId'],
                'originEntityId' => $origin['entityId'],
                'destinationSkyId' => $destination['skyId'],
                'destinationEntityId' => $destination['entityId'],
                'date' => $date,
                'cabinClass' => 'economy',
                'adults' => 1,
                'currency' => 'EUR',
            ]);

       if ($response->successful()) {
            \Illuminate\Support\Facades\Log::info('RAW SKY-SCRAPPER RESPONSE', $response->json());
            return $response->json();
        }

        return ['error' => 'Neuspešno dohvatanje letova sa eksternog servisa.', 'data' => ['itineraries' => []]];
    }

    public function getPriceCalendar($originSkyId, $destinationSkyId, $yearMonth)
    {
        $origin = $this->resolveLocation($originSkyId);
        $destination = $this->resolveLocation($destinationSkyId);

        if (!$origin || !$origin['entityId'] || !$destination || !$destination['entityId']) {
            return ['error' => 'Nije moguće pronaći aerodrom za dati kod.', 'days' => []];
        }

        $cacheKey = 'price_calendar_' . $origin['skyId'] . '_' . $destination['skyId'] . '_' . $yearMonth;

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($origin, $destination, $yearMonth) {
            $response = Http::withHeaders($this->apiHeaders())
            ->timeout(10)
    ->connectTimeout(5)
    ->withOptions(['force_ip_resolve' => 'v4'])
                ->get('https://sky-scrapper.p.rapidapi.com/api/v1/flights/getPriceCalendar', [
                    'originSkyId' => $origin['skyId'],
                    'originEntityId' => $origin['entityId'],
                    'destinationSkyId' => $destination['skyId'],
                    'destinationEntityId' => $destination['entityId'],
                    'yearMonth' => $yearMonth,
                    'currency' => 'EUR',
                ]);

            if (!$response->successful()) {
                return ['error' => 'Neuspešno dohvatanje kalendara cena.', 'days' => []];
            }

            $days = $response->json('data.flights.days', []);

            usort($days, fn($a, $b) => ($a['price'] ?? PHP_INT_MAX) <=> ($b['price'] ?? PHP_INT_MAX));

            return ['days' => $days];
        });
    }
}