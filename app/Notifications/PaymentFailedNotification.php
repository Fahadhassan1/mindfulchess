<?php

namespace App\Notifications;

use App\Models\ChessSession;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected ChessSession $session;
    protected string $errorMessage;

    /**
     * Create a new notification instance.
     */
    public function __construct(ChessSession $session, string $errorMessage)
    {
        $this->session = $session;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $studentName = $notifiable->name;
        $teacherName = $this->session->teacher->name;
        $sessionDate = $this->session->scheduled_at ? $this->session->scheduled_at->format('M d, Y \a\t H:i') : 'Recent session';
        $sessionAmount = '£' . number_format($this->session->payment ? $this->session->payment->amount : 0, 2);
        
        return (new MailMessage)
            ->subject('Payment Failed - Action Required')
            ->greeting("Hello {$studentName}!")
            ->line("⚠️ **Payment Failed for Your Chess Session**")
            ->line("We were unable to process payment for your completed session with {$teacherName}.")
            ->line("**Session Details:**")
            ->line("• Teacher: {$teacherName}")
            ->line("• Session: {$this->session->session_name}")
            ->line("• Date: {$sessionDate}")
            ->line("• Amount: {$sessionAmount}")
            ->line("**Why did this happen?**")
            ->line("Your payment method may have expired, been declined, or there might be insufficient funds.")
            ->line("**What you need to do:**")
            ->line("1. Update your payment method in your student portal")
            ->line("2. We'll automatically retry the payment once updated")
            ->line("3. Contact support if you continue to experience issues")
            ->action('🔄 Update Payment Method', route('student.payment-methods.update'))
            ->line("**Important:** Please update your payment method as soon as possible to avoid any service interruptions.")
            ->line("If you have any questions, please don't hesitate to contact our support team.")
            ->salutation("Best regards,\nThe " . config('app.name') . " Team");
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'session_id' => $this->session->id,
            'teacher_id' => $this->session->teacher_id,
            'error_message' => $this->errorMessage,
            'amount' => $this->session->payment ? $this->session->payment->amount : 0,
        ];
    }
}
