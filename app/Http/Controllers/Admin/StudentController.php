<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    /**
     * Display a listing of the students with pagination and filters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        // Start with a query for students
        $query = User::role('student')->with(['studentProfile.teacher']);
        
        // Apply search filter if provided
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('email', 'like', $searchTerm)
                  ->orWhereHas('studentProfile', function($subQuery) use ($searchTerm) {
                      $subQuery->where('school', 'like', $searchTerm)
                               ->orWhere('level', 'like', $searchTerm)
                               ->orWhere('parent_name', 'like', $searchTerm);
                  })
                  ->orWhereHas('studentProfile.teacher', function($subQuery) use ($searchTerm) {
                      $subQuery->where('name', 'like', $searchTerm);
                  });
            });
        }
        
        // Filter by level if provided
        if ($request->has('level') && !empty($request->level)) {
            $query->whereHas('studentProfile', function($q) use ($request) {
                $q->where('level', $request->level);
            });
        }
        
        // Filter by teacher if provided
        if ($request->has('teacher') && !empty($request->teacher)) {
            if ($request->teacher === 'unassigned') {
                $query->where(function($q) {
                    $q->whereDoesntHave('studentProfile')
                      ->orWhereHas('studentProfile', function($subQuery) {
                          $subQuery->whereNull('teacher_id');
                      });
                });
            } else {
                $query->whereHas('studentProfile', function($q) use ($request) {
                    $q->where('teacher_id', $request->teacher);
                });
            }
        }
        
        // Get paginated results
        $students = $query->paginate(10);
        
        // Get all possible student levels for filter dropdown
        $levels = ['beginner', 'intermediate', 'advanced'];
        
        // Get all teachers for filter dropdown
        $teachers = User::role('teacher')->get();
        
        return view('admin.students.index', compact('students', 'levels', 'teachers'));
    }

    /**
     * Show the form for editing the specified student's profile.
     *
     * @param  \App\Models\User  $student
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(User $student)
    {
        if (!$student->isStudent()) {
            return redirect()->route('admin.students.index')->with('error', 'User is not a student.');
        }

        // Load or create student profile
        $profile = $student->studentProfile ?? new StudentProfile();
        
        return view('admin.students.edit', compact('student', 'profile'));
    }

    /**
     * Update the specified student profile in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $student
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $student)
    {
        if (!$student->isStudent()) {
            return redirect()->route('admin.students.index')->with('error', 'User is not a student.');
        }

        // Validate the request data
        $request->validate([
            'age' => 'nullable|integer|min:1|max:100',
            'level' => 'nullable|in:beginner,intermediate,advanced',
            'parent_name' => 'nullable|string|max:255',
            'parent_email' => 'nullable|email|max:255',
            'parent_phone' => 'nullable|string|max:20',
            'school' => 'nullable|string|max:255',
            'learning_goals' => 'nullable|string',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle file upload for profile image
        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            if ($student->studentProfile && $student->studentProfile->profile_image) {
                Storage::delete('public/profile_images/' . $student->studentProfile->profile_image);
            }
            
            // Store new image
            $imageName = time() . '_' . $request->file('profile_image')->getClientOriginalName();
            $request->file('profile_image')->storeAs('public/profile_images', $imageName);
        }

        // Update or create student profile
        $profileData = $request->only([
            'age',
            'level',
            'parent_name',
            'parent_email',
            'parent_phone',
            'school',
            'learning_goals',
        ]);

        if (isset($imageName)) {
            $profileData['profile_image'] = $imageName;
        }

        $student->studentProfile()->updateOrCreate(
            ['user_id' => $student->id],
            $profileData
        );

        return redirect()->route('admin.students.index')->with('success', 'Student profile updated successfully.');
    }

    /**
     * View details of a specific student.
     *
     * @param  \App\Models\User  $student
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(User $student)
    {
        if (!$student->isStudent()) {
            return redirect()->route('admin.students.index')->with('error', 'User is not a student.');
        }

        return view('admin.students.show', compact('student'));
    }
    
    /**
     * Export students data as CSV.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request)
    {
        // Start with a query for students
        $query = User::role('student')->with('studentProfile');
        
        // Apply search filter if provided
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('email', 'like', $searchTerm)
                  ->orWhereHas('studentProfile', function($subQuery) use ($searchTerm) {
                      $subQuery->where('school', 'like', $searchTerm)
                               ->orWhere('level', 'like', $searchTerm)
                               ->orWhere('parent_name', 'like', $searchTerm);
                  });
            });
        }
        
        // Filter by level if provided
        if ($request->has('level') && !empty($request->level)) {
            $query->whereHas('studentProfile', function($q) use ($request) {
                $q->where('level', $request->level);
            });
        }
        
        $students = $query->get();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="students.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];
        
        $columns = ['Name', 'Email', 'Age', 'Level', 'School', 'Parent Name', 'Parent Email'];
        
        $callback = function() use ($students, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($students as $student) {
                $row = [
                    $student->name,
                    $student->email,
                    $student->studentProfile->age ?? 'Not specified',
                    $student->studentProfile->level ?? 'Not specified',
                    $student->studentProfile->school ?? 'Not specified',
                    $student->studentProfile->parent_name ?? 'Not specified',
                    $student->studentProfile->parent_email ?? 'Not specified'
                ];
                
                fputcsv($file, $row);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Reassign teacher to a student
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $student
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reassignTeacher(Request $request, User $student)
    {
        if (!$student->isStudent()) {
            return redirect()->route('admin.students.index')->with('error', 'User is not a student.');
        }

        $request->validate([
            'teacher_id' => 'nullable|exists:users,id'
        ]);

        // Verify the teacher_id belongs to a teacher role if provided
        $teacher = null;
        if ($request->teacher_id) {
            $teacher = User::find($request->teacher_id);
            if (!$teacher || !$teacher->hasRole('teacher')) {
                return redirect()->route('admin.students.index')->with('error', 'Invalid teacher selected.');
            }
        }

        // Create or update student profile
        $profile = $student->studentProfile ?? new StudentProfile(['user_id' => $student->id]);
        $profile->teacher_id = $request->teacher_id;
        $profile->save();

        $message = $request->teacher_id 
            ? "Student {$student->name} has been assigned to teacher {$teacher->name}."
            : "Teacher assignment has been removed from student {$student->name}.";

        return redirect()->route('admin.students.index')->with('success', $message);
    }
}
