<?php

namespace App\Notifications;

use App\Models\ChessSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
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
            ->line('- Name: ' . $this->studentInfo['name']);
        // Add suggested availability information if available
        if (!empty($this->session->suggested_availability)) {
            $emailMessage->line('**Student\'s Suggested Availability:**');
            
            $availabilityInfo = [];
            $rawAvailability = $this->session->suggested_availability;
            
            // Convert availability ranges to specific time slots for display
            if (is_array($rawAvailability)) {
                // First, merge overlapping time ranges for the same date
                $mergedAvailability = $this->mergeOverlappingTimeRanges($rawAvailability);
                
                foreach ($mergedAvailability as $item) {
                    if (isset($item['date']) && isset($item['time_from']) && isset($item['time_to'])) {
                        $date = $item['date'];
                        $timeSlots = $this->generateTimeSlots(
                            $item['time_from'], 
                            $item['time_to'], 
                            $this->session->duration
                        );
                        
                        if (!empty($timeSlots)) {
                            $timeList = implode(', ', array_map(function($time) {
                                return date('g:i A', strtotime($time));
                            }, $timeSlots));
                            $availabilityInfo[] = "- " . date('l, F j', strtotime($date)) . ": " . $timeList;
                        }
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
    
    /**
     * Merge overlapping time ranges for the same date
     */
    private function mergeOverlappingTimeRanges($availability)
    {
        if (!is_array($availability)) {
            return $availability;
        }
        
        // Group by date
        $groupedByDate = [];
        foreach ($availability as $item) {
            if (isset($item['date']) && isset($item['time_from']) && isset($item['time_to'])) {
                $date = $item['date'];
                if (!isset($groupedByDate[$date])) {
                    $groupedByDate[$date] = [];
                }
                $groupedByDate[$date][] = [
                    'time_from' => $item['time_from'],
                    'time_to' => $item['time_to'],
                    'label' => $item['label'] ?? 'Time Range'
                ];
            }
        }
        
        $mergedAvailability = [];
        
        foreach ($groupedByDate as $date => $timeRanges) {
            // Sort time ranges by start time
            usort($timeRanges, function($a, $b) {
                return strcmp($a['time_from'], $b['time_from']);
            });
            
            $merged = [];
            $current = null;
            
            foreach ($timeRanges as $range) {
                if ($current === null) {
                    $current = $range;
                } else {
                    // Check if ranges overlap or are adjacent
                    if ($this->timeRangesOverlap($current['time_from'], $current['time_to'], $range['time_from'], $range['time_to'])) {
                        // Merge ranges
                        $current['time_to'] = max($current['time_to'], $range['time_to']);
                        $current['label'] = $this->combineLables($current['label'], $range['label']);
                    } else {
                        // No overlap, add current to merged and start new
                        $merged[] = $current;
                        $current = $range;
                    }
                }
            }
            
            // Add the last range
            if ($current !== null) {
                $merged[] = $current;
            }
            
            // Add merged ranges to final result
            foreach ($merged as $mergedRange) {
                $mergedAvailability[] = [
                    'date' => $date,
                    'time_from' => $mergedRange['time_from'],
                    'time_to' => $mergedRange['time_to'],
                    'label' => $mergedRange['label']
                ];
            }
        }
        
        return $mergedAvailability;
    }
    
    /**
     * Check if two time ranges overlap or are adjacent
     */
    private function timeRangesOverlap($start1, $end1, $start2, $end2)
    {
        try {
            // Helper function to parse time in either H:i or H:i:s format
            $parseTime = function($time) {
                // First try H:i:s format, then H:i format
                try {
                    return \Carbon\Carbon::createFromFormat('H:i:s', $time);
                } catch (\Exception $e) {
                    return \Carbon\Carbon::createFromFormat('H:i', $time);
                }
            };
            
            $s1 = $parseTime($start1);
            $e1 = $parseTime($end1);
            $s2 = $parseTime($start2);
            $e2 = $parseTime($end2);
            
            // Check if ranges overlap or are adjacent (touching)
            return $s1->lte($e2) && $s2->lte($e1);
        } catch (\Exception $e) {
            Log::error('Error checking time range overlap in notification', [
                'start1' => $start1, 'end1' => $end1,
                'start2' => $start2, 'end2' => $end2,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Combine labels from merged time ranges
     */
    private function combineLables($label1, $label2)
    {
        if ($label1 === $label2) {
            return $label1;
        }
        
        // Create a combined label
        $labels = array_unique([$label1, $label2]);
        return implode(' + ', $labels);
    }
    
    /**
     * Generate specific time slots from a time range based on session duration
     */
    private function generateTimeSlots($timeFrom, $timeTo, $duration)
    {
        $slots = [];
        
        try {
            $startTime = \Carbon\Carbon::createFromFormat('H:i', $timeFrom);
            $endTime = \Carbon\Carbon::createFromFormat('H:i', $timeTo);
            
            // Generate slots with the session duration
            $currentTime = $startTime->copy();
            
            while ($currentTime->copy()->addMinutes($duration)->lte($endTime)) {
                $slots[] = $currentTime->format('H:i');
                
                // Move to next slot (every 30 minutes for flexibility)
                $currentTime->addMinutes(30);
            }
            
        } catch (\Exception $e) {
            // Log error and return empty array
            Log::error('Error generating time slots in notification', [
                'time_from' => $timeFrom,
                'time_to' => $timeTo,
                'duration' => $duration,
                'error' => $e->getMessage()
            ]);
        }
        
        return $slots;
    }
}
