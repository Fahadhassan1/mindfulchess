<?php

namespace App\Notifications;

use App\Models\Homework;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class HomeworkAssigned extends Notification implements ShouldQueue
{
    use Queueable;
    
    /**
     * The homework assignment
     * 
     * @var \App\Models\Homework
     */
    protected $homework;

    /**
     * Create a new notification instance.
     * 
     * @param \App\Models\Homework $homework
     */
    public function __construct(Homework $homework)
    {
        $this->homework = $homework;
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
        $sessionDate = $this->homework->session->scheduled_at 
            ? $this->homework->session->scheduled_at->format('F j, Y \a\t g:i A') 
            : 'Recent session';
            
        $teacherName = $this->homework->teacher->name;
        
        $message = (new MailMessage)
            ->subject('New Homework Assignment - ' . $this->homework->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have received a new homework assignment from your chess teacher.')
            ->line('**Assignment Details:**')
            ->line('**Title:** ' . $this->homework->title)
            ->line('**Description:** ' . $this->homework->description);
            
        if ($this->homework->instructions) {
            $message->line('**Instructions:** ' . $this->homework->instructions);
        }
        
        $message->line('**Teacher:** ' . $teacherName)
                ->line('**Session:** ' . $sessionDate);
                
        if ($this->homework->attachment_path) {
            $message->line('📎 This assignment includes an attachment that you can download from your student portal.');
        }
        
        $message->action('View Homework', route('student.homework.show', $this->homework))
                ->line('Please log into your student portal to view the complete assignment and download any attachments.')
                ->line('Happy learning!')
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
            'homework_id' => $this->homework->id,
            'homework_title' => $this->homework->title,
            'session_id' => $this->homework->session_id,
            'teacher_name' => $this->homework->teacher->name,
        ];
    }
}
