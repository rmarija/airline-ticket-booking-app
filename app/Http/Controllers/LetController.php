<?php

namespace App\Http\Controllers;

use App\Models\Let;
use Illuminate\Http\Request;

class LetController extends Controller
{
  public function index(Request $request)
{
    $query = Let::query();

    if ($request->filled('polaziste')) {
        $query->where('polaziste', 'like', '%' . $request->polaziste . '%');
    }
    if ($request->filled('odrediste')) {
        $query->where('odrediste', 'like', '%' . $request->odrediste . '%');
    }
    if ($request->filled('cena_max')) {
        $query->where('cena', '<=', $request->cena_max);
    }

    
    $sortBy = $request->get('sort_by', 'created_at');
    $sortDir = $request->get('sort_dir', 'desc');
    $query->orderBy($sortBy, $sortDir);

    
    $perPage = $request->get('per_page', 100);
    $letovi = $query->paginate($perPage);

    return response()->json([
        'data' => $letovi->items(),
        'meta' => [
            'current_page' => $letovi->currentPage(),
            'per_page' => $letovi->perPage(),
            'total' => $letovi->total(),
            'last_page' => $letovi->lastPage(),
            'sort_by' => $sortBy,
            'sort_dir' => $sortDir,
        ],
        'links' => [
            'first' => $letovi->url(1),
            'last' => $letovi->url($letovi->lastPage()),
            'prev' => $letovi->previousPageUrl(),
            'next' => $letovi->nextPageUrl(),
        ],
    ]);
}

    public function store(Request $request)
{
       \Log::info('Store method called', [
        'user_id' => $request->user()->id ?? null,
        'user_role' => $request->user()->role ?? null,
    ]);

    if ($request->user()->role !== 'admin') {
        return response()->json(['error' => 'Forbidden'], 403);
    }

    $validated = $request->validate([
        'broj_leta' => 'required|string|unique:lets,broj_leta',
        'polaziste' => 'required|string',
        'odrediste' => 'required|string',
        'vreme_poletanja' => 'required|date',
        'vreme_sletanja' => 'required|date|after:vreme_poletanja',
        'broj_mesta' => 'required|integer|min:1',
        'cena' => 'required|numeric|min:0',
    ]);

    $let = Let::create($validated);

    return response()->json($let, 201);
}


public function gradovi()
{
    $sviGradovi = \App\Models\Let::pluck('polaziste')
        ->merge(\App\Models\Let::pluck('odrediste'))
        ->unique()
        ->values();

    return response()->json(['data' => $sviGradovi]);
}

   public function show($id)
{
    $let = Let::find($id);

    if (!$let) {
        return response()->json(['error' => 'Let nije pronađen.'], 404);
    }

    return response()->json($let);
}


    public function update(Request $request, $id)
{
    $let = Let::find($id);

    if (!$let) {
        return response()->json(['error' => 'Let nije pronađen'], 404);
    }

    if ($request->user()->role !== 'admin') {
        return response()->json(['error' => 'Forbidden'], 403);
    }

    $validated = $request->validate([
        'broj_leta' => 'sometimes|string|unique:lets,broj_leta,' . $id,
        'polaziste' => 'sometimes|string',
        'odrediste' => 'sometimes|string',
        'vreme_poletanja' => 'sometimes|date',
        'vreme_sletanja' => 'sometimes|date|after:vreme_poletanja',
        'broj_mesta' => 'sometimes|integer|min:1',
        'cena' => 'sometimes|numeric|min:0',
    ]);

    $let->update($validated);

    return response()->json($let);
}


   public function destroy(Request $request, $id)
{
    $let = Let::find($id);

    if (!$let) {
        return response()->json(['error' => 'Let nije pronađen'], 404);
    }

    if ($request->user()->role !== 'admin') {
        return response()->json(['error' => 'Forbidden'], 403);
    }

    $let->delete();

    return response()->json(['message' => 'Let obrisan']);
}

public function slobodnaSedista(Request $request)
{
    $validated = $request->validate([
        'let_id' => 'required|exists:lets,id',
    ]);

    $letId = (int)$validated['let_id'];
    $let = Let::findOrFail($letId);
    $ukupnoMesta = $let->broj_mesta;

    $now = now();

    $zauzeta = \App\Models\Rezervacija::where('let_id', $letId)
        ->pluck('broj_sedista')
        ->toArray();

    $zakljucana = \App\Models\LockedSeat::where('let_id', $letId)
        ->where('locked_until', '>', $now)
        ->pluck('broj_sedista')
        ->toArray();

    $svaMesta = range(1, $ukupnoMesta);

    $slobodna = array_diff($svaMesta, $zauzeta, $zakljucana);

    return response()->json([
        'let_id' => $letId,
        'slobodna_sedista' => array_values($slobodna),
    ]);
}


}