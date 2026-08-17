<?php

namespace App\Http\Controllers;

use App\Models\LockedSeat;
use App\Models\Let;
use App\Models\Rezervacija;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LockedSeatController extends Controller
{
  public function lock(Request $request)
{
    $validated = $request->validate([
        'let_id' => 'required|exists:lets,id',
        'broj_sedista' => 'nullable|integer|min:1', 
        'trajanje_min' => 'nullable|integer|min:5|max:30',
    ]);

    $letId = (int)$validated['let_id'];
    $ttlMin = (int)($validated['trajanje_min'] ?? 5);
    $now = now();
    $until = (clone $now)->addMinutes($ttlMin);

    $let = Let::findOrFail($letId);
    $ukupnoMesta = $let->broj_mesta;

    if (empty($validated['broj_sedista'])) {
        $zauzeta = Rezervacija::where('let_id', $letId)->pluck('broj_sedista')->toArray();
        $zakljucana = LockedSeat::where('let_id', $letId)
            ->where('locked_until', '>', $now)
            ->pluck('broj_sedista')
            ->toArray();

        $svaMesta = range(1, $ukupnoMesta);
        $slobodna = array_diff($svaMesta, $zauzeta, $zakljucana);

        if (empty($slobodna)) {
            return response()->json(['error' => 'Nema slobodnih mesta.'], 409);
        }

        $brojSedista = $slobodna[array_rand($slobodna)];
    } else {
        $brojSedista = (int)$validated['broj_sedista'];
        if ($brojSedista < 1 || $brojSedista > $ukupnoMesta) {
            return response()->json(['error' => 'Nevalidan broj sedišta.'], 422);
        }
    }

    $rezervisanoPostoji = Rezervacija::where('let_id', $letId)
        ->where('broj_sedista', $brojSedista)
        ->exists();
    if ($rezervisanoPostoji) {
        return response()->json(['error' => 'Sedište je već rezervisano.'], 409);
    }

    try {
        return DB::transaction(function () use ($letId, $brojSedista, $now, $until) {

            $postojeci = LockedSeat::where('let_id', $letId)
                ->where('broj_sedista', $brojSedista)
                ->lockForUpdate()
                ->first();

            if ($postojeci) {
                if ($postojeci->locked_until && $postojeci->locked_until->greaterThan($now)) {
                    return response()->json([
                        'error' => 'Sedište je već privremeno zaključano.'
                    ], 409);
                }

                $postojeci->locked_until = $until;
                $postojeci->save();

                return response()->json([
                    'message' => "Sedište $brojSedista je ponovo zaključano na {$now->diffInMinutes($until)} minuta.",
                    'lock' => $postojeci,
                ], 200);
            }

            $lock = LockedSeat::create([
                'let_id' => $letId,
                'broj_sedista' => $brojSedista,
                'locked_until' => $until,
            ]);

            return response()->json([
                'message' => "Sedište $brojSedista je uspešno zaključano na {$now->diffInMinutes($until)} minuta.",
                'lock' => $lock,
            ], 201);
        });
    } catch (\Illuminate\Database\QueryException $e) {
        if ((int)($e->errorInfo[1] ?? 0) === 1062) {
            return response()->json([
                'error' => 'Sedište je već privremeno zaključano.',
            ], 409);
        }
        throw $e;
    }
}
}



