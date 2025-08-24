<?php

namespace App\Http\Controllers;

use App\Models\ChessSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Display a listing of the sessions.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        $user = Auth::user();
        
        // Make sure we have a logged in user
        if (!$user) {
            return redirect()->route('login');
        }
        
        $roles = $user->roles->pluck('name')->toArray();
        
        // Get sessions based on user role
        if (in_array('admin', $roles)) {
            // Admins see all sessions
            $sessions = ChessSession::with(['payment', 'student.user', 'teacher.user'])->latest()->paginate(20);
        } elseif (in_array('teacher', $roles)) {
            // Teachers see sessions assigned to them or ones that they could potentially be assigned to
            $teacherProfile = $user->teacherProfile;
            
            if ($teacherProfile) {
                $teachingType = $teacherProfile->teaching_type;
                
                $sessions = ChessSession::with(['payment', 'student.user'])
                    ->where(function($query) use ($user, $teachingType) {
                        $query->where('teacher_id', $user->id) // Sessions assigned to this teacher
                            ->orWhere(function($q) use ($teachingType) { // OR Sessions without a teacher that match the teacher's teaching type
                                $q->whereNull('teacher_id')
                                  ->where(function($inner) use ($teachingType) {
                                      $inner->where('session_type', $teachingType)
                                            ->orWhere(function($or) use ($teachingType) {
                                                $or->where('session_type', 'all')
                                                   ->orWhere(DB::raw("'$teachingType'"), 'all');
                                            });
                                  });
                            });
                    })
                    ->latest()
                    ->paginate(20);
            } else {
                $sessions = ChessSession::where('teacher_id', $user->id)->latest()->paginate(20);
            }
        } elseif (in_array('student', $roles)) {
            // Students only see their own sessions
            $sessions = ChessSession::with(['payment', 'teacher.user'])
                ->where('student_id', $user->id)
                ->latest()
                ->paginate(20);
        } else {
            // Default case
            $sessions = collect();
        }
        
        return view('sessions.index', compact('sessions'));
    }
}
