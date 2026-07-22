<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\BillingHistory;
use App\Models\GeneratedAudio;
use App\Models\GeneratedContent;
use App\Models\GeneratedImage;
use App\Models\Template;
use App\Models\User;
use App\Policies\BillingHistoryPolicy;
use App\Policies\GeneratedAudioPolicy;
use App\Policies\GeneratedContentPolicy;
use App\Policies\GeneratedImagePolicy;
use App\Policies\TemplatePolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Application services use constructor injection and require no manual bindings.
    }

    public function boot(): void
    {
        Model::preventSilentlyDiscardingAttributes($this->app->isLocal() || $this->app->runningUnitTests());
        Model::preventLazyLoading($this->app->isLocal() || $this->app->runningUnitTests());

        Paginator::useBootstrapFive();

        Password::defaults(function (): Password {
            $rule = Password::min(12)->mixedCase()->numbers()->symbols();

            return $this->app->isProduction() ? $rule->uncompromised() : $rule;
        });

        Gate::policy(GeneratedContent::class, GeneratedContentPolicy::class);
        Gate::policy(BillingHistory::class, BillingHistoryPolicy::class);
        Gate::policy(GeneratedImage::class, GeneratedImagePolicy::class);
        Gate::policy(GeneratedAudio::class, GeneratedAudioPolicy::class);
        Gate::policy(Template::class, TemplatePolicy::class);

        Gate::before(fn (User $user): ?bool => $user->role === UserRole::Admin && $user->is_active ? true : null);
        Gate::define('manage-cms', fn (User $user): bool => $user->role === UserRole::Admin && $user->is_active);
        Gate::define('review-payments', fn (User $user): bool => $user->role === UserRole::Admin && $user->is_active);

        foreach (['assistant', 'audio', 'billing', 'contact', 'document', 'heading', 'image', 'payment', 'plan', 'question', 'template'] as $parameter) {
            Route::pattern($parameter, '[0-9]+');
        }

        RateLimiter::for('ai-content', fn (Request $request): Limit => Limit::perMinute(
            max(1, (int) config('ai.rate_limits.content_per_minute', 6)),
        )->by('content:'.($request->user()?->id ?? $request->ip())));

        RateLimiter::for('ai-media', fn (Request $request): Limit => Limit::perMinute(
            max(1, (int) config('ai.rate_limits.media_per_minute', 3)),
        )->by('media:'.($request->user()?->id ?? $request->ip())));

        RateLimiter::for('ai-chat', fn (Request $request): Limit => Limit::perMinute(
            max(1, (int) config('ai.rate_limits.chat_per_minute', 8)),
        )->by('chat:'.($request->user()?->id ?? $request->ip())));

        RateLimiter::for('contact', fn (Request $request): Limit => Limit::perMinute(5)
            ->by('contact:'.$request->ip()));

        RateLimiter::for('payment-submission', fn (Request $request): Limit => Limit::perHour(3)
            ->by('payment:'.($request->user()?->id ?? $request->ip())));
    }
}
