<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'lowercase', 'email:rfc', 'max:180', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($validated): User {
            $plan = Plan::query()
                ->where('is_active', true)
                ->where('price', 0)
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            $user = new User($validated);
            $user->forceFill([
                'role' => UserRole::User,
                'plan_id' => $plan?->id,
                'word_limit' => $plan?->monthly_word_limit ?? 0,
                'usage_reset_at' => $plan ? now()->addMonthNoOverflow() : null,
            ])->save();

            return $user;
        }, 3);

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
