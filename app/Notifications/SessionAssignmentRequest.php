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

        $emailMessage = (new MailMessage)
            ->subject('New Chess Session Assignment Request')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new chess session is available for assignment.')
            ->line('**Session Details:**')
            ->line('- Type: ' . $this->session->session_type)
            ->line('- Duration: ' . $this->session->duration . ' minutes')
            ->line('- Name: ' . $this->session->session_name)
            ->line('**Student Information:**')
            ->line('- Name: ' . $this->studentInfo['name'])
            ->line('- Student ID: #' . str_pad($this->session->student_id, 4, '0', STR_PAD_LEFT));
            
        // Add suggested availability information if available
        if (!empty($this->session->suggested_availability)) {
            $emailMessage->line('**Student\'s Suggested Availability:**');
            
            $availabilityInfo = [];
            if (isset($this->session->suggested_availability['preferences'])) {
                $preferences = $this->session->suggested_availability['preferences'];
                foreach ($preferences as $preference) {
                    $date = $preference['date'] ?? 'Unknown date';
                    $times = $preference['times'] ?? [];
                    if (!empty($times)) {
                        $timeList = implode(', ', array_map(function($time) {
                            return date('g:i A', strtotime($time));
                        }, $times));
                        $availabilityInfo[] = "- " . date('l, F j', strtotime($date)) . ": " . $timeList;
                    }
                }
            } else {
                foreach ($this->session->suggested_availability as $item) {
                    if (isset($item['date']) && isset($item['times'])) {
                        $date = $item['date'];
                        $times = $item['times'];
                        $timeList = implode(', ', array_map(function($time) {
                            return date('g:i A', strtotime($time));
                        }, $times));
                        $availabilityInfo[] = "- " . date('l, F j', strtotime($date)) . ": " . $timeList;
                    }
                }
            }
            
            if (!empty($availabilityInfo)) {
                foreach ($availabilityInfo as $info) {
                    $emailMessage->line($info);
                }
            } else {
                $emailMessage->line('- No specific times provided');
            }
        }
            
        $emailMessage->action('Accept This Session', $url)
            ->line('If you accept, you will be asked to select a time from the student\'s suggested availability.')
            ->line('This link will expire in 7 days. If you accept this session, you will be assigned as the teacher for this student.')
            ->line('Note: If another teacher accepts this session first, you will be notified when you attempt to accept it.')
            ->salutation('Thank you for your contribution to Mindful Chess!');
            
        return $emailMessage;
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
