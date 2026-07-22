<?php

namespace App\Services\Dashboard;

use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\BillingHistory;
use App\Models\GeneratedAudio;
use App\Models\GeneratedContent;
use App\Models\GeneratedImage;
use App\Models\Plan;
use App\Models\Template;
use App\Models\User;

class AdminDashboardService
{
    /** @return array<string, mixed> */
    public function metrics(): array
    {
        return [
            'plans' => Plan::query()->orderBy('price')->get(),
            'popularTemplates' => Template::query()
                ->withCount('generatedContents')
                ->orderByDesc('generated_contents_count')
                ->limit(8)
                ->get(),
            'stats' => [
                'users' => User::query()->where('role', UserRole::User->value)->count(),
                'templates' => Template::query()->count(),
                'documents' => GeneratedContent::query()->count(),
                'revenue' => (float) BillingHistory::query()
                    ->where('status', PaymentStatus::Approved->value)
                    ->sum('total'),
                'pendingPayments' => BillingHistory::query()
                    ->where('status', PaymentStatus::Pending->value)
                    ->count(),
                'images' => GeneratedImage::query()->count(),
                'audio' => GeneratedAudio::query()->count(),
            ],
            'recentDocuments' => GeneratedContent::query()
                ->with(['user:id,name', 'template:id,title'])
                ->latest()
                ->limit(5)
                ->get(),
        ];
    }
}
