<?php

namespace App\Actions\CRM;

use App\Enums\ActivityType;
use App\Models\CRM\Activity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class LogActivity
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(User $user, Model $subject, array $attributes): Activity
    {
        return Activity::create([
            'account_id' => $user->current_account_id,
            'user_id' => $user->id,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'type' => $attributes['type'] ?? ActivityType::Note,
            'title' => $attributes['title'],
            'body' => $attributes['body'] ?? null,
            'occurred_at' => $attributes['occurred_at'] ?? now(),
        ]);
    }
}
