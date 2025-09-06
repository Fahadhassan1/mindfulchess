<?php
// Debug page for session assignment
use Illuminate\Support\Facades\Route;

Route::get('/sessions/debug-session/{session}', function($sessionId) {
    if (!app()->environment('local')) {
        abort(404);
    }
    
    $session = \App\Models\ChessSession::find($sessionId);
    $pendingAssignment = session('pending_session_assignment');
    
    return response()->json([
        'session' => $session ? $session->toArray() : null,
        'pending_assignment' => $pendingAssignment,
        'session_id' => session()->getId(),
        'current_timestamp' => now()->timestamp,
        'session_driver' => config('session.driver'),
        'cookie_status' => isset($_COOKIE[config('session.cookie')]) ? 'present' : 'missing',
        'cookie_name' => config('session.cookie')
    ]);
})->name('sessions.debug');
