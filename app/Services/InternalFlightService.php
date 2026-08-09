<?php

namespace App\Services;

use App\Models\Let;
use Illuminate\Support\Collection;

class InternalFlightService
{
    public function searchFlights(string $originCity, string $destinationCity, string $date): Collection
    {
        return Let::whereDate('vreme_poletanja', $date)
            ->where('polaziste', 'LIKE', "%{$originCity}%")
            ->where('odrediste', 'LIKE', "%{$destinationCity}%")
            ->orderBy('cena')
            ->get();
    }

    public function cheapestInMonth(string $originCity, string $destinationCity, string $yearMonth): Collection
    {
        [$year, $month] = explode('-', $yearMonth);

        return Let::whereYear('vreme_poletanja', $year)
            ->whereMonth('vreme_poletanja', $month)
            ->where('polaziste', 'LIKE', "%{$originCity}%")
            ->where('odrediste', 'LIKE', "%{$destinationCity}%")
            ->orderBy('cena')
            ->get();
    }
}