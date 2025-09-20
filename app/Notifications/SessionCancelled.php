<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ChessSession;
use App\Models\User;

class SessionCancelled extends Notification
{
    use Queueable;

    public $session;
    public $teacher;

    /**
     * Create a new notification instance.
     */
    public function __construct(ChessSession $session, User $teacher)
    {
        $this->session = $session;
        $this->teacher = $teacher;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Chess Session Cancelled')
                    ->line('Your chess session has been cancelled by your teacher.')
                    ->line('**Session Details:**')
                    ->line('Date: ' . $this->session->scheduled_at->format('l, F j, Y'))
                    ->line('Time: ' . $this->session->scheduled_at->format('g:i A'))
                    ->line('Duration: ' . $this->session->duration . ' minutes')
                    ->line('Teacher: ' . $this->teacher->name)
                    ->line('You can book a new session at your convenience.')
                    ->action('Book New Session', url('/student/booking/calendar'))
                    ->line('If you have any questions, please contact us.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Your chess session scheduled for ' . $this->session->scheduled_at->format('M j, Y g:i A') . ' has been cancelled by ' . $this->teacher->name,
            'session_id' => $this->session->id,
            'teacher_id' => $this->teacher->id,
            'teacher_name' => $this->teacher->name,
            'scheduled_at' => $this->session->scheduled_at,
            'duration' => $this->session->duration,
            'type' => 'session_cancelled'
        ];
    }
}
