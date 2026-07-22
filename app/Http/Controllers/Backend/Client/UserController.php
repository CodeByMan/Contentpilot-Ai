<?php

namespace App\Http\Controllers\Backend\Client;

use App\Http\Controllers\Backend\AccountSettingsController;
use App\Services\AccountDeletionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserController extends AccountSettingsController
{
    public function profile(Request $request): View
    {
        $profileData = $request->user()->loadMissing('plan');

        return view('client.user_profile', compact('profileData'));
    }

    public function changePassword(): View
    {
        return view('client.user_change_password');
    }

    public function destroyProfile(Request $request, AccountDeletionService $accounts): RedirectResponse
    {
        $request->validateWithBag('accountDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $accounts->delete($request->user());
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'Your account has been deleted.');
    }
}
