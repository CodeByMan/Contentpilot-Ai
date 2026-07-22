<?php

namespace Database\Seeders;

use App\Models\Heading;
use App\Models\Plan;
use App\Models\Question;
use App\Models\Slider;
use Illuminate\Database\Seeder;

class ApplicationReferenceSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->plans() as $plan) {
            Plan::query()->updateOrCreate(['name' => $plan['name']], $plan);
        }

        Slider::query()->updateOrCreate(
            ['id' => 1],
            [
                'title' => 'Create polished English content with responsible AI tools',
                'description' => 'Generate, review, edit, and securely manage written content, images, and audio from one Laravel workspace.',
                'image' => null,
                'link' => '/register',
            ],
        );

        foreach ($this->headings() as $heading) {
            Heading::query()->updateOrCreate(['title' => $heading['title']], $heading);
        }

        foreach ($this->questions() as $question) {
            Question::query()->updateOrCreate(['title' => $question['title']], $question);
        }
    }

    /** @return array<int, array<string, int|float|string|bool>> */
    private function plans(): array
    {
        return [
            [
                'name' => 'Starter',
                'monthly_word_limit' => 5_000,
                'price' => 0,
                'templates' => 4,
                'image_generation_limit' => 2,
                'audio_generation_limit' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Creator',
                'monthly_word_limit' => 25_000,
                'price' => 19,
                'templates' => 12,
                'image_generation_limit' => 30,
                'audio_generation_limit' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Agency',
                'monthly_word_limit' => 100_000,
                'price' => 79,
                'templates' => 30,
                'image_generation_limit' => 150,
                'audio_generation_limit' => 150,
                'is_active' => true,
            ],
        ];
    }

    /** @return array<int, array{title: string, description: string}> */
    private function headings(): array
    {
        return [
            [
                'title' => 'Create English content faster with guided templates',
                'description' => 'Choose a template, provide clear source details, and generate editable content for blogs, campaigns, emails, and landing pages.',
            ],
            [
                'title' => 'A secure workflow from prompt to published draft',
                'description' => 'Your generations remain private to your account and are protected by plan limits, authorization policies, and rate controls.',
            ],
            [
                'title' => 'Plans designed for individual creators and growing teams',
                'description' => 'Start with essential tools and upgrade through the reviewed offline-payment workflow when your usage grows.',
            ],
        ];
    }

    /** @return array<int, array{title: string, description: string}> */
    private function questions(): array
    {
        return [
            [
                'title' => 'How are plan upgrades activated?',
                'description' => 'Submit the requested offline-payment details. An administrator reviews the submission before any plan is activated.',
            ],
            [
                'title' => 'Who can access generated content?',
                'description' => 'Generated documents, images, audio, and invoices are restricted to their owner. Administrators have explicitly authorized management access.',
            ],
            [
                'title' => 'What happens when an AI request fails?',
                'description' => 'Reserved quota is released when generation fails, so unsuccessful provider requests do not permanently consume your allowance.',
            ],
        ];
    }
}
