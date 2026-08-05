<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FlightService;
use Illuminate\Support\Facades\Http;

class AiChatController extends Controller
{
    protected $flightService;

    public function __construct(FlightService $flightService)
    {
        $this->flightService = $flightService;
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $userMessage = $request->input('message');
        $openaiKey = env('OPENAI_API_KEY');

        $response = Http::withToken($openaiKey)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                   'role' => 'system',
                    'content' => 'Ti si pametni AI asistent integrisan u web aplikaciju za rezervaciju avio-karata. Tvoj glavni zadatak je da pretražuješ letove iz eksternih baza u realnom vremenu i pomažeš korisnicima. 
           
                    
                    Pravila ponašanja:
                    1. Kada korisnik zatraži let (npr. "najjeftiniji let za Pariz u narednih 5 dana"), uvek prvo pozovi funkciju "searchFlights" da izvučeš sveže podatke.
                    2. Ako korisnik ne navede tačan datum, pitaj ga za datum pre nego što pozoveš funkciju.
                    3. Kada dobiješ rezultate, formatiraj ih tako da budu jasni i laki za čitanje (cena, avio-kompanija, vreme polaska).
                    4. NAJVAŽNIJE: Za svaki pronađeni let, OBAVEZNO prosledi direktan link (URL) ka tom letu kako bi korisnik mogao da klikne i pređe na eksterni sajt radi finalne kupovine.
                    5. Budi profesionalan, uslužan i koncizan.'
                ],
                [
                    'role' => 'user',
                    'content' => $userMessage
                ]
            ],
            'tools' => [
                [
                    'type' => 'function',
                    'function' => [
                        'name' => 'searchFlights',
                        'description' => 'Pretraži aviokarte preko eksternog API-ja na osnovu aerodroma polaska, dolaska i datuma.',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [
                                'originSkyId' => [
                                    'type' => 'string',
                                    'description' => 'Kod aerodroma polaska, npr. LOND za London ili PARI za Pariz.'
                                ],
                                'destinationSkyId' => [
                                    'type' => 'string',
                                    'description' => 'Kod aerodroma dolaska, npr. NYC za Njujork.'
                                ],
                                'date' => [
                                    'type' => 'string',
                                    'description' => 'Datum leta u formatu YYYY-MM-DD.'
                                ]
                            ],
                            'required' => ['originSkyId', 'destinationSkyId', 'date']
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

            if ($functionName === 'searchFlights') {
                $flightData = $this->flightService->searchFlights(
                    $arguments['originSkyId'] ?? '',
                    $arguments['destinationSkyId'] ?? '',
                    $arguments['date'] ?? ''
                );

                return response()->json([
                    'reply' => 'Evo rezultata pretrage letova koje je naš AI agent pronašao za vas:',
                    'flights' => $flightData
                ]);
            }
        }

        return response()->json([
            'reply' => $messageChoice['content'] ?? 'Izvinite, nisam najbolje razumuo vaš zahtev.',
            'flights' => null
        ]);
    }
}