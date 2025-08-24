<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'payment_id',
        'customer_id',
        'customer_email',
        'customer_name',
        'amount',
        'original_amount',
        'currency',
        'status',
        'payment_method_type',
        'payment_method_id',
        'is_default',
        'coupon_code',
        'discount_percentage',
        'stripe_data',
        'paid_at',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'original_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'is_default' => 'boolean',
        'stripe_data' => 'array',
        'paid_at' => 'datetime',
    ];
    
    /**
     * Get the chess session associated with the payment.
     */
    public function chessSession()
    {
        return $this->hasOne(ChessSession::class);
    }
    
    /**
     * Get the session associated with the payment.
     */
    public function session()
    {
        return $this->hasOne(ChessSession::class);
    }
    
    /**
     * Get the user associated with the payment.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'customer_email', 'email');
    }
    
    /**
     * Set this payment method as the default for the customer
     */
    public function setAsDefault()
    {
        // First, unset all other default payment methods for this customer
        static::where('customer_email', $this->customer_email)
              ->where('id', '!=', $this->id)
              ->update(['is_default' => false]);
        
        // Set this payment as default
        $this->update(['is_default' => true]);
    }
    
    /**
     * Get the default payment method for a customer
     */
    public static function getDefaultForCustomer($customerEmail)
    {
        return static::where('customer_email', $customerEmail)
                    ->where('status', 'succeeded')
                    ->where('is_default', true)
                    ->whereNotNull('payment_method_id')
                    ->first();
    }
    
    /**
     * Get the latest payment method for a customer (fallback if no default)
     */
    public static function getLatestForCustomer($customerEmail)
    {
        return static::where('customer_email', $customerEmail)
            ->whereNotNull('payment_method_id')
            ->where('status', 'succeeded')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Check if this payment method is still valid in Stripe
     */
    public function isValidInStripe()
    {
        if (!$this->payment_method_id) {
            return false;
        }

        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            $paymentMethod = \Stripe\PaymentMethod::retrieve($this->payment_method_id);
            return $paymentMethod && !$paymentMethod->isDeleted();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Payment method validation failed: ' . $e->getMessage(), [
                'payment_id' => $this->id,
                'payment_method_id' => $this->payment_method_id
            ]);
            return false;
        }
    }
}
