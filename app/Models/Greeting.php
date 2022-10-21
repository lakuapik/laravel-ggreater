<?php

namespace App\Models;

use App\Enums\GreetingType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Greeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'type', 'message', 'for_date',
        'available_at', 'sending_at', 'sent_at', 'metadata',
    ];

    protected $casts = [
        'type' => GreetingType::class,
        'for_date' => 'date',
        'available_at' => 'datetime',
        'sending_at' => 'datetime',
        'sent_at' => 'datetime',
        'metadata' => 'json',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('sent_at');
    }

    public function scopeReady(Builder $query): Builder
    {
        return $query->whereNull('sending_at')->whereNull('sent_at');
    }

    public function scopeSending(Builder $query): Builder
    {
        return $query->whereNotNull('sending_at')->whereNull('sent_at');
    }

    public function scopeSent(Builder $query): Builder
    {
        return $query->whereNotNull('sending_at')->whereNotNull('sent_at');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
