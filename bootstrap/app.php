<?php

use App\Exceptions\AiGenerationException;
use App\Exceptions\QuotaExceededException;
use App\Http\Middleware\EnsureActiveAccount;
use App\Http\Middleware\EnsureAiAccess;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsUser;
use App\Http\Middleware\SecureResponseHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('web', SecureResponseHeaders::class);

        $middleware->alias([
            'active' => EnsureActiveAccount::class,
            'admin' => IsAdmin::class,
            'user' => IsUser::class,
            'ai.access' => EnsureAiAccess::class,
        ]);

        $middleware->validateCsrfTokens(except: []);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontFlash([
            'current_password',
            'password',
            'password_confirmation',
            'old_password',
            'new_password',
            'new_password_confirmation',
            'payment_reference',
        ]);

        $exceptions->render(function (QuotaExceededException $exception, Request $request) {
            $message = $exception->getMessage() ?: 'Your plan quota is exhausted.';

            return $request->expectsJson()
                ? response()->json(['message' => $message], 422)
                : back()->withErrors(['quota' => $message]);
        });

        $exceptions->render(function (AiGenerationException $exception, Request $request) {
            return $request->expectsJson()
                ? response()->json(['message' => $exception->getMessage()], 503)
                : back()->withErrors(['ai' => $exception->getMessage()]);
        });
    })->create();
