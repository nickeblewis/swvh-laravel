<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * The hall's real starting content: the 19 regular activities and the 5
 * hireable spaces, from the Nuxt site's seed migration (`data/hall.sql`).
 * Idempotent: a row whose slug already exists is left alone, so re-running
 * never overwrites edits made in the admin.
 */
class HallSeeder extends Seeder
{
    public function run(): void
    {
        $statements = explode('--> statement-breakpoint', File::get(__DIR__.'/data/hall.sql'));

        // A checked-in SQL file, run as is (like `schema:load`), not built from input.
        foreach ($statements as $statement) {
            DB::connection()->getPdo()->exec($statement);
        }

        foreach (['activities', 'spaces'] as $table) {
            DB::table($table)->whereNull('created_at')->update(['created_at' => now(), 'updated_at' => now()]);
        }
    }
}
