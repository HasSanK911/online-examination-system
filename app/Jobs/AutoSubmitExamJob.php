<?php

namespace App\Jobs;

use App\Models\ExamAttempt;
use App\Services\ExamAttemptService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AutoSubmitExamJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly int $attemptId) {}

    public function handle(ExamAttemptService $service): void
    {
        $attempt = ExamAttempt::find($this->attemptId);

        if (! $attempt || $attempt->status !== 'in_progress') {
            return;
        }

        $service->submitAttempt($attempt, true);
    }
}
