<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChessSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SessionsExport;

class SessionController extends Controller
{
    /**
     * Display a listing of the sessions.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $query = ChessSession::with(['student', 'teacher', 'payment']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->whereHas('student', function($sq) use ($searchTerm) {
                    $sq->where('name', 'like', $searchTerm)
                      ->orWhere('email', 'like', $searchTerm);
                })->orWhereHas('teacher', function($tq) use ($searchTerm) {
                    $tq->where('name', 'like', $searchTerm)
                      ->orWhere('email', 'like', $searchTerm);
                });
            });
        }

        $sessions = $query->orderBy('scheduled_at', 'desc')->paginate(15);

        // Get teachers and students for filter dropdowns
        $teachers = User::role('teacher')->with('teacherProfile')->get();
        $students = User::role('student')->with('studentProfile')->get();

        // Calculate statistics
        $stats = [
            'total_sessions' => ChessSession::count(),
            'pending_sessions' => ChessSession::where('status', 'pending')->count(),
            'confirmed_sessions' => ChessSession::where('status', 'booked')->count(), // Using booked instead of confirmed
            'completed_sessions' => ChessSession::where('status', 'completed')->count(),
            'cancelled_sessions' => ChessSession::where('status', 'cancelled')->count(),
            'total_revenue' => DB::table('payments')
                ->join('chess_sessions', 'chess_sessions.payment_id', '=', 'payments.id')
                ->where('payments.status', 'succeeded')
                ->sum('payments.amount'),
        ];

        return view('admin.sessions.index', compact('sessions', 'teachers', 'students', 'stats'));
    }

    /**
     * Display the specified session.
     *
     * @param  \App\Models\ChessSession  $session
     * @return \Illuminate\Contracts\View\View
     */
    public function show(ChessSession $session)
    {
        $session->load(['student', 'teacher', 'payment', 'homework']);
        
        return view('admin.sessions.show', compact('session'));
    }

    /**
     * Update the session status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ChessSession  $session
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, ChessSession $session)
    {
        $request->validate([
            'status' => 'required|in:pending,booked,completed,cancelled',
            'reason' => 'nullable|string|max:500'
        ]);

        $oldStatus = $session->status;
        
        $session->update([
            'status' => $request->status,
            'admin_notes' => $request->reason ? 
                ($session->admin_notes ? $session->admin_notes . "\n\n" : '') . 
                now()->format('Y-m-d H:i:s') . " - Status changed from {$oldStatus} to {$request->status}: " . $request->reason
                : $session->admin_notes
        ]);

        return redirect()->back()->with('success', 'Session status updated successfully.');
    }

    /**
     * Export sessions to Excel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        $filters = $request->only(['status', 'teacher_id', 'student_id', 'date_from', 'date_to', 'search']);
        
        return Excel::download(new SessionsExport($filters), 'sessions_' . now()->format('Y-m-d') . '.xlsx');
    }
}
