<?php

namespace App\Services\Ai;

use App\Enums\AiFeature;
use App\Exceptions\AiGenerationException;
use App\Models\AiUsageReservation;
use App\Models\GeneratedContent;
use App\Models\Template;
use App\Models\User;
use App\Services\Quota\AiQuotaService;
use App\Support\WordCounter;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;
use Throwable;
use UnexpectedValueException;

class AiContentGenerator
{
    public function __construct(
        private readonly PromptBuilder $promptBuilder,
        private readonly AiQuotaService $quota,
    ) {}

    /**
     * @param  array<string, mixed>  $inputData
     * @return array{output:string,word_count:int,remaining_words:int,document_id:int}
     */
    public function generate(
        User $user,
        Template $template,
        array $inputData,
        string $model,
        int $resultLength,
    ): array {
        if (! in_array($model, (array) config('ai.content_models', []), true)) {
            throw new AiGenerationException('The selected AI model is not allowed.');
        }

        $reservation = $this->quota->reserve($user, AiFeature::Content, $resultLength);

        try {
            $prompt = $this->promptBuilder->build($template, $inputData, $resultLength);
            $response = retry(
                max(1, (int) config('ai.retry_attempts', 2)),
                fn () => OpenAI::chat()->create([
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a secure SaaS writing assistant. Follow the supplied content brief and produce only the requested English copy.',
                        ],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'max_tokens' => min(2400, max(150, $resultLength * 2)),
                    'temperature' => 0.7,
                ]),
                max(0, (int) config('ai.retry_delay_ms', 250)),
            );

            $output = trim((string) ($response->choices[0]->message->content ?? ''));

            if ($output === '') {
                throw new UnexpectedValueException('The AI provider returned an empty response.');
            }

            $output = $this->limitWords($output, $resultLength);
            $wordCount = max(1, WordCounter::count($output));
            $document = $this->quota->commit(
                $reservation,
                $wordCount,
                fn (): GeneratedContent => GeneratedContent::query()->create([
                    'user_id' => $user->id,
                    'template_id' => $template->id,
                    'input' => $inputData,
                    'output' => $output,
                    'word_count' => $wordCount,
                    'model' => $model,
                ]),
            );

            $freshUser = $user->fresh();
            $remainingWords = $freshUser
                ? max(0, (int) $freshUser->word_limit - (int) $freshUser->words_used - (int) $freshUser->words_reserved)
                : 0;

            return [
                'output' => $output,
                'word_count' => $wordCount,
                'remaining_words' => $remainingWords,
                'document_id' => $document->id,
            ];
        } catch (Throwable $exception) {
            $this->releaseReservationSafely($reservation);

            Log::warning('AI content generation failed.', [
                'user_id' => $user->id,
                'template_id' => $template->id,
                'model' => $model,
                'exception_class' => $exception::class,
            ]);

            if ($exception instanceof AiGenerationException) {
                throw $exception;
            }

            throw new AiGenerationException(previous: $exception);
        }
    }

    private function releaseReservationSafely(?AiUsageReservation $reservation): void
    {
        try {
            $this->quota->release($reservation);
        } catch (Throwable $releaseException) {
            Log::error('AI quota release failed after content generation error.', [
                'reservation_id' => $reservation?->id,
                'exception_class' => $releaseException::class,
            ]);
        }
    }

    private function limitWords(string $content, int $maximumWords): string
    {
        $words = preg_split('/\s+/u', trim($content), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return count($words) <= $maximumWords
            ? trim($content)
            : implode(' ', array_slice($words, 0, $maximumWords));
    }
}
