<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * The publishing and slug rules shared by activities and pages.
 *
 *   published -> draft = false AND (published_at IS NULL OR published_at <= now)
 *   ordered   -> position ASC, title ASC
 *
 * A blank slug is made from the title when the record is created, and is not
 * changed when the title changes later.
 *
 * @mixin Model
 */
trait Publishable
{
    public static function bootPublishable(): void
    {
        static::creating(function (Model $model): void {
            if (blank($model->getAttribute('slug'))) {
                $model->setAttribute('slug', Str::slug((string) $model->getAttribute('title')));
            }
        });
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('draft', false)
            ->where(fn (Builder $query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->orderBy('position')
            ->orderBy('title');
    }

    public function isPublished(): bool
    {
        return ! $this->getAttribute('draft')
            && ($this->getAttribute('published_at') === null || $this->getAttribute('published_at') <= now());
    }
}
