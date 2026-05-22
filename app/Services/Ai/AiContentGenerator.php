<?php

namespace App\Services\Ai;

use App\Models\GeneratedContent;
use App\Models\Template;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use OpenAI\Laravel\Facades\OpenAI;

class AiContentGenerator
{
    public function __construct(private readonly PromptBuilder $promptBuilder)
    {
    }

    /**
     * @param  array<string, mixed>  $inputData
     * @return array{output: string, word_count: int, remaining_words: int}
     */
    public function generate(User $user, Template $template, array $inputData, string $language, string $model, int $resultLength): array
    {
        $estimatedWords = $resultLength;
        $wordsUsed = (int) $user->words_used;
        $wordLimit = (int) $user->current_word_usage;

        if (($wordsUsed + $estimatedWords) > $wordLimit) {
            throw new \RuntimeException('Word limit exceeded. Upgrade the plan or reduce the requested output length.');
        }

        $prompt = $this->promptBuilder->build($template, $inputData, $language, $resultLength);

        $response = OpenAI::chat()->create([
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a conversion-focused SaaS content writer. Keep the answer clear, structured, and useful.',
                ],
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        $output = trim((string) $response->choices[0]->message->content);
        $wordCount = str_word_count(strip_tags($output));

        DB::transaction(function () use ($user, $template, $inputData, $output, $wordCount): void {
            $user->increment('words_used', $wordCount);

            GeneratedContent::create([
                'user_id' => $user->id,
                'template_id' => $template->id,
                'input' => $inputData,
                'output' => $output,
                'word_count' => $wordCount,
            ]);
        });

        return [
            'output' => $output,
            'word_count' => $wordCount,
            'remaining_words' => max(0, $wordLimit - ($wordsUsed + $wordCount)),
        ];
    }
}
