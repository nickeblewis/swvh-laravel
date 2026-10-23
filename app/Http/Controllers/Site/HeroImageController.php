<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

/**
 * The Nuxt site served uploads at `/images/uploads/<key>`. Imported uploads
 * keep their key under `heroes/`, so old links still work.
 */
class HeroImageController extends Controller
{
    public function __invoke(string $key): RedirectResponse
    {
        abort_unless(Storage::disk('public')->exists("heroes/{$key}"), 404);

        return redirect(Storage::disk('public')->url("heroes/{$key}"), 301);
    }
}
