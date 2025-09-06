<?php

namespace App\Notifications;

use App\Models\Message;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MessageFlagged extends Notification implements ShouldQueue
{
    use Queueable;

    protected Message $message;
    protected User $sender;
    protected User $recipient;
    protected array $flaggedReasons;

    /**
     * Create a new notification instance.
     */
    public function __construct(Message $message, User $sender, User $recipient, array $flaggedReasons)
    {
        $this->message = $message;
        $this->sender = $sender;
        $this->recipient = $recipient;
        $this->flaggedReasons = $flaggedReasons;
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
        
        $recipientRole = $this->recipient->hasRole('teacher') ? 'Teacher' : 
                        ($this->recipient->hasRole('student') ? 'Student' : 'Admin');

        $adminUrl = url('/admin/messages/' . $this->message->id);

        return (new MailMessage)
            ->subject('🚨 Message Flagged for Review - MindfulChess')
            ->markdown('emails.admin.message-flagged', [
                'message' => $this->message,
                'sender' => $this->sender,
                'recipient' => $this->recipient,
                'senderRole' => $senderRole,
                'recipientRole' => $recipientRole,
                'flaggedReasons' => $this->flaggedReasons,
                'adminUrl' => $adminUrl,
                'messagePreview' => \Illuminate\Support\Str::limit($this->message->content, 200)
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
            'recipient_id' => $this->recipient->id,
            'flagged_reasons' => $this->flaggedReasons,
            'subject' => 'Message flagged for review',
            'priority' => 'high'
        ];
    }
}
