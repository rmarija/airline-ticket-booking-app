<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FlightService;
use App\Services\InternalFlightService;
use Illuminate\Support\Facades\Http;

class AiChatController extends Controller
{
    protected $flightService;
    protected $internalFlightService;

    public function __construct(FlightService $flightService, InternalFlightService $internalFlightService)
    {
        $this->flightService = $flightService;
        $this->internalFlightService = $internalFlightService;
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'history' => 'nullable|array',
        ]);

        $userMessage = $request->input('message');
        $history = $request->input('history', []);
        $openaiKey = env('OPENAI_API_KEY');

        $messages = [
            [
               'role' => 'system',
               'content' => 'Ti si iskusan i ljubazan AI agent za rezervaciju avio karata u modernoj turističkoj agenciji "Veloro AvioKarte". Trenutni datum je ' . date('Y-m-d') . '.

                PRAVILA RAZGOVORA:
                1. Strogo prati kontekst! Ako je korisnik ranije pomenuo grad ili datum, zapamti ga i ne menjaj ga osim ako korisnik izričito ne traži izmenu.
                2. Korisnik ti daje imena gradova (npr. "Beograd", "Istanbul") — koristi ih tačno tako, bez prevođenja u bilo kakve kodove. Nikad ne pominji IATA kodove korisniku.
                3. Imaš DVE funkcije za pretragu — biraj pravu prema tome koliko je korisnik precizan:
                   - Ako korisnik ima TAČAN datum -> pozovi searchFlights.
                   - Ako korisnik NEMA tačan datum nego traži najjeftiniju opciju u širem periodu (npr. "u avgustu") -> pozovi searchCheapestInMonth.
                4. Kada dobiješ rezultat od searchCheapestInMonth, izdvoji 3-5 najjeftinijih dana i predstavi ih korisniku, pa ga pitaj da li želi konkretne letove za neki od tih dana.
                5. Kada dobiješ rezultate od searchFlights, ispiši ih pregledno — cena, kompanija/broj leta, vreme polaska, vreme sletanja (samo dostupna polja, ne izmišljaj vrednosti).
                6. Ako funkcija ne vrati letove, jasno to reci i predloži drugi datum ili mesec. NIKADA ne izmišljaj letove ili cene.
                7. Korisnik te može pitati i opšta pitanja o avio-putovanjima. Odgovori iz opšteg znanja, uz napomenu da su to opšte informacije.
                8. Ne ubacuj sam link ka stranici za rezervaciju — to sistem radi automatski nakon tvog odgovora.
                9. Budi koncizan, topao i profesionalan. Piši na srpskom jeziku.'
            ]
        ];

        foreach ($history as $msg) {
            $messages[] = [
                'role' => $msg['sender'] === 'user' ? 'user' : 'assistant',
                'content' => $msg['text']
            ];
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage];

        $response = Http::withToken($openaiKey)->retry(2, 1000, throw: false)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
            'tools' => [
                [
                    'type' => 'function',
                    'function' => [
                        'name' => 'searchFlights',
                        'description' => 'Pretraži konkretne letove za JEDAN tačan datum.',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [
                                'originCity' => ['type' => 'string', 'description' => 'Grad polaska, tačno kako ga je korisnik naveo.'],
                                'destinationCity' => ['type' => 'string', 'description' => 'Grad destinacije, tačno kako ga je korisnik naveo.'],
                                'date' => ['type' => 'string', 'description' => 'Tačan datum leta u formatu YYYY-MM-DD.']
                            ],
                            'required' => ['originCity', 'destinationCity', 'date']
                        ]
                    ]
                ],
                [
                    'type' => 'function',
                    'function' => [
                        'name' => 'searchCheapestInMonth',
                        'description' => 'Vrati najjeftinije letove za CEO mesec. Koristi kad korisnik nema tačan datum.',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [
                                'originCity' => ['type' => 'string', 'description' => 'Grad polaska.'],
                                'destinationCity' => ['type' => 'string', 'description' => 'Grad destinacije.'],
                                'yearMonth' => ['type' => 'string', 'description' => 'Mesec u formatu YYYY-MM.']
                            ],
                            'required' => ['originCity', 'destinationCity', 'yearMonth']
                        ]
                    ]
                ]
            ],
            'tool_choice' => 'auto',
        ]);

        if (!$response->successful()) {
            return response()->json(['error' => 'Greška u komunikaciji sa AI servisom.'], 500);
        }

        $result = $response->json();
        $messageChoice = $result['choices'][0]['message'] ?? null;

        if (isset($messageChoice['tool_calls'])) {
            $toolCall = $messageChoice['tool_calls'][0];
            $functionName = $toolCall['function']['name'];
            $arguments = json_decode($toolCall['function']['arguments'], true);

            if (!isset($messageChoice['content']) || $messageChoice['content'] === null) {
                $messageChoice['content'] = "";
            }
            $messages[] = $messageChoice;

            if ($functionName === 'searchFlights') {
                return $this->handleSearchFlights($arguments, $toolCall, $messages, $openaiKey);
            }

            if ($functionName === 'searchCheapestInMonth') {
                return $this->handleSearchCheapestInMonth($arguments, $toolCall, $messages, $openaiKey);
            }
        }

        return response()->json([
            'reply' => $messageChoice['content'] ?? 'Izvinite, nisam najbolje razumeo vaš zahtev.',
            'flights' => null
        ]);
    }

    private function handleSearchFlights(array $arguments, array $toolCall, array $messages, string $openaiKey)
    {
        $originCity = trim($arguments['originCity'] ?? '');
        $destCity = trim($arguments['destinationCity'] ?? '');
        $date = $arguments['date'] ?? '';

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !strtotime($date)) {
            return response()->json([
                'reply' => 'Za pretragu mi je potreban tačan datum (npr. 15.08.2026). Za koji dan tačno da proverim letove?',
                'flights' => null
            ]);
        }

        $internalFlights = $this->internalFlightService->searchFlights($originCity, $destCity, $date);

        if ($internalFlights->isNotEmpty()) {
            $simplified = $internalFlights->map(function ($let) {
                return [
                    'broj_leta' => $let->broj_leta,
                    'cena' => $let->cena . ' €',
                    'vreme_polaska' => $let->vreme_poletanja,
                    'vreme_sletanja' => $let->vreme_sletanja,
                ];
            })->values()->toArray();

            $najjeftinijiId = $internalFlights->first()->id;
            $bookingUrl = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/') . '/letovi/' . $najjeftinijiId;

            $messages[] = [
                'role' => 'tool',
                'tool_call_id' => $toolCall['id'],
                'name' => 'searchFlights',
                'content' => json_encode(['pronadjeni_letovi' => $simplified]),
            ];

            return $this->finalizeResponse($messages, $openaiKey, $bookingUrl, '✈️ Pogledaj i rezerviši karte', ['izvor' => 'sopstvena_baza', 'letovi' => $simplified]);
        }

        $flightData = $this->flightService->searchFlights($originCity, $destCity, $date);

        $simplifiedFlights = [];
        $hasResults = isset($flightData['data']['itineraries']) && count($flightData['data']['itineraries']) > 0;

        if ($hasResults) {
            $itineraries = array_slice($flightData['data']['itineraries'], 0, 3);
            foreach ($itineraries as $itinerary) {
                $leg = $itinerary['legs'][0] ?? [];
                $simplifiedFlights[] = [
                    'cena' => $itinerary['price']['formatted'] ?? 'N/A',
                    'kompanija' => $leg['carriers']['marketing'][0]['name'] ?? 'N/A',
                    'vreme_polaska' => $leg['departure'] ?? 'N/A',
                    'vreme_sletanja' => $leg['arrival'] ?? 'N/A',
                ];
            }
        }
        $bookingUrl = "https://www.skyscanner.net/";

        $messages[] = [
            'role' => 'tool',
            'tool_call_id' => $toolCall['id'],
            'name' => 'searchFlights',
            'content' => json_encode([
                'pronadjeni_letovi' => $hasResults ? $simplifiedFlights : 'Nema letova za taj datum.',
            ])
        ];

        $linkText = $hasResults ? '✈️ Pogledaj i rezerviši karte' : '📅 Proveri druge datume na Skyscanner-u';

        return $this->finalizeResponse($messages, $openaiKey, $bookingUrl, $linkText, $flightData);
    }

    private function handleSearchCheapestInMonth(array $arguments, array $toolCall, array $messages, string $openaiKey)
    {
        $originCity = trim($arguments['originCity'] ?? '');
        $destCity = trim($arguments['destinationCity'] ?? '');
        $yearMonth = $arguments['yearMonth'] ?? '';

        if (!preg_match('/^\d{4}-\d{2}$/', $yearMonth)) {
            return response()->json([
                'reply' => 'Za koji mesec tačno da proverim cene? (npr. avgust 2026)',
                'flights' => null
            ]);
        }

        $internalFlights = $this->internalFlightService->cheapestInMonth($originCity, $destCity, $yearMonth);

        if ($internalFlights->isNotEmpty()) {
            $simplified = $internalFlights->take(5)->map(function ($let) {
                return [
                    'datum' => $let->vreme_poletanja,
                    'cena' => $let->cena . ' €',
                    'broj_leta' => $let->broj_leta,
                ];
            })->values()->toArray();

            $bookingUrl = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/') . '/?polazna=' . urlencode($originCity) . '&odrediste=' . urlencode($destCity);

            $messages[] = [
                'role' => 'tool',
                'tool_call_id' => $toolCall['id'],
                'name' => 'searchCheapestInMonth',
                'content' => json_encode(['najjeftiniji_dani' => $simplified]),
            ];

            return $this->finalizeResponse($messages, $openaiKey, $bookingUrl, '🔍 Pretraži sve letove za ovu rutu', ['izvor' => 'sopstvena_baza', 'dani' => $simplified]);
        }

        $calendarData = $this->flightService->getPriceCalendar($originCity, $destCity, $yearMonth);
        $days = $calendarData['days'] ?? [];
        $cheapestDays = array_slice($days, 0, 5);

        $bookingUrl = "https://www.skyscanner.net/";


        $messages[] = [
            'role' => 'tool',
            'tool_call_id' => $toolCall['id'],
            'name' => 'searchCheapestInMonth',
            'content' => json_encode([
                'najjeftiniji_dani' => empty($cheapestDays) ? 'Nema podataka o cenama za taj mesec.' : $cheapestDays,
            ])
        ];

        return $this->finalizeResponse($messages, $openaiKey, $bookingUrl, '📅 Pogledaj ceo kalendar cena na Skyscanner-u', $calendarData);
    }

    private function finalizeResponse(array $messages, string $openaiKey, string $bookingUrl, string $linkText, $rawData)
    {
        $finalResponse = Http::withToken($openaiKey)->retry(2, 1000, throw: false)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
        ]);

        if (!$finalResponse->successful()) {
            return response()->json(['reply' => 'Greška pri obradi finalnog odgovora.', 'flights' => null]);
        }

        $finalResult = $finalResponse->json() ?? [];
        $finalMessage = $finalResult['choices'][0]['message']['content'] ?? 'Evo rezultata pretrage.';

        $finalMessage .= '<br><br><a href="' . $bookingUrl . '" target="_blank" style="background-color:#0056b3;color:white;padding:6px 12px;border-radius:4px;text-decoration:none;font-weight:bold;display:inline-block;">'
         . $linkText . '</a>';

        return response()->json([
            'reply' => $finalMessage,
            'flights' => $rawData
        ]);
    }
}