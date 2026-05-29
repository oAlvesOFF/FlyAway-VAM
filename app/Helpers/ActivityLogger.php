<?php

namespace App\Helpers;

use App\Models\ActivityLog;

class ActivityLogger
{
    public static function log(
        string $action,
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?string $description = null,
    ): void {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'description' => $description,
        ]);
    }
}
