<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
    protected $fillable = [
        'topic_id',
        'user_id',
        'correct_count',
        'wrong_count',
    ];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
