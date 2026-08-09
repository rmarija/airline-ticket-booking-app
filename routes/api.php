<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LetController;
use App\Http\Controllers\RezervacijaController;
use App\Http\Controllers\LockedSeatController;
use Illuminate\Support\Facades\Http; 
use App\Http\Controllers\AiChatController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::get('/letovi', [LetController::class, 'index']);
Route::get('/letovi/{id}', [LetController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    Route::post('/letovi', [LetController::class,'store']);
    Route::put('/letovi/{id}', [LetController::class, 'update']);
    Route::delete('/letovi/{id}', [LetController::class, 'destroy']);

    Route::get('/rezervacije', [RezervacijaController::class, 'index']);
    Route::get('/rezervacije/{id}', [RezervacijaController::class, 'show']);
    Route::post('/rezervacije', [RezervacijaController::class, 'store']);
    Route::put('/rezervacije/{id}', [RezervacijaController::class, 'update']);
    Route::delete('/rezervacije/{id}', [RezervacijaController::class, 'destroy']);

    Route::post('/zakljucaj-sediste', [LockedSeatController::class, 'lock']);
});

Route::get('/admin-only', function () {
    return response()->json(['message' => 'Dobrodošao admin!']);
})->middleware(['auth:sanctum', 'role:admin']);

Route::get('/user-only', function () {
    return response()->json(['message' => 'Dobrodošao korisniče!']);
})->middleware(['auth:sanctum', 'role:user']);

Route::get('/slobodna-sedista', [LetController::class, 'slobodnaSedista']);
Route::delete('/locked-seats/cleanup', [LockedSeatController::class, 'cleanupExpired']);

Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);


Route::post('/chat', [AiChatController::class, 'chat']);
Route::get('/gradovi', [LetController::class, 'gradovi']);

Route::get('/weather/{city}', function ($city) {
    $apiKey = config('services.weather.key');

    if (!$apiKey) {
        return response()->json(['error' => 'API key nije učitan'], 500);
    }

    $response = Http::get("https://api.openweathermap.org/data/2.5/weather", [
        'q'     => $city,
        'appid' => $apiKey,
        'units' => 'metric',
        'lang'  => 'sr'
    ]);

    

    return $response->json();
});
