<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentRateRejectionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected User $student;
    protected User $teacher;
    protected array $rejectedRates;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $student, User $teacher, array $rejectedRates)
    {
        $this->student = $student;
        $this->teacher = $teacher;
        $this->rejectedRates = $rejectedRates;
    }

    /**
     * Get the notification's delivery channels.
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
        $studentName = $this->student->name;
        $teacherName = $this->teacher->name;
        
        // Build rejected rates text
        $rejectedRatesText = "";
        foreach ($this->rejectedRates as $duration => $rateInfo) {
            $rejectedRatesText .= "• {$rateInfo['name']}: £" . number_format($rateInfo['price'] / 100, 2) . "\n";
        }
        
        return (new MailMessage)
            ->subject('Action Required: Student Rejected Rate Increase')
            ->greeting('Hello Admin,')
            ->line("**Student Rate Rejection Alert**")
            ->line("A student has rejected the premium rate increase and is requesting a new teacher assignment.")
            ->line("**Student Details:**")
            ->line("• Name: {$studentName}")
            ->line("• Email: {$this->student->email}")
            ->line("**Current Teacher:**")
            ->line("• Name: {$teacherName}")
            ->line("• Email: {$this->teacher->email}")
            ->line("**Rejected Premium Rates:**")
            ->line($rejectedRatesText)
            ->line("**Required Actions:**")
            ->line("1. Contact the student within 2 business days")
            ->line("2. Find an alternative teacher at standard rates")
            ->line("3. Coordinate the teacher transition")
            ->line("4. Update the student's teacher assignment")
            ->action('View Student Profile', route('admin.students.show', $this->student->id))
            ->line("You can also view the teacher profile for reference:")
            ->action('View Teacher Profile', route('admin.teachers.show', $this->teacher->id))
            ->line("Please prioritize this request to ensure continued excellent service for our students.")
            ->salutation("Best regards,\nThe " . config('app.name') . " System");
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'student_id' => $this->student->id,
            'teacher_id' => $this->teacher->id,
            'student_name' => $this->student->name,
            'teacher_name' => $this->teacher->name,
            'rejected_rates' => $this->rejectedRates,
        ];
    }
}
