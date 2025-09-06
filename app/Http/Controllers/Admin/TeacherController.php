<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Storage;

class TeacherController extends Controller
{
    /**
     * Display a listing of the teachers with pagination and filters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        // Start with a query for teachers
        $query = User::role('teacher')->with('teacherProfile');
        
        // Apply search filter if provided
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('email', 'like', $searchTerm)
                  ->orWhereHas('teacherProfile', function($subQuery) use ($searchTerm) {
                      $subQuery->where('qualification', 'like', $searchTerm)
                               ->orWhere('teaching_type', 'like', $searchTerm);
                  });
            });
        }
        
        // Filter by activity status if provided
        if ($request->has('status') && !empty($request->status)) {
            if ($request->status === 'active') {
                $query->whereHas('teacherProfile', function($q) {
                    $q->where('is_active', true);
                });
            } elseif ($request->status === 'inactive') {
                $query->whereHas('teacherProfile', function($q) {
                    $q->where('is_active', false);
                });
            } elseif ($request->status === 'no_profile') {
                $query->doesntHave('teacherProfile');
            }
        }
        
        // Get paginated results
        $teachers = $query->paginate(10);
        
        // Load session counts for each teacher
        $teachers->each(function ($teacher) {
            $teacher->sessions_count = \App\Models\ChessSession::where('teacher_id', $teacher->id)->count();
        });
        
        return view('admin.teachers.index', compact('teachers'));
    }

    /**
     * Show the form for editing the specified teacher's profile.
     *
     * @param  \App\Models\User  $teacher
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(User $teacher)
    {
        if (!$teacher->isTeacher()) {
            return redirect()->route('admin.teachers.index')->with('error', 'User is not a teacher.');
        }

        // Load or create teacher profile
        $profile = $teacher->teacherProfile ?? new TeacherProfile();
        
        return view('admin.teachers.edit', compact('teacher', 'profile'));
    }

    /**
     * Update the specified teacher profile in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $teacher
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $teacher)
    {
        if (!$teacher->isTeacher()) {
            return redirect()->route('admin.teachers.index')->with('error', 'User is not a teacher.');
        }

        // Validate the request data
        $request->validate([
            'teaching_type' => 'nullable|in:adult,kids',
            'stripe_account_id' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'experience_years' => 'nullable|integer|min:0',
            'specialties' => 'nullable',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        // Handle file upload for profile image
        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            if ($teacher->teacherProfile && $teacher->teacherProfile->profile_image) {
                Storage::delete('public/profile_images/' . $teacher->teacherProfile->profile_image);
            }
            
            // Store new image
            $imageName = time() . '_' . $request->file('profile_image')->getClientOriginalName();
            $request->file('profile_image')->storeAs('public/profile_images', $imageName);
        }

        // Process specialties (converting from comma-separated string to array)
        $specialties = null;
        if ($request->has('specialties') && !empty($request->specialties)) {
            $specialties = array_map('trim', explode(',', $request->specialties));
        }

        // Update or create teacher profile
        $profileData = $request->only([
            'teaching_type',
            'stripe_account_id',
            'bio',
            'experience_years',
        ]);
        
        // Handle is_active checkbox
        $profileData['is_active'] = $request->has('is_active');
        
        // Add specialties to profile data
        if ($specialties !== null) {
            $profileData['specialties'] = $specialties;
        }

        if (isset($imageName)) {
            $profileData['profile_image'] = $imageName;
        }

        $teacher->teacherProfile()->updateOrCreate(
            ['user_id' => $teacher->id],
            $profileData
        );

        return redirect()->route('admin.teachers.index')->with('success', 'Teacher profile updated successfully.');
    }

    /**
     * View details of a specific teacher.
     *
     * @param  \App\Models\User  $teacher
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(User $teacher)
    {
        if (!$teacher->isTeacher()) {
            return redirect()->route('admin.teachers.index')->with('error', 'User is not a teacher.');
        }

        // Load teacher with availability data
        $teacher->load('availability');
        
        // Group availability by day of week
        $groupedAvailability = collect(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])
            ->mapWithKeys(function ($day) use ($teacher) {
                return [
                    $day => $teacher->availability->where('day_of_week', $day)->values()
                ];
            });

        return view('admin.teachers.show', compact('teacher', 'groupedAvailability'));
    }
    
    /**
     * Toggle the active status of a teacher.
     *
     * @param  \App\Models\User  $teacher
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleActive(User $teacher)
    {
        if (!$teacher->isTeacher()) {
            return redirect()->route('admin.teachers.index')->with('error', 'User is not a teacher.');
        }

        // Get the current profile or create a new one
        $profile = $teacher->teacherProfile ?? $teacher->teacherProfile()->create();
        
        // Toggle the active status
        $profile->update([
            'is_active' => !$profile->is_active
        ]);
        
        $status = $profile->is_active ? 'activated' : 'deactivated';
        
        return redirect()->back()->with('success', "Teacher {$teacher->name} has been {$status} successfully.");
    }
    
    /**
     * Export teachers data as CSV.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request)
    {
        // Start with a query for teachers
        $query = User::role('teacher')->with('teacherProfile');
        
        // Apply search filter if provided
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('email', 'like', $searchTerm)
                  ->orWhereHas('teacherProfile', function($subQuery) use ($searchTerm) {
                      $subQuery->where('qualification', 'like', $searchTerm)
                               ->orWhere('teaching_type', 'like', $searchTerm);
                  });
            });
        }
        
        // Filter by activity status if provided
        if ($request->has('status') && !empty($request->status)) {
            if ($request->status === 'active') {
                $query->whereHas('teacherProfile', function($q) {
                    $q->where('is_active', true);
                });
            } elseif ($request->status === 'inactive') {
                $query->whereHas('teacherProfile', function($q) {
                    $q->where('is_active', false);
                });
            } elseif ($request->status === 'no_profile') {
                $query->doesntHave('teacherProfile');
            }
        }
        
        $teachers = $query->get();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="teachers.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];
        
        $columns = ['Name', 'Email', 'Teaching Type', 'Experience (Years)', 'Status'];
        
        $callback = function() use ($teachers, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($teachers as $teacher) {
                $row = [
                    $teacher->name,
                    $teacher->email,
                    $teacher->teacherProfile->teaching_type ?? 'Not specified',
                    $teacher->teacherProfile->experience_years ?? 'Not specified',
                    (!$teacher->teacherProfile ? 'No Profile' : ($teacher->teacherProfile->is_active ? 'Active' : 'Inactive'))
                ];
                
                fputcsv($file, $row);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Display statistics for teachers showing recurring students and session counts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function statistics(Request $request)
    {
        // Get all teachers with their profiles
        $teachers = User::role('teacher')
            ->with('teacherProfile')
            ->get();
            
        $teacherStats = [];
        
        foreach ($teachers as $teacher) {
            // Get all sessions for this teacher
            $sessions = \App\Models\ChessSession::where('teacher_id', $teacher->id)->get();
            
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
                
            // Get top 5 students by session count
            $topStudents = [];
            if ($totalStudentCount > 0) {
                $studentCounts = $studentSessions->map(function ($sessions) {
                    return [
                        'student_id' => $sessions->first()->student_id,
                        'count' => $sessions->count()
                    ];
                })->sortByDesc('count')->take(5);
                
                foreach ($studentCounts as $stat) {
                    $student = \App\Models\User::find($stat['student_id']);
                    if ($student) {
                        $topStudents[] = [
                            'name' => $student->name,
                            'sessions' => $stat['count']
                        ];
                    }
                }
            }
            
            // Calculate monthly session counts for the past 6 months
            $monthlySessions = [];
            for ($i = 0; $i < 6; $i++) {
                $month = now()->subMonths($i);
                $monthName = $month->format('M Y');
                $startOfMonth = $month->startOfMonth();
                $endOfMonth = $month->endOfMonth();
                
                $count = $sessions->filter(function ($session) use ($startOfMonth, $endOfMonth) {
                    return $session->created_at >= $startOfMonth && $session->created_at <= $endOfMonth;
                })->count();
                
                $monthlySessions[$monthName] = $count;
            }
            
            // Reverse the array to show oldest month first
            $monthlySessions = array_reverse($monthlySessions, true);
            
            // Store all stats for this teacher
            $teacherStats[$teacher->id] = [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'email' => $teacher->email,
                'is_active' => $teacher->teacherProfile ? $teacher->teacherProfile->is_active : false,
                'total_sessions' => $totalSessions,
                'total_students' => $totalStudentCount,
                'recurring_students' => $recurringStudentCount,
                'recurring_percentage' => $recurringPercentage,
                'top_students' => $topStudents,
                'monthly_sessions' => $monthlySessions
            ];
        }
        
        return view('admin.teachers.statistics', [
            'teacherStats' => $teacherStats
        ]);
    }
    
    /**
     * Display statistics for a specific teacher.
     *
     * @param  \App\Models\User  $teacher
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function teacherStatistics(User $teacher)
    {
        if (!$teacher->isTeacher()) {
            return redirect()->route('admin.teachers.index')->with('error', 'User is not a teacher.');
        }

        // Get all sessions for this teacher
        $sessions = \App\Models\ChessSession::where('teacher_id', $teacher->id)->get();
        
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
            
        // Get detailed information about each student
        $studentDetails = [];
        foreach ($studentSessions as $studentId => $sessions) {
            $student = \App\Models\User::find($studentId);
            if ($student) {
                $studentDetails[] = [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'sessions_count' => $sessions->count(),
                    'first_session' => $sessions->sortBy('created_at')->first()->created_at->format('M d, Y'),
                    'last_session' => $sessions->sortByDesc('created_at')->first()->created_at->format('M d, Y'),
                    'is_recurring' => $sessions->count() > 1
                ];
            }
        }
        
        // Sort students by session count (highest first)
        usort($studentDetails, function($a, $b) {
            return $b['sessions_count'] - $a['sessions_count'];
        });
        
        // Calculate monthly session counts for the past 6 months
        $monthlySessions = [];
        $monthlyLabels = [];
        $monthlyData = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthName = $month->format('M Y');
            $monthlyLabels[] = $monthName;
            
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();
            
            $count = $sessions->filter(function ($session) use ($startOfMonth, $endOfMonth) {
                return $session->created_at >= $startOfMonth && $session->created_at <= $endOfMonth;
            })->count();
            
            $monthlyData[] = $count;
            $monthlySessions[$monthName] = $count;
        }
        
        return view('admin.teachers.teacher_statistics', [
            'teacher' => $teacher,
            'totalSessions' => $totalSessions,
            'totalStudents' => $totalStudentCount,
            'recurringStudents' => $recurringStudentCount,
            'recurringPercentage' => $recurringPercentage,
            'studentDetails' => $studentDetails,
            'monthlySessions' => $monthlySessions,
            'monthlyLabels' => json_encode($monthlyLabels),
            'monthlyData' => json_encode($monthlyData)
        ]);
    }
}
