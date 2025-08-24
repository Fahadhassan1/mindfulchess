<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'session_id',
        'amount',
        'application_fee',
        'total_session_amount',
        'stripe_transfer_id',
        'status',
        'transferred_at',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'application_fee' => 'decimal:2',
        'total_session_amount' => 'decimal:2',
        'transferred_at' => 'datetime',
    ];

    /**
     * Get the teacher for this transfer.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id')->with('teacherProfile');
    }

    /**
     * Get the session for this transfer.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(ChessSession::class, 'session_id');
    }

    /**
     * Calculate teacher payment based on session duration and amount.
     */
    public static function calculateTeacherPayment($sessionAmount, $duration)
    {
        $paymentBreakdown = [
            60 => ['teacher_percentage' => 55.56, 'teacher_amount' => 25.00, 'app_fee' => 20.00], // 25/45 = 55.56%
            45 => ['teacher_percentage' => 53.57, 'teacher_amount' => 18.75, 'app_fee' => 16.25], // 18.75/35 = 53.57%
            30 => ['teacher_percentage' => 50.00, 'teacher_amount' => 12.50, 'app_fee' => 12.50], // 12.50/25 = 50%
        ];

        if (isset($paymentBreakdown[$duration])) {
            return $paymentBreakdown[$duration];
        }

        // Fallback: 50% to teacher, 50% app fee
        $teacherAmount = $sessionAmount * 0.5;
        return [
            'teacher_percentage' => 50.00,
            'teacher_amount' => $teacherAmount,
            'app_fee' => $sessionAmount - $teacherAmount
        ];
    }
}
