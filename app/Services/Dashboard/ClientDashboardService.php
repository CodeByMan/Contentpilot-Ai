<?php

namespace App\Services\Dashboard;

use App\Models\Template;
use App\Models\User;

class ClientDashboardService
{
    /** @return array<string, mixed> */
    public function metrics(User $user): array
    {
        $user->loadMissing('plan');
        $templateLimit = max(0, (int) ($user->plan?->templates ?? 0));
        $wordLimit = max(0, (int) $user->word_limit);
        $wordsUsed = max(0, (int) $user->words_used);
        $wordsReserved = max(0, (int) $user->words_reserved);

        return [
            'user' => $user,
            'templateLimit' => $templateLimit,
            'wordsUsed' => $wordsUsed,
            'wordsReserved' => $wordsReserved,
            'wordLimit' => $wordLimit,
            'wordsLeft' => max(0, $wordLimit - $wordsUsed - $wordsReserved),
            'usagePercent' => $wordLimit > 0
                ? min(100, round((($wordsUsed + $wordsReserved) / $wordLimit) * 100))
                : 0,
            'templates' => Template::query()
                ->where('is_active', true)
                ->orderByDesc('id')
                ->limit($templateLimit)
                ->get(),
            'documentsCount' => $user->generatedContents()->count(),
            'imagesCount' => $user->generatedImages()->count(),
            'audioCount' => $user->generatedAudios()->count(),
            'recentDocuments' => $user->generatedContents()
                ->with('template:id,title')
                ->latest()
                ->limit(5)
                ->get(),
        ];
    }
}
