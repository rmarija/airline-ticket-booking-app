<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FlightService

{
    protected array $gradoviMapa = [
        'beograd' => 'Belgrade',
        'beca' => 'Vienna',
        'bec' => 'Vienna',
        'vienna' => 'Vienna',
        'budimpesta' => 'Budapest',
        'solun' => 'Thessaloniki',
        'atina' => 'Athens',
        'rim' => 'Rome',
        'milano' => 'Milan',
        'venecija' => 'Venice',
        'pariz' => 'Paris',
        'london' => 'London',
        'berlin' => 'Berlin',
        'minhen' => 'Munich',
        'cirih' => 'Zurich',
        'zenava' => 'Geneva',
        'zeneva' => 'Geneva',
        'amsterdam' => 'Amsterdam',
        'brisel' => 'Brussels',
        'prag' => 'Prague',
        'varsava' => 'Warsaw',
        'moskva' => 'Moscow',
        'istanbul' => 'Istanbul',
        'carigrad' => 'Istanbul',
        'kopenhagen' => 'Copenhagen',
        'stokholm' => 'Stockholm',
        'lisabon' => 'Lisbon',
        'madrid' => 'Madrid',
        'barselona' => 'Barcelona',
        'nica' => 'Nice',
        'dubai' => 'Dubai',
        'njujork' => 'New York',
        'zagreb' => 'Zagreb',
        'ljubljana' => 'Ljubljana',
        'skoplje' => 'Skopje',
        'sarajevo' => 'Sarajevo',
        'podgorica' => 'Podgorica',
        'tivat' => 'Tivat',
        'nis' => 'Nis',
    ];

    protected function normalizeCity(string $city): string
    {
        $key = mb_strtolower(trim($city), 'UTF-8');

        $key = strtr($key, [
            'č' => 'c', 'ć' => 'c', 'ž' => 'z',
            'š' => 's', 'đ' => 'd', 'dž' => 'z',
        ]);

        $kandidati = [$key, rtrim($key, 'auei')];

        foreach ($kandidati as $k) {
            if (isset($this->gradoviMapa[$k])) {
                return $this->gradoviMapa[$k];
            }
        }

        return $city;
    }

    protected function apiHeaders()
    {
        return [
            'x-rapidapi-host' => env('RAPIDAPI_FLIGHT_HOST', 'sky-scrapper.p.rapidapi.com'),
            'x-rapidapi-key' => env('RAPIDAPI_KEY'),
        ];
    }

        public function resolveLocation($iataCode)
    {
        $upit = $this->normalizeCity((string) $iataCode);
        $cacheKey = 'skyscanner_location_' . strtoupper($upit);

        return Cache::remember($cacheKey, now()->addDay(), function () use ($upit) {
            $response = Http::withHeaders($this->apiHeaders())
                         ->timeout(10)
                         ->connectTimeout(5)
                         ->withOptions(['force_ip_resolve' => 'v4'])
                         ->get('https://sky-scrapper.p.rapidapi.com/api/v1/flights/searchAirport', [
                    'query' => $upit,
                    'locale' => 'en-US',
                ]);

            if (!$response->successful()) {
                \Log::warning('searchAirport nije uspeo', [
                    'grad' => $upit,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $data = $response->json('data', []);

            if (empty($data)) {
                \Log::warning('searchAirport nije nasao grad', ['grad' => $upit]);
                return null;
            }

            $flightParams = $data[0]['navigation']['relevantFlightParams'] ?? [];

            return [
                'skyId' => $flightParams['skyId'] ?? $upit,
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