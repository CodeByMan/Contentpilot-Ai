<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAccountProfileRequest;
use App\Services\Storage\SecureUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Throwable;

abstract class AccountSettingsController extends Controller
{
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'You have been signed out.');
    }

    public function updateProfile(
        UpdateAccountProfileRequest $request,
        SecureUploadService $uploads,
    ): RedirectResponse {
        $user = $request->user();
        $data = $request->safe()->except(['photo', 'current_password']);
        $oldPhoto = $user->photo;
        $newPhoto = null;

        if ($request->hasFile('photo')) {
            $newPhoto = $uploads->storeImage(
                $request->file('photo'),
                'profiles',
                512,
                512,
                maxBytes: 2_097_152,
            );
            $data['photo'] = $newPhoto;
        }

        $emailChanged = isset($data['email']) && $data['email'] !== $user->email;

        try {
            $user->fill($data);

            if ($emailChanged) {
                $user->email_verified_at = null;
            }

            $user->save();
        } catch (Throwable $exception) {
            $uploads->delete($newPhoto);

            throw $exception;
        }

        if ($newPhoto) {
            $uploads->delete($oldPhoto);
        }

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        return back()->with([
            'message' => 'Profile updated successfully.',
            'alert-type' => 'success',
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'old_password' => ['required', 'current_password'],
            'new_password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update(['password' => $validated['new_password']]);
        Auth::logoutOtherDevices($validated['new_password']);
        $request->session()->regenerate();

        return back()->with([
            'message' => 'Password updated successfully.',
            'alert-type' => 'success',
        ]);
    }
}
