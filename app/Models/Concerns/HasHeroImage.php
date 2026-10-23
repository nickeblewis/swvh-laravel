<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * A hero image is either an upload on the `public` disk (`hero_image`, set by
 * the admin) or a file shipped in `public/images` (`hero_image_path`, from
 * the seed data). An upload wins. Replacing, removing or deleting removes the
 * old upload.
 *
 * @mixin Model
 */
trait HasHeroImage
{
    public static function bootHasHeroImage(): void
    {
        static::updated(function (Model $model): void {
            $previous = $model->getOriginal('hero_image');

            if ($model->wasChanged('hero_image') && filled($previous)) {
                Storage::disk('public')->delete($previous);
            }
        });

        static::deleted(function (Model $model): void {
            if (filled($model->getAttribute('hero_image'))) {
                Storage::disk('public')->delete($model->getAttribute('hero_image'));
            }
        });
    }

    /** The URL the site shows, or null when there is no image. */
    public function heroImageUrl(): ?string
    {
        $upload = $this->getAttribute('hero_image');

        if (filled($upload)) {
            return Storage::disk('public')->url($upload);
        }

        return $this->getAttribute('hero_image_path') ?: null;
    }
}
