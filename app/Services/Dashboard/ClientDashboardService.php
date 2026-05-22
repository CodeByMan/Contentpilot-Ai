<?php

namespace App\Services\Dashboard;

use App\Models\GeneratedAudio;
use App\Models\GeneratedContent;
use App\Models\GeneratedImage;
use App\Models\Template;
use App\Models\User;

class ClientDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function metrics(User $user): array
    {
        $templateLimit = (int) ($user->plan?->templates ?? 3);
        $wordLimit = (int) $user->current_word_usage;
        $wordsUsed = (int) $user->words_used;

        return [
            'user' => $user->loadMissing('plan'),
            'templateLimit' => $templateLimit,
            'wordsUsed' => $wordsUsed,
            'wordLimit' => $wordLimit,
            'wordsLeft' => max(0, $wordLimit - $wordsUsed),
            'usagePercent' => $wordLimit > 0 ? min(100, round(($wordsUsed / $wordLimit) * 100)) : 0,
            'templates' => Template::where('is_active', true)->latest()->limit($templateLimit)->get(),
            'documentsCount' => GeneratedContent::where('user_id', $user->id)->count(),
            'imagesCount' => GeneratedImage::where('user_id', $user->id)->count(),
            'audioCount' => GeneratedAudio::where('user_id', $user->id)->count(),
            'recentDocuments' => GeneratedContent::with('template')->where('user_id', $user->id)->latest()->limit(5)->get(),
        ];
    }
}
