<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /**
     * Validate a coupon code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string',
        ]);

        $couponCode = $request->coupon_code;
        $coupon = Coupon::where('code', $couponCode)->first();

        if (!$coupon) {
            return response()->json([
                'valid' => false,
                'message' => 'Coupon code not found.',
            ]);
        }

        if (!$coupon->isValid()) {
            $message = 'This coupon is no longer valid.';
            if ($coupon->expiry_date && $coupon->expiry_date->isPast()) {
                $message = 'This coupon has expired.';
            } elseif (!$coupon->is_active) {
                $message = 'This coupon is inactive.';
            } elseif ($coupon->usage_limit && $coupon->usage_count >= $coupon->usage_limit) {
                $message = 'This coupon has reached its usage limit.';
            }

            return response()->json([
                'valid' => false,
                'message' => $message,
            ]);
        }

        return response()->json([
            'valid' => true,
            'discount_percentage' => $coupon->discount_percentage,
            'message' => 'Coupon applied successfully! ' . $coupon->discount_percentage . '% discount added.',
        ]);
    }
}
