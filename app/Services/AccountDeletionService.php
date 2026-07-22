<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class AccountDeletionService
{
    public function delete(User $user): void
    {
        /** @var array{public: list<string>, private: list<string>} $paths */
        $paths = DB::transaction(function () use ($user): array {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            $paths = [
                'public' => array_values(array_filter([$lockedUser->photo])),
                'private' => array_values(array_filter([
                    ...$lockedUser->generatedImages()->withTrashed()->pluck('image_path')->all(),
                    ...$lockedUser->generatedAudios()->withTrashed()->pluck('audio_path')->all(),
                ])),
            ];

            $lockedUser->billingHistories()
                ->where('status', PaymentStatus::Pending->value)
                ->update([
                    'status' => PaymentStatus::Rejected->value,
                    'reviewed_at' => now(),
                    'rejection_reason' => 'The payment request was closed because the account was deleted.',
                    'pending_key' => null,
                ]);

            $lockedUser->generatedImages()->withTrashed()->forceDelete();
            $lockedUser->generatedAudios()->withTrashed()->forceDelete();
            $lockedUser->generatedContents()->withTrashed()->forceDelete();
            $lockedUser->chatConversations()->delete();
            $lockedUser->aiUsageReservations()->delete();
            $lockedUser->notifications()->delete();
            DB::table('password_reset_tokens')->where('email', $lockedUser->email)->delete();
            DB::table('sessions')->where('user_id', $lockedUser->id)->delete();

            $lockedUser->forceFill([
                'name' => 'Deleted User',
                'email' => 'deleted+'.Str::uuid().'@example.invalid',
                'password' => Hash::make(Str::password(64)),
                'photo' => null,
                'phone' => null,
                'address' => null,
                'remember_token' => null,
                'plan_id' => null,
                'word_limit' => 0,
                'words_used' => 0,
                'words_reserved' => 0,
                'image_generations_used' => 0,
                'image_generations_reserved' => 0,
                'audio_generations_used' => 0,
                'audio_generations_reserved' => 0,
                'plan_activated_at' => null,
                'usage_reset_at' => null,
                'is_active' => false,
            ])->save();

            $lockedUser->delete();

            return $paths;
        }, 3);

        $this->deleteFiles('public', $paths['public']);
        $this->deleteFiles('private', $paths['private']);
    }

    /** @param list<string> $paths */
    private function deleteFiles(string $disk, array $paths): void
    {
        if ($paths === []) {
            return;
        }

        try {
            Storage::disk($disk)->delete($paths);
        } catch (Throwable $exception) {
            Log::warning('Account file cleanup failed.', [
                'disk' => $disk,
                'exception_class' => $exception::class,
            ]);
        }
    }
}
