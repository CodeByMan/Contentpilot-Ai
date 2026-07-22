<?php

namespace App\Policies;

use App\Models\Template;
use App\Models\User;

class TemplatePolicy
{
    public function view(User $user, Template $template): bool
    {
        if (! $template->is_active || ! $user->is_active) {
            return false;
        }

        $user->loadMissing('plan');
        $limit = max(0, (int) ($user->plan?->templates ?? 0));

        if ($limit === 0) {
            return false;
        }

        return Template::query()
            ->where('is_active', true)
            ->orderByDesc('id')
            ->limit($limit)
            ->pluck('id')
            ->contains($template->id);
    }
}
