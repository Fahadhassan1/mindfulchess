<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the teacher profile associated with the user.
     */
    public function teacherProfile()
    {
        return $this->hasOne(TeacherProfile::class);
    }

    /**
     * Get the student profile associated with the user.
     */
    public function studentProfile()
    {
        return $this->hasOne(StudentProfile::class);
    }

    /**
     * Check if user is a teacher.
     */
    public function isTeacher()
    {
        return $this->hasRole('teacher');
    }

    /**
     * Check if user is a student.
     */
    public function isStudent()
    {
        return $this->hasRole('student');
    }
    
    /**
     * Get the availability slots for the teacher.
     */
    public function availability()
    {
        return $this->hasMany(TeacherAvailability::class);
    }

    /**
     * Get messages sent by this user.
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get messages received by this user.
     */
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'recipient_id');
    }

    /**
     * Get all messages for this user (sent and received).
     */
    public function messages()
    {
        return Message::where('sender_id', $this->id)
                     ->orWhere('recipient_id', $this->id);
    }
}
