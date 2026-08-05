<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FlightService
{
    public function searchFlights($originSkyId, $destinationSkyId, $date)
    {
        $response = Http::withHeaders([
            'x-rapidapi-host' => env('RAPIDAPI_FLIGHT_HOST', 'sky-scrapper.p.rapidapi.com'),
            'x-rapidapi-key' => env('RAPIDAPI_KEY'),
        ])->get('https://sky-scrapper.p.rapidapi.com/api/v2/flights/searchFlights', [
            'originSkyId' => $originSkyId,
            'destinationSkyId' => $destinationSkyId,
            'date' => $date,
            'cabinClass' => 'economy',
            'adults' => 1,
            'currency' => 'EUR',
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return ['error' => 'Neuspešno dohvatanje letova sa eksternog servisa.'];
    }
}