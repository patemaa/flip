<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// routes/web.php dosyasının sonuna ekle:

Route::post('/api/pomodoro/create', function (Request $request) {
    \Log::info('Pomodoro create request:', $request->all());

    $session = \App\Models\Pomodoro::create([
        'type' => $request->type,
        'duration_seconds' => $request->duration_seconds,
        'project_name' => $request->project_name,
        'priority' => $request->priority ?? 'medium',
        'status' => 'in_progress',
        'started_at' => now(),
        'expected_end_at' => now()->addSeconds($request->duration_seconds),
    ]);

    return response()->json(['session' => $session]);
});

Route::post('/api/pomodoro/{id}/update', function (Request $request, $id) {
    \Log::info('Pomodoro update request:', ['id' => $id, 'data' => $request->all()]);

    $session = \App\Models\Pomodoro::findOrFail($id);

    $updateData = [
        'status' => $request->status,
    ];

    if ($request->status === 'completed' || $request->status === 'cancelled') {
        $updateData['ended_at'] = now();
    }

    if ($request->has('accumulated_seconds')) {
        $updateData['accumulated_seconds'] = $request->accumulated_seconds;
    }

    $session->update($updateData);

    return response()->json(['success' => true]);
});

require __DIR__.'/auth.php';
