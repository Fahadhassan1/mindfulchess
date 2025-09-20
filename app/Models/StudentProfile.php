<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'age',
        'level',
        'chess_rating',
        'parent_name',
        'parent_email',
        'parent_phone',
        'school',
        'learning_goals',
        'session_type_preference',
        'profile_image',
        'teacher_id',
        'rate_increase_notified',
        'rate_increase_notified_at',
        'rate_rejected',
        'rate_rejected_teacher_id',
        'rate_rejected_at',
        'rate_rejection_reason',
        'payment_method_id',
        'customer_id',
        'payment_method_updated_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'rate_increase_notified' => 'boolean',
        'rate_increase_notified_at' => 'datetime',
        'rate_rejected' => 'boolean',
        'rate_rejected_at' => 'datetime',
        'payment_method_updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the student profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the teacher assigned to this student.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the teacher whose rate was rejected.
     */
    public function rejectedRateTeacher()
    {
        return $this->belongsTo(User::class, 'rate_rejected_teacher_id');
    }
}
