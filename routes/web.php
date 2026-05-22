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
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsUser;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home.index'))->name('home');

Route::middleware(['auth', IsUser::class])->group(function () {
    Route::get('/dashboard', ClientDashboardController::class)->name('dashboard');
    Route::post('/user/logout', [UserController::class, 'UserLogout'])->name('user.logout');

    Route::get('/user/profile', [UserController::class, 'UserProfile'])->name('user.profile');
    Route::post('/user/profile/store', [UserController::class, 'UserProfileStore'])->name('user.profile.store');
    Route::get('/user/change/password', [UserController::class, 'UserChangePassword'])->name('user.change.password');
    Route::post('/user/password/update', [UserController::class, 'UserPasswordUpdate'])->name('user.password.update');

    Route::controller(UserTemplateController::class)->group(function () {
        Route::get('/user/template', 'UserTemplate')->name('user.template');
        Route::get('/user/details/template/{id}', 'UserDetailsTemplate')->name('user.details.template');
        Route::post('/user/content/generate/{id}', 'UserContentGenerate')->name('user.content.generate');
        Route::get('/user/document', 'UserDocument')->name('user.document');
        Route::get('/edit/user/document/{id}', 'EditUserDocument')->name('edit.user.document');
        Route::post('/user/update/document/{id}', 'UserUpdateDocument')->name('user.update.document');
        Route::delete('/delete/user/document/{id}', 'DeleteUserDocument')->name('delete.user.document');
    });

    Route::controller(CheckoutController::class)->group(function () {
        Route::get('/user/checkout', 'UserCheckout')->name('user.checkout');
        Route::post('/user/process/checkout', 'UserProcessCheckout')->name('user.process.checkout');
        Route::get('/payment/success', 'PaymentSuccess')->name('payment.success');
        Route::get('/invoice/generate/{id}', 'InvoiceGenerate')->name('invoice.generate');
    });

    Route::controller(GenerateController::class)->group(function () {
        Route::get('/user/generate/image', 'UserGenerateImage')->name('user.generate.image');
        Route::get('/user/all/generate/image', 'UserAllGenerateImage')->name('user.all.generate.image');
        Route::get('/user/generate/audio', 'UserGenerateAudio')->name('user.generate.audio');
        Route::get('/user/all/generate/audio', 'UserAllGenerateAudio')->name('user.all.generate.audio');
    });
});

Route::prefix('admin')->middleware(['auth', IsAdmin::class])->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('admin.dashboard');
    Route::post('/logout', [AdminController::class, 'AdminLogout'])->name('admin.logout');

    Route::get('/profile', [AdminController::class, 'AdminProfile'])->name('admin.profile');
    Route::post('/profile/store', [AdminController::class, 'AdminProfileStore'])->name('admin.profile.store');
    Route::get('/change/password', [AdminController::class, 'AdminChangePassword'])->name('admin.change.password');
    Route::post('/password/update', [AdminController::class, 'AdminPasswordUpdate'])->name('admin.password.update');

    Route::controller(PlanController::class)->group(function () {
        Route::get('/all/plans', 'AllPlans')->name('all.plans');
        Route::get('/add/plans', 'AddPlans')->name('add.plans');
        Route::post('/store/plans', 'StorePlans')->name('store.plans');
        Route::get('/edit/plans/{id}', 'EditPlans')->name('edit.plans');
        Route::post('/update/plans', 'UpdatePlans')->name('update.plans');
        Route::delete('/delete/plans/{id}', 'DeletePlans')->name('delete.plans');
    });

    Route::controller(TemplateController::class)->group(function () {
        Route::get('/all/template', 'AllTemplate')->name('all.template');
        Route::get('/add/template', 'AddTemplate')->name('add.template');
        Route::post('/store/template', 'StoreTemplate')->name('store.template');
        Route::get('/edit/template/{id}', 'EditTemplate')->name('edit.template');
        Route::post('/update/template/{id}', 'UpdateTemplate')->name('update.template');
        Route::get('/details/template/{id}', 'DetailsTemplate')->name('details.template');
        Route::post('/content/generate/{id}', 'AdminContentGenerate')->name('content.generate');
    });

    Route::controller(DocumentController::class)->group(function () {
        Route::get('/all/doucment', 'AdminDocument')->name('all.doucment');
        Route::get('/edit/document/{id}', 'EditAdminDocument')->name('edit.document');
        Route::post('/update/document/{id}', 'AdminUpdateDocument')->name('update.document');
        Route::delete('/delete/document/{id}', 'DeleteAdminDocument')->name('delete.document');
    });

    Route::controller(OrderController::class)->group(function () {
        Route::get('/all/order', 'AllOrder')->name('all.order');
        Route::patch('/update/order/status/{id}', 'UpdateOrderStatus')->name('update.order.status');
    });

    Route::controller(ChatController::class)->group(function () {
        Route::get('/all/assistants', 'AllAssistants')->name('all.assistants');
        Route::get('/add/assistants', 'AddAssistants')->name('add.assistants');
        Route::post('/chat-assistants/store', 'StoreAssistants')->name('chat-assistants.store');
        Route::get('/chat-assistants/chat/{assistantId}', 'ChatAssistants')->name('chat-assistants.chat');
        Route::post('/chat-assistants/send/{assistantId}', 'ChatSendMessage')->name('chat-assistants.send');
        Route::get('/chat-assistants/new/{assistantId}', 'StartNewConversation')->name('chat-assistants.new');
        Route::get('/chat-assistants/{assistantId}/conversation/{conversationId}', 'SelecteConversation')->name('chat-assistants.select');
    });

    Route::controller(HomeController::class)->group(function () {
        Route::get('/home/slider', 'HomeSlider')->name('home.slider');
        Route::post('/update/slider', 'UpdateSlider')->name('update.slider');
        Route::get('/contact/message', 'ContactMessage')->name('contact.message');
        Route::delete('/delete/contact/message/{id}', 'DeleteContactMessage')->name('delete.contact.message');
    });

    Route::controller(HeadingController::class)->group(function () {
        Route::get('/all/heading', 'AllHeading')->name('all.heading');
        Route::get('/add/heading', 'AddHeading')->name('add.heading');
        Route::post('/store/heading', 'StoreHeading')->name('store.heading');
        Route::get('/edit/heading/{id}', 'EditHeading')->name('edit.heading');
        Route::post('/update/heading', 'UpdateHeading')->name('update.heading');
        Route::delete('/delete/heading/{id}', 'DeleteHeading')->name('delete.heading');
    });

    Route::controller(QuestionsController::class)->group(function () {
        Route::get('/all/questions', 'AllQuestions')->name('all.questions');
        Route::get('/add/questions', 'AddQuestions')->name('add.questions');
        Route::post('/store/questions', 'StoreQuestions')->name('store.questions');
        Route::get('/edit/questions/{id}', 'EditQuestions')->name('edit.questions');
        Route::post('/update/questions', 'UpdateQuestions')->name('update.questions');
        Route::delete('/delete/questions/{id}', 'DeleteQuestions')->name('delete.questions');
    });

    Route::controller(GenerateController::class)->group(function () {
        Route::get('/generate/image', 'GenerateImage')->name('generate.image');
        Route::get('/all/generate/image', 'AllGenerateImage')->name('all.generate.image');
        Route::get('/generate/audio', 'GenerateAudio')->name('generate.audio');
        Route::get('/all/generate/audio', 'AllGenerateAudio')->name('all.generate.audio');
    });
});

Route::middleware('auth')->group(function () {
    Route::post('/update-slider/{id}', [HomeController::class, 'UpdateSliders']);
    Route::post('/update-slider-image/{id}', [HomeController::class, 'UpdateSliderImage']);
    Route::post('/update-started/{id}', [HeadingController::class, 'UpdateStarted']);

    Route::controller(GenerateController::class)->group(function () {
        Route::post('/generate-save-image', 'GenerateAndSaveImage');
        Route::post('/generate-audio', 'GenerateAndSaveAudio');
    });
});

Route::controller(HomeController::class)->group(function () {
    Route::get('/usecase', 'UseCase')->name('usecase');
    Route::get('/features', 'Features')->name('features');
    Route::get('/pricing', 'Pricing')->name('pricing');
    Route::get('/contact', 'Contact')->name('contact');
    Route::post('/store/contact', 'StoreContact')->name('store.contact');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
