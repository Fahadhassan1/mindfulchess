<?php

namespace App\Notifications;

use App\Models\Message;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMessage extends Notification implements ShouldQueue
{
    use Queueable;

    protected Message $message;
    protected User $sender;

    /**
     * Create a new notification instance.
     */
    public function __construct(Message $message, User $sender)
    {
        $this->message = $message;
        $this->sender = $sender;
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
        $senderRole = $this->sender->hasRole('teacher') ? 'Teacher' : 
                     ($this->sender->hasRole('student') ? 'Student' : 'Admin');
        
        $messagePreview = \Illuminate\Support\Str::limit($this->message->content, 100);
        // Link to conversation with the sender (since the notifiable is the recipient)
        $actionUrl = url('/messages/conversation/' . $this->sender->id);

        return (new MailMessage)
            ->subject('New Message from ' . $senderRole . ' - MindfulChess')
            ->markdown('emails.new-message', [
                'notifiable' => $notifiable,
                'sender' => $this->sender,
                'senderRole' => $senderRole,
                'messagePreview' => $messagePreview,
                'actionUrl' => $actionUrl
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message_id' => $this->message->id,
            'sender_id' => $this->sender->id,
            'sender_name' => $this->sender->name,
            'subject' => 'New message received',
            'preview' => \Illuminate\Support\Str::limit($this->message->content, 100),
        ];
    }
}
