<?php

namespace App\Services\Dashboard;

use App\Models\billingHistory;
use App\Models\GeneratedAudio;
use App\Models\GeneratedContent;
use App\Models\GeneratedImage;
use App\Models\Plan;
use App\Models\Template;
use App\Models\User;

class AdminDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function metrics(): array
    {
        return [
            'plans' => Plan::latest()->get(),
            'popularTemplates' => Template::withCount('generatedContents')->latest()->limit(8)->get(),
            'stats' => [
                'users' => User::where('role', 'user')->count(),
                'templates' => Template::count(),
                'documents' => GeneratedContent::count(),
                'revenue' => (float) billingHistory::where('status', 'Paid')->sum('total'),
                'images' => GeneratedImage::count(),
                'audio' => GeneratedAudio::count(),
            ],
            'recentDocuments' => GeneratedContent::with(['user', 'template'])->latest()->limit(5)->get(),
        ];
    }
}
