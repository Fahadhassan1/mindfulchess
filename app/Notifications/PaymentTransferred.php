<?php

namespace App\Notifications;

use App\Models\Transfer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentTransferred extends Notification
{
    use Queueable;

    public $transfer;

    /**
     * Create a new notification instance.
     */
    public function __construct(Transfer $transfer)
    {
        $this->transfer = $transfer;
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
        $session = $this->transfer->session;
        $student = $session->student;
        
        return (new MailMessage)
            ->subject('Payment Transfer Confirmation - £' . number_format($this->transfer->amount, 2))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your payment has been successfully transferred to your Stripe Connect account.')
            ->line('**Transfer Details:**')
            ->line('• Amount: £' . number_format($this->transfer->amount, 2))
            ->line('• Session: ' . $session->session_name)
            ->line('• Student: ' . $student->name)
            ->line('• Duration: ' . $session->duration . ' minutes')
            ->line('• Session Date: ' . ($session->session_date ? $session->session_date->format('d M Y, H:i') : 'Not scheduled'))
            ->line('• Transfer ID: ' . $this->transfer->stripe_transfer_id)
            ->line('')
            ->line('**Payment Breakdown:**')
            ->line('• Total Session Amount: £' . number_format($this->transfer->total_session_amount, 2))
            ->line('• Application Fee: £' . number_format($this->transfer->application_fee, 2))
            ->line('• Your Earnings: £' . number_format($this->transfer->amount, 2))
            ->line('')
            ->line('💰 **The funds will appear in your bank account within 3-5 business days.**')
            ->line('')
            ->action('View Invoice in Portal', route('teacher.transfers.invoice', $this->transfer->id))
            ->line('You can also view all your payment invoices in your teacher portal under the "Transfers" section.')
            ->line('If you have any questions about this transfer, please contact our support team.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'transfer_id' => $this->transfer->id,
            'amount' => $this->transfer->amount,
            'session_id' => $this->transfer->session_id,
            'stripe_transfer_id' => $this->transfer->stripe_transfer_id,
        ];
    }
}
