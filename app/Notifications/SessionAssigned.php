<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ChessSession;
use App\Models\User;

class SessionAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    protected $session;
    protected $teacher;

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\ChessSession  $session
     * @param  \App\Models\User  $teacher
     * @return void
     */
    public function __construct(ChessSession $session, User $teacher)
    {
        $this->session = $session;
        $this->teacher = $teacher;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $teacherProfileUrl = url('/student/sessions/' . $this->session->id);
        
        $message = (new MailMessage)
            ->subject('Your Chess Session Has Been Assigned')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('Great news! Your chess session has been assigned to a teacher.')
            ->line('Session details:')
            ->line('- Session: ' . $this->session->session_name)
            ->line('- Duration: ' . $this->session->duration . ' minutes')
            ->line('- Teacher: ' . $this->teacher->name)
            ->action('View Session Details', $teacherProfileUrl)
            ->line('Please log in to your student portal to see all details and communicate with your teacher.');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'session_id' => $this->session->id,
            'teacher_id' => $this->teacher->id,
            'teacher_name' => $this->teacher->name,
            'session_name' => $this->session->session_name,
        ];
    }
}
