<?php

namespace App\Notifications;

use App\Models\Exam;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExamReminderNotification extends Notification implements ShouldQueue
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
            ->subject("Reminder: {$this->exam->title} starts tomorrow")
            ->greeting("Hello {$notifiable->name},")
            ->line("This is a reminder that your exam is tomorrow.")
            ->line("**Exam:** {$this->exam->title}")
            ->line("**Starts:** " . $this->exam->start_time->format('D, d M Y h:i A'))
            ->line("**Duration:** {$this->exam->duration_minutes} minutes")
            ->action('Prepare Now', url('/student/exams'))
            ->line('Good luck!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'exam_reminder',
            'exam_id' => $this->exam->id,
            'title'   => $this->exam->title,
            'start'   => $this->exam->start_time,
            'message' => "Reminder: '{$this->exam->title}' starts tomorrow.",
        ];
    }
}
