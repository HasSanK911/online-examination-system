<?php

namespace App\Models\Concerns;

use DateTimeInterface;

/**
 * Serialize model dates in the configured app timezone (Asia/Karachi) with an
 * explicit offset, instead of Laravel's default UTC "Z" form.
 *
 * This keeps the wall-clock time consistent for the frontend — e.g. an exam
 * scheduled for 10:00 PKT serializes as "...T10:00:00+05:00", so datetime-local
 * inputs prefill and display the same Pakistan time regardless of the browser's
 * own timezone.
 */
trait SerializesDatesInAppTimezone
{
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d\TH:i:sP');
    }
}
