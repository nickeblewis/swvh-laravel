<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use function Laravel\Prompts\password;

/**
 * Create an admin login, or reset an existing one's password. The password
 * is asked for, never passed as an argument, so it stays out of shell history.
 */
#[Signature('swvh:admin {email} {--name= : Display name (defaults to the start of the email)}')]
#[Description('Create an admin user, or reset their password')]
class CreateAdmin extends Command
{
    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));

        if (Validator::make(['email' => $email], ['email' => 'required|email'])->fails()) {
            $this->error('That is not a valid email address.');

            return self::FAILURE;
        }

        $password = password(
            label: "Password for {$email}",
            required: true,
            validate: fn (string $value): ?string => strlen($value) < 12 ? 'At least 12 characters.' : null,
        );

        $user = User::firstOrNew(['email' => $email]);
        $existed = $user->exists;
        $user->name = $this->option('name') ?: ($user->name ?? Str::headline(Str::before($email, '@')));
        $user->password = $password;
        $user->email_verified_at ??= now();
        $user->save();

        $this->info($existed ? "Password reset for {$email}." : "Admin user {$email} created.");

        return self::SUCCESS;
    }
}
