<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'action',
        'details',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $action, string $details = ''): self
    {
        return static::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'details' => Auth::check() ? $details : '[Unauthenticated Session] ' . $details,
        ]);
    }
}
