<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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
            try {
                $response = Http::withHeaders($this->apiHeaders())
                             ->timeout(10)
                             ->connectTimeout(5)
                             ->withOptions(['force_ip_resolve' => 'v4'])
                             ->get('https://sky-scrapper.p.rapidapi.com/api/v1/flights/searchAirport', [
                        'query' => $iataCode,
                        'locale' => 'en-US',
                    ]);
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::warning('SKY-SCRAPPER resolveLocation TIMEOUT/CONNECTION ERROR', [
                    'query' => $iataCode,
                    'error' => $e->getMessage(),
                ]);
                return null;
            }

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json('data', []);

            if (empty($data)) {
                Log::warning('SKY-SCRAPPER resolveLocation EMPTY RESULT', [
                    'query' => $iataCode,
                    'body' => $response->body(),
                ]);
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
            Log::warning('SKY-SCRAPPER searchFlights: nije moguće razrešiti aerodrom', [
                'origin' => $originSkyId,
                'destination' => $destinationSkyId,
                'origin_resolved' => $origin,
                'destination_resolved' => $destination,
            ]);
            return ['error' => 'Nije moguće pronaći aerodrom za dati kod.', 'data' => ['itineraries' => []]];
        }

        $cacheKey = 'sky_search_' . $origin['skyId'] . '_' . $destination['skyId'] . '_' . $date;

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($origin, $destination, $date) {
            try {
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
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::warning('SKY-SCRAPPER searchFlights TIMEOUT/CONNECTION ERROR', [
                    'error' => $e->getMessage(),
                ]);
                return ['error' => 'Neuspešno dohvatanje letova sa eksternog servisa.', 'data' => ['itineraries' => []]];
            }

            if ($response->successful()) {
                Log::info('SKY-SCRAPPER RAW RESPONSE', $response->json() ?? []);
                return $response->json();
            }

            Log::warning('SKY-SCRAPPER searchFlights FAILED', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['error' => 'Neuspešno dohvatanje letova sa eksternog servisa.', 'data' => ['itineraries' => []]];
        });
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
            try {
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
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::warning('SKY-SCRAPPER getPriceCalendar TIMEOUT/CONNECTION ERROR', [
                    'error' => $e->getMessage(),
                ]);
                return ['error' => 'Neuspešno dohvatanje kalendara cena.', 'days' => []];
            }

            if (!$response->successful()) {
                Log::warning('SKY-SCRAPPER getPriceCalendar FAILED', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return ['error' => 'Neuspešno dohvatanje kalendara cena.', 'days' => []];
            }

            $days = $response->json('data.flights.days', []);

            usort($days, fn($a, $b) => ($a['price'] ?? PHP_INT_MAX) <=> ($b['price'] ?? PHP_INT_MAX));

            return ['days' => $days];
        });
    }
}