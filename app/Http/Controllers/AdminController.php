<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ChessSession;
use App\Models\Payment;
use App\Models\Transfer;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Models\TeacherProfile;
use App\Models\StudentProfile;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Show the admin dashboard with statistics.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Get counts for dashboard stats
        $totalUsers = User::count();
        $totalTeachers = User::role('teacher')->count();
        $totalStudents = User::role('student')->count();
        $totalAdmins = User::role('admin')->count();
        
        // Get session statistics
        $totalSessions = ChessSession::count();
        $pendingSessions = ChessSession::where('status', 'pending')->count();
        $completedSessions = ChessSession::where('status', 'completed')->count();
        $todaySessions = ChessSession::whereDate('scheduled_at', today())->count();
        
        // Get payment statistics
        $totalRevenue = Payment::where('status', 'succeeded')->sum('amount');
        $thisMonthRevenue = Payment::where('status', 'succeeded')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');
        $totalPayments = Payment::count();
        $pendingPayments = Payment::where('status', 'pending')->count();
        
        // Get transfer statistics
        $totalTransfers = Transfer::where('status', 'completed')->sum('amount');
        $pendingTransfers = Transfer::where('status', 'pending')->count();
        $totalFeesCollected = Transfer::where('status', 'completed')->sum('application_fee');
        
        // Get the most recent teachers and students
        $recentTeachers = User::role('teacher')->with('teacherProfile')->latest()->take(5)->get();
        $recentStudents = User::role('student')->with('studentProfile')->latest()->take(5)->get();
        
        // Get recent sessions
        $recentSessions = ChessSession::with(['student', 'teacher'])
            ->latest()
            ->take(5)
            ->get();
        
        // Get teaching type distribution
        $teachingTypes = TeacherProfile::selectRaw('teaching_type, COUNT(*) as count')
            ->groupBy('teaching_type')
            ->get()
            ->pluck('count', 'teaching_type')
            ->toArray();
        
        // Get student level distribution
        $studentLevels = StudentProfile::selectRaw('level, COUNT(*) as count')
            ->groupBy('level')
            ->get()
            ->pluck('count', 'level')
            ->toArray();
            
        // Calculate teacher statistics for session milestones
        $teachers = User::role('teacher')->with('teacherProfile')->get();
        $teacherStats = [];
        
        foreach ($teachers as $teacher) {
            // Get all sessions for this teacher
            $sessions = ChessSession::where('teacher_id', $teacher->id)->get();
            
            // Count total sessions
            $totalSessions = $sessions->count();
            
            // Group sessions by student to find recurring students
            $studentSessions = $sessions->groupBy('student_id');
            
            // Count students with more than one session (recurring)
            $recurringStudents = $studentSessions->filter(function ($sessions) {
                return $sessions->count() > 1;
            });
            
            $recurringStudentCount = $recurringStudents->count();
            $totalStudentCount = $studentSessions->count();
            
            // Calculate recurring student percentage
            $recurringPercentage = $totalStudentCount > 0 
                ? round(($recurringStudentCount / $totalStudentCount) * 100, 2) 
                : 0;
                
            // Calculate session milestone counts
            $studentsWithTenPlusSessions = 0;
            $studentsWithTwentyPlusSessions = 0;
            $studentsWithFiftyPlusSessions = 0;
            
            foreach ($studentSessions as $sessions) {
                $sessionCount = $sessions->count();
                if ($sessionCount >= 10) $studentsWithTenPlusSessions++;
                if ($sessionCount >= 20) $studentsWithTwentyPlusSessions++;
                if ($sessionCount >= 50) $studentsWithFiftyPlusSessions++;
            }
            
            $tenPlusSessionsPercentage = $totalStudentCount > 0 
                ? round(($studentsWithTenPlusSessions / $totalStudentCount) * 100, 1) 
                : 0;
                
            // Store all stats for this teacher
            $teacherStats[] = [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'email' => $teacher->email,
                'is_active' => $teacher->teacherProfile ? $teacher->teacherProfile->is_active : false,
                'total_sessions' => $totalSessions,
                'total_students' => $totalStudentCount,
                'recurring_students' => $recurringStudentCount,
                'recurring_percentage' => $recurringPercentage,
                'ten_plus_sessions' => $studentsWithTenPlusSessions,
                'ten_plus_percentage' => $tenPlusSessionsPercentage,
                'twenty_plus_sessions' => $studentsWithTwentyPlusSessions,
                'fifty_plus_sessions' => $studentsWithFiftyPlusSessions,
            ];
        }
        
        return view('admin.dashboard', compact(
            'totalUsers', 'totalTeachers', 'totalStudents', 'totalAdmins',
            'totalSessions', 'pendingSessions', 'completedSessions', 'todaySessions',
            'totalRevenue', 'thisMonthRevenue', 'totalPayments', 'pendingPayments',
            'totalTransfers', 'pendingTransfers', 'totalFeesCollected',
            'recentTeachers', 'recentStudents', 'recentSessions',
            'teachingTypes', 'studentLevels', 'teacherStats'
        ));
    }

    /**
     * Show the user management page with pagination and filters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function manageUsers(Request $request)
    {
        // Start with a query for users
        $query = User::with('roles');
        
        // Apply search filter if provided
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('email', 'like', $searchTerm);
            });
        }
        
        // Filter by role if provided
        if ($request->has('role') && !empty($request->role)) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }
        
        // Get paginated results
        $users = $query->paginate(10);
        
        // Get all roles for filter dropdown
        $roles = Role::all();
        
        return view('admin.users', compact('users', 'roles'));
    }
}
