<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ChessSession;
use App\Models\User;
use App\Models\Payment;

class AdditionalSessionBooked extends Notification implements ShouldQueue
{
    use Queueable;

    protected $session;
    protected $teacher;
    protected $payment;

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\ChessSession  $session
     * @param  \App\Models\User  $teacher
     * @param  \App\Models\Payment  $payment
     * @return void
     */
    public function __construct(ChessSession $session, User $teacher, Payment $payment)
    {
        $this->session = $session;
        $this->teacher = $teacher;
        $this->payment = $payment;
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
        $sessionUrl = url('/student/sessions/' . $this->session->id);
        
        $scheduledTime = $this->session->scheduled_at ? $this->session->scheduled_at->format('D, M j, Y \a\t g:i A') : 'To be determined';
        
        $message = (new MailMessage)
            ->subject('Additional Chess Session Booked - Mindful Chess')
            ->greeting('Thank you for booking an additional session!')
            ->line('Your additional chess session has been successfully booked and confirmed.')
            ->line('**Session Details:**')
            ->line('- Session Type: ' . ucfirst($this->session->session_type))
            ->line('- Duration: ' . $this->session->duration . ' minutes')
            ->line('- Scheduled for: ' . $scheduledTime)
            ->line('- Teacher: ' . $this->teacher->name)
            ->line('')
            ->line('**Payment Details:**')
            ->line('- Amount: £' . number_format($this->payment->amount, 2))
            ->line('- Date: ' . $this->payment->paid_at->format('d M Y, H:i'));
        
        $message->action('View Session Details', $sessionUrl)
            ->line('')
            ->line('If you need to make any changes to this session, please contact your teacher directly or through our platform.')
            ->line('We hope you enjoy your chess session!')
            ->salutation('Kind regards,')
            ->salutation('The Mindful Chess Team');
            
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
            'scheduled_at' => $this->session->scheduled_at,
            'payment_id' => $this->payment->id
        ];
    }
}
