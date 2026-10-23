<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The database-backed content: regular activities, admin-created pages and
 * the hireable spaces. Table-for-table with the Nuxt site's `db/schema.ts`,
 * except that an uploaded hero image is a path on the `public` disk
 * (`hero_image`) rather than a Netlify Blobs key. `hero_image_path` stays for
 * the images shipped in `public/images`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('body');
            $table->text('description')->nullable();
            // Free text, e.g. "Monday, Thursday" / "1st Sunday of month" and
            // "7:30 PM - 9:00 PM". See App\Support\Schedule.
            $table->string('day')->nullable();
            $table->string('time')->nullable();
            $table->string('price')->nullable();
            $table->string('age_range')->nullable();
            $table->string('contact')->nullable();
            $table->string('phone')->nullable();
            $table->text('external_url')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('hero_image_path')->nullable();
            $table->string('hero_image_alt')->nullable();
            $table->text('source_url')->nullable();
            $table->date('source_updated_on')->nullable();
            $table->boolean('draft')->default(false);
            $table->integer('position')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['draft', 'position']);
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('body');
            $table->string('template')->default('standard');
            $table->text('description')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('hero_image_path')->nullable();
            $table->string('hero_image_alt')->nullable();
            $table->text('source_url')->nullable();
            $table->boolean('draft')->default(false);
            $table->integer('position')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['draft', 'position']);
        });

        Schema::create('spaces', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('bookable')->default(true);
            $table->integer('position')->default(0);
            $table->integer('capacity')->nullable();
            $table->text('description')->nullable();
            $table->string('dimensions')->nullable();
            $table->timestamps();

            $table->index(['bookable', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spaces');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('activities');
    }
};
