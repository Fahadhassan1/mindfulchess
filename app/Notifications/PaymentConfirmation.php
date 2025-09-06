<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentConfirmation extends Notification
{
    use Queueable;
    
    /**
     * Payment data
     * 
     * @var array
     */
    protected $paymentData;
    
    /**
     * Session data
     * 
     * @var array
     */
    protected $sessionData;
    
    /**
     * User account data (if newly created)
     * 
     * @var array|null
     */
    protected $accountData;

    /**
     * Create a new notification instance.
     * 
     * @param array $paymentData
     * @param array $sessionData
     * @param array|null $accountData
     */
    public function __construct(array $paymentData, array $sessionData, ?array $accountData = null)
    {
        $this->paymentData = $paymentData;
        $this->sessionData = $sessionData;
        $this->accountData = $accountData;
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
        $message = (new MailMessage)
            ->subject('Payment Confirmation - Mindful Chess Lesson')
            ->greeting('Thank you for your payment!')
            ->line('We are pleased to confirm that your payment for a chess lesson has been successfully processed.')
            ->line('**Payment Details:**')
            // ->line('- Payment ID: ' . $this->paymentData['payment_id'])
            ->line('- Amount: £' . number_format($this->paymentData['amount'], 2))
            ->line('- Date: ' . $this->paymentData['paid_at']->format('d M Y, H:i'))
            ->line('**Session Details:**')
            ->line('- Session Type: ' . $this->sessionData['session_type_name'])
            ->line('- Duration: ' . $this->sessionData['duration'] . ' minutes')
            ->line('- Status: ' . ucfirst($this->sessionData['status']));
        
        if ($this->accountData) {
            $message->line('')
                ->line('**Your Account Details:**')
                ->line('- Email: ' . $this->accountData['email'])
                ->line('- Password: ' . $this->accountData['password'])
                ->line('For security reasons, please change your password after logging in.')
                ->action('Login to Your Account', url('/login'));
        }
        
        $message->line('')
            ->line('Our team will contact you shortly to schedule your chess lesson.')
            ->line('If you have any questions, please don\'t hesitate to contact us.')
            ->salutation('Kind regards,')
            ->salutation('The Mindful Chess Team');
            
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
            //
        ];
    }
}
