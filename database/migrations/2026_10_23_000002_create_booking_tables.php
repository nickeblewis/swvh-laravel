<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The booking model carried over from the Nuxt and Rails sites. The public
 * site still books through Hall Master; these are edited in the admin only.
 *
 * The Nuxt `users` table (hirers) is `hirers` here, because `users` is the
 * admin login table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hirers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->timestamp('approved_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('status')->default('requested')->index();
            $table->text('notes')->nullable();
            $table->foreignId('hirer_id')->constrained();
            $table->timestamps();

            $table->index(['starts_at', 'ends_at']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE bookings ADD CONSTRAINT bookings_ends_after_starts CHECK (ends_at > starts_at)');
        }

        Schema::create('booking_space', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('space_id')->constrained();
            $table->timestamps();

            $table->unique(['booking_id', 'space_id']);
        });

        // Money is integer pence plus an ISO currency code. Never floats.
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained()->cascadeOnDelete();
            $table->integer('amount_pence');
            $table->string('currency', 3)->default('GBP');
            $table->string('status')->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->string('provider')->nullable();
            $table->string('provider_reference')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('booking_space');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('hirers');
    }
};
