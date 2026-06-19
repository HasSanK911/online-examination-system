<?php

namespace App\Notifications;

use App\Models\Result;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResultPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Result $result) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $exam = $this->result->exam;

        return (new MailMessage)
            ->subject("Result Published: {$exam?->title}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your result has been published.")
            ->line("**Exam:** {$exam?->title}")
            ->line("**Marks:** {$this->result->obtained_marks} / {$this->result->total_marks}")
            ->line("**Grade:** {$this->result->grade}  |  **GPA:** {$this->result->gpa}")
            ->line($this->result->is_pass ? 'Congratulations! You passed.' : 'Unfortunately, you did not pass.')
            ->action('View Result', url("/student/results/{$this->result->id}"))
            ->line('Download your result card from the student portal.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'      => 'result_published',
            'result_id' => $this->result->id,
            'exam_id'   => $this->result->exam_id,
            'grade'     => $this->result->grade,
            'is_pass'   => $this->result->is_pass,
            'message'   => "Your result for '{$this->result->exam?->title}' has been published. Grade: {$this->result->grade}",
        ];
    }
}
