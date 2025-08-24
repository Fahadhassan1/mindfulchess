<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'discount_percentage',
        'is_active',
        'expiry_date',
        'usage_limit',
        'usage_count'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expiry_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Check if the coupon is valid.
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->is_active &&
               ($this->expiry_date === null || $this->expiry_date->isFuture()) &&
               ($this->usage_limit === null || $this->usage_count < $this->usage_limit);
    }

    /**
     * Increment the usage count of the coupon.
     *
     * @return void
     */
    public function incrementUsage()
    {
        $this->increment('usage_count');
    }
}
