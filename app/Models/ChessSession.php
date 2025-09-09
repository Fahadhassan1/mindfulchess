<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChessSession extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
        protected $fillable = [
        'student_id',
        'teacher_id',
        'subject',
        'description',
        'status',
        'scheduled_at',
        'duration',
        'lesson_date',
        'payment_id',
        'is_paid',
        'admin_notes',
        'session_type',
        'session_name',
        'suggested_availability'
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'amount' => 'decimal:2',
        'is_paid' => 'boolean',
        'suggested_availability' => 'array',
    ];
    
    /**
     * Get the payment record associated with the session.
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
    
    /**
     * Get the student associated with the session.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id')->with('studentProfile');
    }
    
    /**
     * Get the teacher associated with the session.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id')->with('teacherProfile');
    }

    /**
     * Get the homework assigned for this session.
     */
    public function homework()
    {
        return $this->hasMany(Homework::class, 'session_id');
    }

    /**
     * Get the transfer record for this session.
     */
    public function transfer()
    {
        return $this->hasOne(Transfer::class, 'session_id');
    }
}
