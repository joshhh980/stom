<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use App\Enums\Status;

trait HasActiveStatus
{

    public function initializeHasActiveStatus(): void
    {
        $this->casts['status'] = Status::class;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status',  Status::ACTIVE)->orderBy('name', 'asc');
    }
}
