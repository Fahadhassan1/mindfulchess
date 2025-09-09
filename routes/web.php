<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// redirect to login page
Route::get('/', function () {
    return redirect()->route('login');
});

// Checkout routes
Route::get('/checkout', [App\Http\Controllers\CheckoutController::class, 'index'])->name('checkout');
Route::post('/checkout/process', [App\Http\Controllers\CheckoutController::class, 'processPayment'])->name('checkout.process');
Route::get('/checkout/success', [App\Http\Controllers\CheckoutController::class, 'success'])->name('checkout.success');
Route::post('/coupon/validate', [App\Http\Controllers\CouponController::class, 'validateCoupon'])->name('coupon.validate');

// Session assignment routes
Route::get('/sessions/assign/{session}', [App\Http\Controllers\SessionAssignmentController::class, 'assignTeacher'])
    ->name('sessions.assign')
    ->middleware(['signed']); // This ensures the URL signature is valid

Route::post('/sessions/confirm-time/{session}', [App\Http\Controllers\SessionAssignmentController::class, 'confirmSessionTime'])
    ->name('sessions.confirm-time');

// Default dashboard route - redirects based on role
Route::get('/dashboard', function () {
    if (auth()->check()) {
        $user = auth()->user();
        $roles = $user->roles->pluck('name')->toArray();
        
        if (in_array('admin', $roles)) {
            return redirect()->route('admin.dashboard');
        } elseif (in_array('teacher', $roles)) {
            return redirect()->route('teacher.dashboard');
        } elseif (in_array('student', $roles)) {
            return redirect()->route('student.dashboard');
        }
    }
    
    // Default view if no role or not authenticated
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\AdminController::class, 'index'])->name('dashboard');
    Route::get('/users', [App\Http\Controllers\AdminController::class, 'manageUsers'])->name('users');
    
    // User management routes
    Route::get('/users/create', [App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create');
    Route::post('/users', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
    
    // Teacher management routes
    Route::get('/teachers', [App\Http\Controllers\Admin\TeacherController::class, 'index'])->name('teachers.index');
    Route::get('/teachers/export', [App\Http\Controllers\Admin\TeacherController::class, 'export'])->name('teachers.export');
    Route::get('/teachers/statistics', [App\Http\Controllers\Admin\TeacherController::class, 'statistics'])->name('teachers.statistics');
    Route::get('/teachers/{teacher}/statistics', [App\Http\Controllers\Admin\TeacherController::class, 'teacherStatistics'])->name('teachers.statistics.show');
    Route::get('/teachers/{teacher}', [App\Http\Controllers\Admin\TeacherController::class, 'show'])->name('teachers.show');
    Route::get('/teachers/{teacher}/edit', [App\Http\Controllers\Admin\TeacherController::class, 'edit'])->name('teachers.edit');
    Route::put('/teachers/{teacher}', [App\Http\Controllers\Admin\TeacherController::class, 'update'])->name('teachers.update');
    Route::put('/teachers/{teacher}/toggle-active', [App\Http\Controllers\Admin\TeacherController::class, 'toggleActive'])->name('teachers.toggle-active');
    
    // Student management routes
    Route::get('/students', [App\Http\Controllers\Admin\StudentController::class, 'index'])->name('students.index');
    Route::get('/students/export', [App\Http\Controllers\Admin\StudentController::class, 'export'])->name('students.export');
    Route::get('/students/{student}', [App\Http\Controllers\Admin\StudentController::class, 'show'])->name('students.show');
    Route::get('/students/{student}/edit', [App\Http\Controllers\Admin\StudentController::class, 'edit'])->name('students.edit');
    Route::put('/students/{student}', [App\Http\Controllers\Admin\StudentController::class, 'update'])->name('students.update');
    Route::put('/students/{student}/reassign-teacher', [App\Http\Controllers\Admin\StudentController::class, 'reassignTeacher'])->name('students.reassign-teacher');
    
    // Session management routes
    Route::get('/sessions', [App\Http\Controllers\Admin\SessionController::class, 'index'])->name('sessions.index');
    Route::get('/sessions/export', [App\Http\Controllers\Admin\SessionController::class, 'export'])->name('sessions.export');
    Route::get('/sessions/{session}', [App\Http\Controllers\Admin\SessionController::class, 'show'])->name('sessions.show');
    Route::put('/sessions/{session}/status', [App\Http\Controllers\Admin\SessionController::class, 'updateStatus'])->name('sessions.update-status');
    
    // Payment management routes
    Route::get('/payments', [App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/export', [App\Http\Controllers\Admin\PaymentController::class, 'export'])->name('payments.export');
    Route::get('/payments/{payment}', [App\Http\Controllers\Admin\PaymentController::class, 'show'])->name('payments.show');
    Route::get('/payments/{payment}/invoice', [App\Http\Controllers\Admin\PaymentController::class, 'showInvoice'])->name('payments.invoice');
    Route::post('/payments/{payment}/refund', [App\Http\Controllers\Admin\PaymentController::class, 'refund'])->name('payments.refund');
    
    // Transfer management routes
    Route::get('/transfers', [App\Http\Controllers\Admin\TransferController::class, 'index'])->name('transfers.index');
    Route::get('/transfers/export', [App\Http\Controllers\Admin\TransferController::class, 'export'])->name('transfers.export');
    Route::get('/transfers/{transfer}', [App\Http\Controllers\Admin\TransferController::class, 'show'])->name('transfers.show');
    Route::get('/transfers/{transfer}/invoice', [App\Http\Controllers\Admin\TransferController::class, 'showInvoice'])->name('transfers.invoice');
    Route::post('/transfers/process-pending', [App\Http\Controllers\Admin\TransferController::class, 'processPending'])->name('transfers.process-pending');
    Route::post('/transfers/{transfer}/retry', [App\Http\Controllers\Admin\TransferController::class, 'retry'])->name('transfers.retry');
    
    // Coupon management routes
    Route::get('/coupons', [App\Http\Controllers\Admin\CouponController::class, 'index'])->name('coupons.index');
    Route::get('/coupons/create', [App\Http\Controllers\Admin\CouponController::class, 'create'])->name('coupons.create');
    Route::post('/coupons', [App\Http\Controllers\Admin\CouponController::class, 'store'])->name('coupons.store');
    Route::get('/coupons/export', [App\Http\Controllers\Admin\CouponController::class, 'export'])->name('coupons.export');
    Route::get('/coupons/{coupon}', [App\Http\Controllers\Admin\CouponController::class, 'show'])->name('coupons.show');
    Route::get('/coupons/{coupon}/edit', [App\Http\Controllers\Admin\CouponController::class, 'edit'])->name('coupons.edit');
    Route::put('/coupons/{coupon}', [App\Http\Controllers\Admin\CouponController::class, 'update'])->name('coupons.update');
    Route::delete('/coupons/{coupon}', [App\Http\Controllers\Admin\CouponController::class, 'destroy'])->name('coupons.destroy');
    Route::patch('/coupons/{coupon}/toggle-active', [App\Http\Controllers\Admin\CouponController::class, 'toggleActive'])->name('coupons.toggle-active');
});

// Teacher routes
Route::middleware(['auth', 'role:teacher|admin'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\TeacherController::class, 'index'])->name('dashboard');
    Route::get('/students', [App\Http\Controllers\TeacherController::class, 'students'])->name('students');
    Route::get('/transfers', [App\Http\Controllers\TeacherController::class, 'transfers'])->name('transfers');
    Route::get('/transfers/{transfer}/invoice', [App\Http\Controllers\TeacherController::class, 'showInvoice'])->name('transfers.invoice');
    Route::get('/profile', [App\Http\Controllers\TeacherController::class, 'profile'])->name('profile');
    Route::put('/profile', [App\Http\Controllers\TeacherController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/toggle-active', [App\Http\Controllers\TeacherController::class, 'toggleActive'])->name('profile.toggle-active');
    Route::get('/availability', [App\Http\Controllers\TeacherController::class, 'availability'])->name('availability');
    Route::post('/availability', [App\Http\Controllers\TeacherController::class, 'storeAvailability'])->name('availability.store');
    Route::delete('/availability/{availability}', [App\Http\Controllers\TeacherController::class, 'destroyAvailability'])->name('availability.destroy');
    Route::get('/sessions', [App\Http\Controllers\TeacherController::class, 'sessions'])->name('sessions');
    Route::post('/sessions/{session}/confirm', [App\Http\Controllers\TeacherController::class, 'confirmSession'])->name('sessions.confirm');
    Route::post('/sessions/{session}/complete', [App\Http\Controllers\TeacherController::class, 'completeSession'])->name('sessions.complete');
    Route::get('/sessions/{session}', [App\Http\Controllers\TeacherController::class, 'showSession'])->name('sessions.show');
    Route::put('/sessions/{session}/notes', [App\Http\Controllers\TeacherController::class, 'updateSessionNotes'])->name('sessions.update-notes');
    Route::get('/sessions/{session}/assign-homework', [App\Http\Controllers\TeacherController::class, 'showAssignHomework'])->name('sessions.assign-homework');
    Route::post('/sessions/{session}/homework', [App\Http\Controllers\TeacherController::class, 'storeHomework'])->name('sessions.store-homework');
    Route::get('/stripe-setup', [App\Http\Controllers\TeacherController::class, 'showStripeSetup'])->name('stripe.setup');
    Route::post('/stripe-setup', [App\Http\Controllers\TeacherController::class, 'updateStripeAccount'])->name('stripe.update');
    
    // Teacher booking routes (for booking sessions for their students)
    Route::get('/booking/{student}/calendar', [App\Http\Controllers\TeacherBookingController::class, 'showCalendar'])->name('booking.calendar');
    Route::post('/booking/{student}/process', [App\Http\Controllers\TeacherBookingController::class, 'processBooking'])->name('booking.process');
    Route::get('/booking/{student}/payment', [App\Http\Controllers\TeacherBookingController::class, 'showPayment'])->name('booking.payment');
    Route::post('/booking/{student}/payment/process', [App\Http\Controllers\TeacherBookingController::class, 'processPayment'])->name('booking.payment.process');
    
    // Debug route to check premium pricing
    Route::get('/booking/{student}/debug', function($studentId) {
        $teacher = auth()->user();
        $student = \App\Models\User::with(['studentProfile'])->find($studentId);
        $teacher = \App\Models\User::with(['teacherProfile'])->find($teacher->id);
        
        $sessionCount = \App\Models\ChessSession::where('student_id', $student->id)
                                              ->where('teacher_id', $teacher->id)
                                              ->count();
        
        return response()->json([
            'student_id' => $student->id,
            'student_name' => $student->name,
            'teacher_id' => $teacher->id,
            'teacher_name' => $teacher->name,
            'teacher_has_profile' => $teacher->teacherProfile ? true : false,
            'is_high_level' => $teacher->teacherProfile ? $teacher->teacherProfile->is_high_level : null,
            'session_count' => $sessionCount,
            'should_use_premium' => $sessionCount >= 10 && $teacher->teacherProfile && $teacher->teacherProfile->is_high_level == 1,
            'standard_rates' => [
                '30' => 25.00,
                '45' => 35.00,
                '60' => 45.00
            ],
            'premium_rates' => [
                '30' => 27.50,
                '45' => 38.75,
                '60' => 50.00
            ]
        ]);
    })->name('booking.debug');
});

// Student routes
Route::middleware(['auth', 'role:student|teacher|admin'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\StudentController::class, 'index'])->name('dashboard');
    Route::get('/teachers', [App\Http\Controllers\StudentController::class, 'teachers'])->name('teachers');
    Route::get('/profile', [App\Http\Controllers\StudentController::class, 'profile'])->name('profile');
    Route::put('/profile', [App\Http\Controllers\StudentController::class, 'updateProfile'])->name('profile.update');
    Route::get('/sessions', [App\Http\Controllers\StudentController::class, 'sessions'])->name('sessions');
    Route::get('/payments', [App\Http\Controllers\StudentController::class, 'payments'])->name('payments');
    Route::get('/payments/{payment}/invoice', [App\Http\Controllers\StudentController::class, 'invoice'])->name('payments.invoice');
    Route::get('/sessions/{session}', [App\Http\Controllers\StudentController::class, 'showSession'])->name('sessions.show');
    
    // Additional booking routes
    Route::get('/booking/calendar', [App\Http\Controllers\StudentBookingController::class, 'showCalendar'])->name('booking.calendar');
    Route::post('/booking/process', [App\Http\Controllers\StudentBookingController::class, 'processBooking'])->name('booking.process');
    Route::get('/booking/payment', [App\Http\Controllers\StudentBookingController::class, 'showPayment'])->name('booking.payment');
    Route::post('/booking/payment/process', [App\Http\Controllers\StudentBookingController::class, 'processPayment'])->name('booking.payment.process');
    
    // Payment method management
    Route::get('/payment-methods', [App\Http\Controllers\StudentController::class, 'paymentMethods'])->name('payment-methods');
    Route::get('/payment-methods/update', [App\Http\Controllers\StudentController::class, 'showUpdatePaymentMethod'])->name('payment-methods.update');
    Route::post('/payment-methods/update', [App\Http\Controllers\StudentController::class, 'updatePaymentMethod'])->name('payment-methods.update.process');
    Route::post('/payment-methods/{payment}/set-default', [App\Http\Controllers\StudentController::class, 'setDefaultPaymentMethod'])->name('payment-methods.set-default');
    Route::get('/homework', [App\Http\Controllers\StudentController::class, 'homework'])->name('homework');
    Route::get('/homework/{homework}', [App\Http\Controllers\StudentController::class, 'showHomework'])->name('homework.show');
    Route::get('/homework/{homework}/download', [App\Http\Controllers\StudentController::class, 'downloadHomework'])->name('homework.download');
    Route::put('/homework/{homework}/status', [App\Http\Controllers\StudentController::class, 'updateHomeworkStatus'])->name('homework.update-status');
});

// Public rate rejection route (no authentication required)
Route::get('/public/rate-increase/reject/{student}/{teacher}/{token}', [App\Http\Controllers\PublicController::class, 'rejectRateIncrease'])->name('public.rate-increase.reject');

// Student specific routes (authenticated)
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    // Rate rejection route (authenticated fallback)
    Route::get('/rate-increase/reject/{student}/{teacher}', [App\Http\Controllers\StudentController::class, 'rejectRateIncrease'])->name('rate-increase.reject');
});

// Sessions routes for all authenticated users
Route::middleware('auth')->group(function () {
    Route::get('/sessions', [App\Http\Controllers\SessionController::class, 'index'])->name('sessions.manage');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


require __DIR__.'/auth.php';
