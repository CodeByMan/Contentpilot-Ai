<?php

namespace App\Services\Ai;

use App\Enums\AiFeature;
use App\Exceptions\AiGenerationException;
use App\Models\AiUsageReservation;
use App\Models\ChatAssistant;
use App\Models\ChatConversation;
use App\Models\User;
use App\Services\Quota\AiQuotaService;
use App\Support\WordCounter;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;
use Throwable;

class AssistantChatService
{
    public function __construct(private readonly AiQuotaService $quota) {}

    public function send(
        User $user,
        ChatAssistant $assistant,
        string $conversationUuid,
        string $userMessage,
    ): ChatConversation {
        $model = (string) config('ai.default_content_model', 'gpt-4o-mini');
        $allowedModels = (array) config('ai.content_models', []);
        $maximumWords = min(800, max(50, (int) config('ai.chat_max_words', 300)));

        if (! in_array($model, $allowedModels, true)) {
            throw new AiGenerationException('The configured AI model is not allowed.');
        }

        $reservation = $this->quota->reserve($user, AiFeature::Chat, $maximumWords);

        try {
            $response = retry(
                max(1, (int) config('ai.retry_attempts', 2)),
                fn () => OpenAI::chat()->create([
                    'model' => $model,
                    'messages' => $this->messages($user, $assistant, $conversationUuid, $userMessage),
                    'max_tokens' => min(1200, max(100, (int) config('ai.chat_max_tokens', 500))),
                    'temperature' => 0.5,
                ]),
                max(0, (int) config('ai.retry_delay_ms', 250)),
            );

            $aiResponse = $this->limitWords(
                trim((string) ($response->choices[0]->message->content ?? '')),
                $maximumWords,
            );

            if ($aiResponse === '') {
                throw new AiGenerationException;
            }

            $wordCount = max(1, WordCounter::count($aiResponse));

            return $this->quota->commit(
                $reservation,
                $wordCount,
                fn (): ChatConversation => ChatConversation::query()->create([
                    'assistant_id' => $assistant->id,
                    'user_id' => $user->id,
                    'conversation_uuid' => $conversationUuid,
                    'message' => $userMessage,
                    'response' => $aiResponse,
                    'model' => $model,
                ]),
            );
        } catch (Throwable $exception) {
            $this->releaseReservationSafely($reservation);

            Log::warning('Assistant chat generation failed.', [
                'user_id' => $user->id,
                'assistant_id' => $assistant->id,
                'model' => $model,
                'exception_class' => $exception::class,
            ]);

            if ($exception instanceof AiGenerationException) {
                throw $exception;
            }

            throw new AiGenerationException(previous: $exception);
        }
    }

    /** @return array<int, array{role:string,content:string}> */
    private function messages(
        User $user,
        ChatAssistant $assistant,
        string $conversationUuid,
        string $userMessage,
    ): array {
        $historyLimit = min(20, max(0, (int) config('ai.chat_history_messages', 12)));
        $history = ChatConversation::query()
            ->whereBelongsTo($user)
            ->whereBelongsTo($assistant, 'assistant')
            ->where('conversation_uuid', $conversationUuid)
            ->latest('id')
            ->limit($historyLimit)
            ->get()
            ->reverse();

        $messages = [[
            'role' => 'system',
            'content' => (string) $assistant->instructions,
        ]];

        foreach ($history as $exchange) {
            $messages[] = ['role' => 'user', 'content' => (string) $exchange->message];
            $messages[] = ['role' => 'assistant', 'content' => (string) $exchange->response];
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage];

        return $messages;
    }

    private function releaseReservationSafely(?AiUsageReservation $reservation): void
    {
        try {
            $this->quota->release($reservation);
        } catch (Throwable $releaseException) {
            Log::error('AI quota release failed after assistant chat error.', [
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
