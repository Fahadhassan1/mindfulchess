<?php

namespace App\Notifications;

use App\Models\ChessSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class SessionAssignmentRequest extends Notification implements ShouldQueue
{
    use Queueable;
    
    /**
     * The chess session
     * 
     * @var \App\Models\ChessSession
     */
    protected $session;
    
    /**
     * Student information
     * 
     * @var array
     */
    protected $studentInfo;

    /**
     * Create a new notification instance.
     * 
     * @param \App\Models\ChessSession $session
     * @param array $studentInfo
     */
    public function __construct(ChessSession $session, array $studentInfo)
    {
        $this->session = $session;
        $this->studentInfo = $studentInfo;
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
        // Create a signed URL that will work only once
        $url = URL::temporarySignedRoute(
            'sessions.assign',
            now()->addDays(7), // Link expires after 7 days
            [
                'session' => $this->session->id,
                'teacher' => $notifiable->id
            ]
        );

        return (new MailMessage)
            ->subject('New Chess Session Assignment Request')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new chess session is available for assignment.')
            ->line('**Session Details:**')
            ->line('- Type: ' . $this->session->session_type)
            ->line('- Duration: ' . $this->session->duration . ' minutes')
            ->line('- Name: ' . $this->session->session_name)
            ->line('**Student Information:**')
            ->line('- Name: ' . $this->studentInfo['name'])
            ->line('- Email: ' . $this->studentInfo['email'])
            ->action('Accept This Session', $url)
            ->line('This link will expire in 7 days. If you accept this session, you will be assigned as the teacher for this student.')
            ->line('Note: If another teacher accepts this session first, you will be notified when you attempt to accept it.')
            ->salutation('Thank you for your contribution to Mindful Chess!');
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
            'student_name' => $this->studentInfo['name'],
            'session_type' => $this->session->session_type,
            'session_duration' => $this->session->duration
        ];
    }
}
