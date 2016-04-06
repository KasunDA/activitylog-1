<?php

namespace CodeMyViews\Activitylog\Handlers;

use CodeMyViews\Activitylog\Models\Activity;
use Carbon\Carbon;

class EloquentHandler implements ActivitylogHandlerInterface
{
    /**
     * Log activity in an Eloquent model.
     *
     * @param string $text
     * @param $userId
     * @param array  $attributes
     *
     * @return bool
     */
    public function log($text, $userId = '', $attributes = [])
    {
        $activity = new Activity([
            'text' => $text,
            'user_id' => ($userId == '' ? null : $userId),
            'ip_address' => $attributes['ipAddress'],
        ]);

        if(!is_null($attributes['reference'])) {
            $activity->reference_id = $attributes['reference']->id;
            $activity->reference = get_class($attributes['reference']);
        }

        $activity->save();

        return true;
    }

    /**
     * Clean old log records.
     *
     * @param int $maxAgeInMonths
     *
     * @return bool
     */
    public function cleanLog($maxAgeInMonths)
    {
        $minimumDate = Carbon::now()->subMonths($maxAgeInMonths);
        Activity::where('created_at', '<=', $minimumDate)->delete();

        return true;
    }
}
