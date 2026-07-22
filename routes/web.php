<?php

use App\Http\Controllers\Backend\Admin\AdminDashboardController;
use App\Http\Controllers\Backend\Admin\ChatController;
use App\Http\Controllers\Backend\Admin\DocumentController;
use App\Http\Controllers\Backend\Admin\GenerateController;
use App\Http\Controllers\Backend\Admin\OrderController;
use App\Http\Controllers\Backend\Admin\PlanController;
use App\Http\Controllers\Backend\Admin\TemplateController;
use App\Http\Controllers\Backend\AdminController;
use App\Http\Controllers\Backend\Client\CheckoutController;
use App\Http\Controllers\Backend\Client\ClientDashboardController;
use App\Http\Controllers\Backend\Client\UserController;
use App\Http\Controllers\Backend\Client\UserTemplateController;
use App\Http\Controllers\Frontend\HeadingController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\QuestionsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::controller(HomeController::class)->group(function (): void {
    Route::get('/use-cases', 'useCases')->name('usecase');
    Route::get('/features', 'features')->name('features');
    Route::get('/pricing', 'pricing')->name('pricing');
    Route::get('/contact', 'contact')->name('contact');
    Route::post('/contact', 'storeContact')->middleware('throttle:contact')->name('store.contact');
});

Route::prefix('user')
    ->name('user.')
    ->middleware(['auth', 'verified', 'active', 'user'])
    ->group(function (): void {
        Route::get('/dashboard', ClientDashboardController::class)->name('dashboard');
        Route::post('/logout', [UserController::class, 'logout'])->name('logout');

        Route::get('/profile', [UserController::class, 'profile'])->name('profile');
        Route::patch('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
        Route::delete('/profile', [UserController::class, 'destroyProfile'])
            ->middleware('throttle:5,1')
            ->name('profile.destroy');
        Route::get('/password', [UserController::class, 'changePassword'])->name('password.edit');
        Route::put('/password', [UserController::class, 'updatePassword'])
            ->middleware('throttle:5,1')
            ->name('password.update');

        Route::get('/templates', [UserTemplateController::class, 'index'])->name('templates.index');
        Route::get('/templates/{template}', [UserTemplateController::class, 'show'])->name('templates.show');
        Route::post('/templates/{template}/generate', [UserTemplateController::class, 'generate'])
            ->middleware(['ai.access', 'throttle:ai-content'])
            ->name('content.generate');

        Route::get('/documents', [UserTemplateController::class, 'documents'])->name('documents.index');
        Route::get('/documents/{document}/edit', [UserTemplateController::class, 'editDocument'])->name('documents.edit');
        Route::patch('/documents/{document}', [UserTemplateController::class, 'updateDocument'])->name('documents.update');
        Route::delete('/documents/{document}', [UserTemplateController::class, 'destroyDocument'])->name('documents.destroy');

        Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
        Route::post('/checkout', [CheckoutController::class, 'store'])
            ->middleware('throttle:payment-submission')
            ->name('checkout.store');
        Route::get('/payment-submitted', [CheckoutController::class, 'success'])->name('payment.success');
        Route::get('/invoices/{billing}/download', [CheckoutController::class, 'invoice'])->name('invoices.download');
        Route::post('/billing/notifications/{notification}/read', [CheckoutController::class, 'markNotificationAsRead'])
            ->name('billing.notifications.read');

        Route::get('/images/generate', [GenerateController::class, 'userImageCreate'])->name('images.create');
        Route::post('/images', [GenerateController::class, 'storeImage'])
            ->middleware(['ai.access', 'throttle:ai-media'])
            ->name('images.store');
        Route::get('/images', [GenerateController::class, 'userImageIndex'])->name('images.index');
        Route::get('/images/{image}/file', [GenerateController::class, 'userImageFile'])->name('images.file');
        Route::delete('/images/{image}', [GenerateController::class, 'userImageDestroy'])->name('images.destroy');

        Route::get('/audio/generate', [GenerateController::class, 'userAudioCreate'])->name('audio.create');
        Route::post('/audio', [GenerateController::class, 'storeAudio'])
            ->middleware(['ai.access', 'throttle:ai-media'])
            ->name('audio.store');
        Route::get('/audio', [GenerateController::class, 'userAudioIndex'])->name('audio.index');
        Route::get('/audio/{audio}/file', [GenerateController::class, 'userAudioFile'])->name('audio.file');
        Route::delete('/audio/{audio}', [GenerateController::class, 'userAudioDestroy'])->name('audio.destroy');
    });

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', 'active', 'admin'])
    ->group(function (): void {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
        Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
        Route::get('/profile', [AdminController::class, 'profile'])->name('profile');
        Route::patch('/profile', [AdminController::class, 'updateProfile'])->name('profile.update');
        Route::get('/password', [AdminController::class, 'changePassword'])->name('password.edit');
        Route::put('/password', [AdminController::class, 'updatePassword'])
            ->middleware('throttle:5,1')
            ->name('password.update');

        Route::get('/plans', [PlanController::class, 'index'])->name('plans.index');
        Route::get('/plans/create', [PlanController::class, 'create'])->name('plans.create');
        Route::post('/plans', [PlanController::class, 'store'])->name('plans.store');
        Route::get('/plans/{plan}/edit', [PlanController::class, 'edit'])->name('plans.edit');
        Route::put('/plans/{plan}', [PlanController::class, 'update'])->name('plans.update');
        Route::delete('/plans/{plan}', [PlanController::class, 'destroy'])->name('plans.destroy');

        Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
        Route::get('/templates/create', [TemplateController::class, 'create'])->name('templates.create');
        Route::post('/templates', [TemplateController::class, 'store'])->name('templates.store');
        Route::get('/templates/{template}/edit', [TemplateController::class, 'edit'])->name('templates.edit');
        Route::put('/templates/{template}', [TemplateController::class, 'update'])->name('templates.update');
        Route::delete('/templates/{template}', [TemplateController::class, 'destroy'])->name('templates.destroy');
        Route::get('/templates/{template}', [TemplateController::class, 'show'])->name('templates.show');
        Route::post('/templates/{template}/generate', [TemplateController::class, 'generate'])
            ->middleware(['ai.access', 'throttle:ai-content'])
            ->name('content.generate');

        Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
        Route::get('/documents/{document}/edit', [DocumentController::class, 'edit'])->name('documents.edit');
        Route::patch('/documents/{document}', [DocumentController::class, 'update'])->name('documents.update');
        Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

        Route::get('/payments', [OrderController::class, 'index'])->name('payments.index');
        Route::patch('/payments/{payment}', [OrderController::class, 'review'])->name('payments.review');
        Route::get('/payments/{payment}/receipt', [OrderController::class, 'receipt'])->name('payments.receipt');

        Route::get('/assistants', [ChatController::class, 'index'])->name('assistants.index');
        Route::get('/assistants/create', [ChatController::class, 'create'])->name('assistants.create');
        Route::post('/assistants', [ChatController::class, 'store'])->name('assistants.store');
        Route::get('/assistants/{assistant}/edit', [ChatController::class, 'edit'])->name('assistants.edit');
        Route::put('/assistants/{assistant}', [ChatController::class, 'update'])->name('assistants.update');
        Route::delete('/assistants/{assistant}', [ChatController::class, 'destroy'])->name('assistants.destroy');
        Route::get('/assistants/{assistant}/chat', [ChatController::class, 'chat'])->name('assistants.chat');
        Route::post('/assistants/{assistant}/messages', [ChatController::class, 'sendMessage'])
            ->middleware(['ai.access', 'throttle:ai-chat'])
            ->name('assistants.messages.store');
        Route::post('/assistants/{assistant}/conversations', [ChatController::class, 'startConversation'])->name('assistants.conversations.store');
        Route::get('/assistants/{assistant}/conversations/{conversationUuid}', [ChatController::class, 'selectConversation'])
            ->whereUuid('conversationUuid')
            ->name('assistants.conversations.show');

        Route::middleware('can:manage-cms')->group(function (): void {
            Route::get('/cms/slider', [HomeController::class, 'editSlider'])->name('cms.slider.edit');
            Route::put('/cms/slider/{slider}', [HomeController::class, 'updateSlider'])->name('cms.slider.update');

            Route::get('/cms/headings', [HeadingController::class, 'index'])->name('cms.headings.index');
            Route::get('/cms/headings/create', [HeadingController::class, 'create'])->name('cms.headings.create');
            Route::post('/cms/headings', [HeadingController::class, 'store'])->name('cms.headings.store');
            Route::get('/cms/headings/{heading}/edit', [HeadingController::class, 'edit'])->name('cms.headings.edit');
            Route::put('/cms/headings/{heading}', [HeadingController::class, 'update'])->name('cms.headings.update');
            Route::delete('/cms/headings/{heading}', [HeadingController::class, 'destroy'])->name('cms.headings.destroy');

            Route::get('/cms/questions', [QuestionsController::class, 'index'])->name('cms.questions.index');
            Route::get('/cms/questions/create', [QuestionsController::class, 'create'])->name('cms.questions.create');
            Route::post('/cms/questions', [QuestionsController::class, 'store'])->name('cms.questions.store');
            Route::get('/cms/questions/{question}/edit', [QuestionsController::class, 'edit'])->name('cms.questions.edit');
            Route::put('/cms/questions/{question}', [QuestionsController::class, 'update'])->name('cms.questions.update');
            Route::delete('/cms/questions/{question}', [QuestionsController::class, 'destroy'])->name('cms.questions.destroy');

            Route::get('/contacts', [HomeController::class, 'contacts'])->name('contacts.index');
            Route::delete('/contacts/{contact}', [HomeController::class, 'destroyContact'])->name('contacts.destroy');
        });

        Route::get('/images/generate', [GenerateController::class, 'adminImageCreate'])->name('images.create');
        Route::post('/images', [GenerateController::class, 'storeImage'])
            ->middleware(['ai.access', 'throttle:ai-media'])
            ->name('images.store');
        Route::get('/images', [GenerateController::class, 'adminImageIndex'])->name('images.index');
        Route::get('/images/{image}/file', [GenerateController::class, 'adminImageFile'])->name('images.file');
        Route::delete('/images/{image}', [GenerateController::class, 'adminImageDestroy'])->name('images.destroy');

        Route::get('/audio/generate', [GenerateController::class, 'adminAudioCreate'])->name('audio.create');
        Route::post('/audio', [GenerateController::class, 'storeAudio'])
            ->middleware(['ai.access', 'throttle:ai-media'])
            ->name('audio.store');
        Route::get('/audio', [GenerateController::class, 'adminAudioIndex'])->name('audio.index');
        Route::get('/audio/{audio}/file', [GenerateController::class, 'adminAudioFile'])->name('audio.file');
        Route::delete('/audio/{audio}', [GenerateController::class, 'adminAudioDestroy'])->name('audio.destroy');
    });

require __DIR__.'/auth.php';
