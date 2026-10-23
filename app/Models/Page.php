<?php

namespace App\Models;

use App\Enums\PageTemplate;
use App\Models\Concerns\HasHeroImage;
use App\Models\Concerns\Publishable;
use Carbon\CarbonImmutable;
use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * An admin-created page, served at `/{slug}` by the catch-all route. The
 * built-in pages have their own routes, and their slugs are reserved
 * (`site.reserved_slugs`).
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $body
 * @property PageTemplate $template
 * @property string|null $description
 * @property string|null $hero_image
 * @property string|null $hero_image_path
 * @property string|null $hero_image_alt
 * @property string|null $source_url
 * @property bool $draft
 * @property int $position
 * @property CarbonImmutable|null $published_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Guarded(['id'])]
class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory, HasHeroImage, Publishable;

    protected $attributes = [
        'template' => 'standard',
    ];

    protected function casts(): array
    {
        return [
            'template' => PageTemplate::class,
            'draft' => 'boolean',
            'position' => 'integer',
            'published_at' => 'datetime',
        ];
    }
}
