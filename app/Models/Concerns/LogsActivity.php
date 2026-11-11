<?php

namespace App\Models\Concerns;

use App\Models\ActivityLog;

/** Records create/update/delete of admin-managed records in the audit log. */
trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return; // seeders and artisan commands are not user actions
        }

        static::created(fn ($model) => ActivityLog::record('created', $model));
        static::updated(function ($model) {
            $changed = array_keys(array_diff_key($model->getChanges(), array_flip(['updated_at', 'content_updated_at'])));
            if ($changed) {
                ActivityLog::record('updated', $model, null, ['fields' => $changed]);
            }
        });
        static::deleted(fn ($model) => ActivityLog::record('deleted', $model));
    }

    public function activityLabel(): string
    {
        return (string) ($this->title ?? $this->name ?? $this->filename ?? $this->from_path ?? $this->getKey());
    }
}
