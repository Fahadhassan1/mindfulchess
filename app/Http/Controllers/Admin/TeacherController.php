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
            'qualification' => 'nullable|string|max:255',
            'teaching_type' => 'nullable|in:children,adult,kids,all',
            'stripe_account_id' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'experience_years' => 'nullable|integer|min:0',
            'specialties' => 'nullable|array',
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
            'qualification',
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
        
        $columns = ['Name', 'Email', 'Qualification', 'Teaching Type', 'Experience (Years)', 'Status'];
        
        $callback = function() use ($teachers, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($teachers as $teacher) {
                $row = [
                    $teacher->name,
                    $teacher->email,
                    $teacher->teacherProfile->qualification ?? 'Not specified',
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
}
