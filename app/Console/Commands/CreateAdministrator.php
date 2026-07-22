<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdministrator extends Command
{
    protected $signature = 'contentpilot:admin:create
        {--name= : Administrator display name}
        {--email= : Administrator email address}';

    protected $description = 'Create a verified administrator without exposing a password in shell history.';

    public function handle(): int
    {
        $name = trim((string) ($this->option('name') ?: $this->ask('Administrator name')));
        $email = mb_strtolower(trim((string) ($this->option('email') ?: $this->ask('Administrator email'))));
        $password = (string) $this->secret('Password');
        $passwordConfirmation = (string) $this->secret('Confirm password');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'lowercase', 'email:rfc', 'max:180', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $administrator = DB::transaction(function () use ($name, $email, $password): User {
            $administrator = new User;
            $administrator->forceFill([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'email_verified_at' => now(),
                'role' => UserRole::Admin,
                'is_active' => true,
                'plan_id' => null,
                'word_limit' => 0,
                'words_used' => 0,
                'words_reserved' => 0,
                'image_generations_used' => 0,
                'image_generations_reserved' => 0,
                'audio_generations_used' => 0,
                'audio_generations_reserved' => 0,
            ]);
            $administrator->save();

            return $administrator;
        });

        $this->info("Administrator created for {$administrator->email}.");

        return self::SUCCESS;
    }
}
