<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends AccountSettingsController
{
    public function profile(Request $request): View
    {
        $profileData = $request->user();

        return view('admin.admin_profile', compact('profileData'));
    }

    public function changePassword(): View
    {
        return view('admin.admin_change_password');
    }
}
