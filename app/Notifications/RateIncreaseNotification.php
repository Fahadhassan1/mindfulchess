<?php

namespace App\Notifications;

use App\Models\ChessSession;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RateIncreaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected ChessSession $session;
    protected User $teacher;
    protected array $newRates;
    protected int $completedSessions;

    /**
     * Create a new notification instance.
     */
    public function __construct(ChessSession $session, User $teacher, array $newRates, int $completedSessions)
    {
        $this->session = $session;
        $this->teacher = $teacher;
        $this->newRates = $newRates;
        $this->completedSessions = $completedSessions;
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
        $teacherName = $this->teacher->name;
        $studentName = $notifiable->name;
        
        // Build rate comparison text
        $rateComparison = "";
        $oldRates = [
            '60' => ['price' => 4500, 'name' => 'Online 1 Hour'],
            '45' => ['price' => 3500, 'name' => 'Online 45 Minutes'],
            '30' => ['price' => 2500, 'name' => 'Online 30 Minutes']
        ];
        
        foreach ($this->newRates as $duration => $rateInfo) {
            $oldPrice = $oldRates[$duration]['price'] ?? 0;
            $difference = $rateInfo['price'] - $oldPrice;
            $rateComparison .= "• {$rateInfo['name']}: £" . number_format($oldPrice / 100, 2) . " → £" . number_format($rateInfo['price'] / 100, 2) . " (+£" . number_format($difference / 100, 2) . ")<br>";
        }
        
        // Generate secure token for public access
        $token = $this->generateRateRejectionToken($notifiable->id, $this->teacher->id);
        
        return (new MailMessage)
            ->subject('Important: Rate Update for Your Chess Lessons')
            ->greeting("Hello {$studentName}!")
            ->line("🎉 **Congratulations!** You've successfully completed **{$this->completedSessions} sessions** with **{$teacherName}**, one of our high-level chess instructors.")
            ->line("Due to the advanced level of instruction you're now receiving, your future sessions will be charged at our premium rates:")
            ->line("**New Lesson Rates (Effective Immediately):**")
            ->line($rateComparison)
            ->line("This rate adjustment reflects the premium quality of instruction you're receiving from {$teacherName}, who brings advanced expertise and specialized teaching methods to help accelerate your chess improvement.")
            ->line("**Not happy with the new rates?** If you would prefer to continue with a different teacher at standard rates, click the button below:")
            ->action('❌ I Need a Different Teacher', route('public.rate-increase.reject', [
                'student' => $notifiable->id,
                'teacher' => $this->teacher->id,
                'token' => $token
            ]))
            ->line("**Important Notes:**")
            ->line("• These new rates apply to all future bookings with {$teacherName}")
            ->line("• If you don't take any action, you'll automatically continue with {$teacherName} at the new rates")
            ->line("• If you request a teacher change, our team will contact you within 2 business days")
            ->line("Thank you for being a valued member of our chess community!")
            ->salutation("Best regards,\nThe " . config('app.name') . " Team");
    }

    /**
     * Generate a secure token for rate rejection links
     */
    private function generateRateRejectionToken($studentId, $teacherId)
    {
        // Create a hash based on student ID, teacher ID, and app key for security
        $data = $studentId . '|' . $teacherId . '|' . config('app.key');
        return hash('sha256', $data);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'session_id' => $this->session->id,
            'teacher_id' => $this->teacher->id,
            'completed_sessions' => $this->completedSessions,
            'new_rates' => $this->newRates,
        ];
    }
}
