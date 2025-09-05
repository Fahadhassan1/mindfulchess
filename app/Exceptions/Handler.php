<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Admin;
use App\Notifications\ExceptionOccurred;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // $this->sendExceptionNotification($e);
        });
    }
    
    /**
     * Send an email notification about the exception to the admin.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    private function sendExceptionNotification(Throwable $exception): void
    {
        try {
            if (!$this->shouldReport($exception)) {
                return;
            }
            
            $adminEmail = config('app.admin_email');
            
            if (empty($adminEmail)) {
                Log::warning('Cannot send exception notification: No admin email configured');
                return;
            }
            
            $request = request();
            $user = Auth::user();
            
            (new Admin($adminEmail))->notify(new ExceptionOccurred($exception, $request, $user));
            
            Log::info('Exception notification sent to admin', [
                'admin_email' => $adminEmail,
                'exception' => get_class($exception),
                'message' => $exception->getMessage()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send exception notification', [
                'exception' => get_class($e),
                'message' => $e->getMessage()
            ]);
        }
    }
}
