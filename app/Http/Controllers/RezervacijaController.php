<?php

namespace App\Http\Controllers;

use App\Models\Let;
use Illuminate\Http\Request;
use App\Models\Rezervacija;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Mail\KartaMail;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class RezervacijaController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'ime_putnika' => 'required|string',
                'email' => 'required|email',
                'broj_sedista' => 'required|array|min:1',
                'broj_sedista.*' => 'integer|distinct',
                'let_id' => 'required|exists:lets,id',
            ]);

            $let = Let::findOrFail($validated['let_id']);

            $rezervacije = DB::transaction(function () use ($validated, $let, $request) {
                $rezs = [];
                foreach ($validated['broj_sedista'] as $seat) {
                    $zauzeto = Rezervacija::where('let_id', $validated['let_id'])
                        ->where('broj_sedista', $seat)
                        ->lockForUpdate()
                        ->exists();

                    if ($zauzeto) {
                        throw new \Exception("Sedište $seat je već rezervisano.");
                    }

                    $rez = Rezervacija::create([
                        'ime_putnika' => $validated['ime_putnika'],
                        'email' => $validated['email'],
                        'broj_sedista' => $seat,
                        'let_id' => $validated['let_id'],
                        'user_id' => $request->user()->id,
                        'broj_karata' => 1,
                        'ukupna_cena' => $let->cena, 
                    ]);

                    $rezs[] = $rez;
                }
                return $rezs;
            });

            foreach ($rezervacije as $rezervacija) {
              $pdf = Pdf::loadView('karta', [
    'rezervacija' => $rezervacija,
    'let' => $let
]);

                Mail::to($rezervacija->email)->send(new KartaMail($pdf->output()));
            }

            return response()->json([
                'message' => 'Rezervacije uspešno kreirane',
                'rezervacije' => $rezervacije
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function index(Request $request)
    {
        $allowedSort = ['created_at', 'ime_putnika', 'email', 'broj_sedista'];

        $sortBy  = in_array($request->get('sort_by'), $allowedSort, true)
            ? $request->get('sort_by')
            : 'created_at';

        $sortDir = strtolower($request->get('sort_dir')) === 'asc' ? 'asc' : 'desc';

        $perPage = (int) $request->get('per_page', 10);
        if ($perPage < 1)   $perPage = 10;
        if ($perPage > 100) $perPage = 100;

        $query = Rezervacija::query()->with('let', 'user');

        if ($request->user()->role !== 'admin') {
            $query->where('user_id', $request->user()->id);
        }

        if ($request->filled('let_id')) {
            $query->where('let_id', (int) $request->get('let_id'));
        }

        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->get('email') . '%');
        }

        if ($request->filled('ime_putnika')) {
            $query->where('ime_putnika', 'like', '%' . $request->get('ime') . '%');
        }

        if ($request->filled('broj_sedista')) {
            $query->where('broj_sedista', (int) $request->get('seat'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        $query->orderBy($sortBy, $sortDir);

        $rez = $query->paginate($perPage)->appends($request->query());

        return response()->json([
            'data' => $rez->items(),
            'meta' => [
                'current_page' => $rez->currentPage(),
                'per_page'     => $rez->perPage(),
                'total'        => $rez->total(),
                'last_page'    => $rez->lastPage(),
                'sort_by'      => $sortBy,
                'sort_dir'     => $sortDir,
            ],
            'links' => [
                'first' => $rez->url(1),
                'last'  => $rez->url($rez->lastPage()),
                'prev'  => $rez->previousPageUrl(),
                'next'  => $rez->nextPageUrl(),
            ],
        ]); 
    }

    public function show($id, Request $request)
    {
        $rezervacija = Rezervacija::with('let', 'user')->find($id);

        if (!$rezervacija) {
            return response()->json(['error' => 'Rezervacija nije pronadjena.'], 404);
        }

        if ($request->user()->role !== 'admin' && $rezervacija->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return response()->json($rezervacija);
    }

    public function destroy($id, Request $request)
    {
        $rezervacija = Rezervacija::find($id);

        if (!$rezervacija) {
            return response()->json(['error' => 'Rezervacija nije pronadjena.'], 404);
        }

        if ($request->user()->role !== 'admin' && $rezervacija->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $rezervacija->delete();

        return response()->json(['message' => 'Rezervacija uspešno obrisana.'], 200);
    }

    public function update($id, Request $request)
    {
        try {
            Log::info('--- UPDATE START ---');

            $rezervacija = Rezervacija::find($id);

            if (!$rezervacija) {
                return response()->json(['error' => 'Rezervacija nije pronađena.'], 404);
            }

            if ($request->user()->role !== 'admin' && $rezervacija->user_id !== $request->user()->id) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            $validated = $request->validate([
                'ime_putnika'  => 'sometimes|nullable|string',
                'email'        => 'sometimes|email',
                'broj_sedista' => 'sometimes|integer',
                'broj_karata'  => 'sometimes|integer|min:1',
            ]);

            if (isset($validated['broj_karata'])) {
                $let = Let::findOrFail($rezervacija->let_id);
                $validated['ukupna_cena'] = $let->cena * $validated['broj_karata'];
            }

            if (isset($validated['broj_sedista'])) {
                $let = Let::findOrFail($rezervacija->let_id);

                if ($validated['broj_sedista'] > $let->broj_mesta) {
                    return response()->json([
                        'error' => 'Uneti broj sedišta premašuje kapacitet leta.'
                    ], 400);
                }

                $lock = \App\Models\LockedSeat::where('let_id', $rezervacija->let_id)
                    ->where('broj_sedista', $validated['broj_sedista'])
                    ->where('locked_until', '>', now())
                    ->first();

                if (!$lock) {
                    return response()->json([
                        'error' => 'Sedište nije zaključano ili je lock istekao.'
                    ], 400);
                }

                $zauzeto = Rezervacija::where('let_id', $rezervacija->let_id)
                    ->where('broj_sedista', $validated['broj_sedista'])
                    ->where('id', '!=', $rezervacija->id)
                    ->lockForUpdate()
                    ->exists();

                if ($zauzeto) {
                    return response()->json([
                        'error' => 'Sedište je već rezervisano za ovaj let.'
                    ], 400);
                }

                $lock->delete();
            }

            $rezervacija->update($validated);
            $rezervacija->load('let', 'user');

            Log::info('--- UPDATE END ---');

            return response()->json($rezervacija, 200);

        } catch (\Exception $e) {
            Log::error('Greška u update metodi: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}