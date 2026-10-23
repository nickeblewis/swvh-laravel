<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * The photos in a folder under `public/images`, e.g. Gallery::from('repair-cafe').
 * Drop files in to add them; they're shown in filename order, and alt text
 * comes from the filename ("03-sewing-repairs.jpg" => "Sewing repairs").
 */
final class Gallery
{
    /** @return list<array{path: string, alt: string}> */
    public static function from(string $folder): array
    {
        $directory = public_path("images/{$folder}");

        if (! File::isDirectory($directory)) {
            return [];
        }

        $files = array_values(array_filter(
            array_map(fn (\SplFileInfo $file): string => $file->getFilename(), File::files($directory)),
            fn (string $file): bool => (bool) preg_match('/\.(?:jpe?g|png|webp|gif)$/i', $file),
        ));
        sort($files);

        return array_map(fn (string $file): array => [
            'path' => "/images/{$folder}/{$file}",
            'alt' => Str::ucfirst(Str::lower(trim((string) preg_replace(
                ['/\.[^.]+$/', '/^\d+[-_ ]*/', '/[-_]/', '/\s+/'],
                ['', '', ' ', ' '],
                $file,
            )))),
        ], $files);
    }
}
