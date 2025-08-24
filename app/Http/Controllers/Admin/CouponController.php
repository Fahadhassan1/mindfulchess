<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /**
     * Display a listing of the coupons.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $query = Coupon::query();

        // Apply filters
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'expired') {
                $query->where('expiry_date', '<', now());
            } elseif ($request->status === 'valid') {
                $query->where('is_active', true)
                      ->where(function($q) {
                          $q->whereNull('expiry_date')
                            ->orWhere('expiry_date', '>', now());
                      });
            }
        }

        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where('code', 'like', $searchTerm);
        }

        $coupons = $query->orderBy('created_at', 'desc')->paginate(15);

        // Calculate statistics
        $stats = [
            'total_coupons' => Coupon::count(),
            'active_coupons' => Coupon::where('is_active', true)->count(),
            'expired_coupons' => Coupon::where('expiry_date', '<', now())->count(),
            'used_coupons' => Coupon::where('usage_count', '>', 0)->count(),
            'total_usage' => Coupon::sum('usage_count'),
        ];

        return view('admin.coupons.index', compact('coupons', 'stats'));
    }

    /**
     * Show the form for creating a new coupon.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('admin.coupons.create');
    }

    /**
     * Store a newly created coupon in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'discount_percentage' => 'required|numeric|min:1|max:100',
            'expiry_date' => 'nullable|date|after:today',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        Coupon::create([
            'code' => strtoupper($request->code),
            'discount_percentage' => $request->discount_percentage,
            'expiry_date' => $request->expiry_date,
            'usage_limit' => $request->usage_limit,
            'is_active' => $request->boolean('is_active', true),
            'usage_count' => 0,
        ]);

        return redirect()->route('admin.coupons.index')
                         ->with('success', 'Coupon created successfully.');
    }

    /**
     * Display the specified coupon.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\Contracts\View\View
     */
    public function show(Coupon $coupon)
    {
        return view('admin.coupons.show', compact('coupon'));
    }

    /**
     * Show the form for editing the specified coupon.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    /**
     * Update the specified coupon in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Coupon $coupon)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'discount_percentage' => 'required|numeric|min:1|max:100',
            'expiry_date' => 'nullable|date|after:today',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $coupon->update([
            'code' => strtoupper($request->code),
            'discount_percentage' => $request->discount_percentage,
            'expiry_date' => $request->expiry_date,
            'usage_limit' => $request->usage_limit,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.coupons.index')
                         ->with('success', 'Coupon updated successfully.');
    }

    /**
     * Remove the specified coupon from storage.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return redirect()->route('admin.coupons.index')
                         ->with('success', 'Coupon deleted successfully.');
    }

    /**
     * Toggle the active status of a coupon.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleActive(Coupon $coupon)
    {
        $coupon->update([
            'is_active' => !$coupon->is_active
        ]);

        $status = $coupon->is_active ? 'activated' : 'deactivated';
        
        return redirect()->back()
                         ->with('success', "Coupon has been {$status} successfully.");
    }

    /**
     * Export coupons to CSV.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request)
    {
        $query = Coupon::query();

        // Apply same filters as index
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $coupons = $query->orderBy('created_at', 'desc')->get();

        $filename = 'coupons_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($coupons) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Code', 'Discount %', 'Status', 'Usage Count', 'Usage Limit', 
                'Expiry Date', 'Created At'
            ]);

            foreach ($coupons as $coupon) {
                fputcsv($file, [
                    $coupon->code,
                    $coupon->discount_percentage,
                    $coupon->is_active ? 'Active' : 'Inactive',
                    $coupon->usage_count,
                    $coupon->usage_limit ?? 'Unlimited',
                    $coupon->expiry_date ? $coupon->expiry_date->format('Y-m-d') : 'No expiry',
                    $coupon->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
