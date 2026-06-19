<?php

namespace App\Notifications;

use App\Models\Exam;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExamScheduledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Exam $exam) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Exam Scheduled: {$this->exam->title}")
            ->greeting("Hello {$notifiable->name},")
            ->line("An exam has been scheduled for you.")
            ->line("**Exam:** {$this->exam->title}")
            ->line("**Course:** {$this->exam->course?->title}")
            ->line("**Starts:** " . $this->exam->start_time->format('D, d M Y h:i A'))
            ->line("**Duration:** {$this->exam->duration_minutes} minutes")
            ->line("**Total Marks:** {$this->exam->total_marks}")
            ->action('View Exam', url('/student/exams'))
            ->line('Please ensure you are ready before the exam starts.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'     => 'exam_scheduled',
            'exam_id'  => $this->exam->id,
            'title'    => $this->exam->title,
            'start'    => $this->exam->start_time,
            'message'  => "Exam '{$this->exam->title}' has been scheduled.",
        ];
    }
}
