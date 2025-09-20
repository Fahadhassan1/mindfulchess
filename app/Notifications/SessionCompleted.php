<?php

namespace App\Notifications;

use App\Models\ChessSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SessionCompleted extends Notification implements ShouldQueue
{
    use Queueable;
    
    /**
     * The chess session
     * 
     * @var \App\Models\ChessSession
     */
    protected $session;

    /**
     * Create a new notification instance.
     * 
     * @param \App\Models\ChessSession $session
     */
    public function __construct(ChessSession $session)
    {
        $this->session = $session;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
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
        $teacherName = $this->session->teacher->name;
        $sessionDate = $this->session->completed_at ? $this->session->completed_at->format('F j, Y \a\t g:i A') : now()->format('F j, Y \a\t g:i A');
        
        $message = (new MailMessage)
            ->subject('Session Completed - Thank You!')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your chess session with ' . $teacherName . ' has been completed.')
            ->line('')
            ->line('**Session Details:**')
            ->line('• **Session:** ' . $this->session->session_name)
            ->line('• **Duration:** ' . $this->session->duration . ' minutes')
            ->line('• **Completed:** ' . $sessionDate)
            ->line('• **Teacher:** ' . $teacherName);
        
        // Add meeting link if available
        if ($this->session->meeting_link) {
            $message->line('• **Meeting Link:** ' . $this->session->meeting_link);
        }

        // Add payment information if available
        if ($this->session->payment) {
            $message->line('')
                    ->line('**Payment Details:**')
                    ->line('• **Amount Charged:** £' . number_format($this->session->payment->amount, 2))
                    ->line('• **Payment Date:** ' . $this->session->payment->paid_at->format('F j, Y \a\t g:i A'));
        }
            
        if ($this->session->notes) {
            $message->line('')
                    ->line('**Teacher Notes:** ' . $this->session->notes);
        }
        
        $message->line('')
                ->line('We hope you enjoyed your chess lesson! Here are some next steps:')
                ->line('• Review any homework assignments from this session')
                ->line('• Practice the concepts you learned')
                ->line('• Book your next session to continue improving')
                ->line('')
                ->action('View Session Details', route('student.sessions.show', $this->session))
                ->action('Book Another Session', route('student.booking.calendar'))
                ->line('')
                ->line('Thank you for choosing Mindful Chess for your chess education!')
                ->salutation('Best regards, Mindful Chess Team');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'session_id' => $this->session->id,
            'session_name' => $this->session->session_name,
            'teacher_name' => $this->session->teacher->name,
            'completed_at' => $this->session->completed_at,
        ];
    }
}
