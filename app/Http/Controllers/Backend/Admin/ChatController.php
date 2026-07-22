<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChatMessageRequest;
use App\Http\Requests\Admin\StoreAssistantRequest;
use App\Models\ChatAssistant;
use App\Models\ChatConversation;
use App\Services\Ai\AssistantChatService;
use App\Services\Storage\SecureUploadService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(): View
    {
        return view('admin.backend.assistant.all_assistant', [
            'assistants' => ChatAssistant::query()->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.backend.assistant.add_assistant', [
            'assistant' => new ChatAssistant(['is_active' => true]),
        ]);
    }

    public function store(
        StoreAssistantRequest $request,
        SecureUploadService $uploads,
    ): RedirectResponse {
        $data = $request->safe()->except('avatar');

        $newAvatar = null;

        if ($request->hasFile('avatar')) {
            $newAvatar = $uploads->storeImage(
                $request->file('avatar'),
                'assistants',
                512,
                512,
                maxBytes: 2_097_152,
            );
            $data['avatar'] = $newAvatar;
        }

        try {
            ChatAssistant::query()->create($data);
        } catch (\Throwable $exception) {
            $uploads->delete($newAvatar);

            throw $exception;
        }

        return $this->success('Assistant created successfully.');
    }

    public function edit(ChatAssistant $assistant): View
    {
        return view('admin.backend.assistant.edit_assistant', compact('assistant'));
    }

    public function update(
        StoreAssistantRequest $request,
        ChatAssistant $assistant,
        SecureUploadService $uploads,
    ): RedirectResponse {
        $data = $request->safe()->except('avatar');

        $oldAvatar = $assistant->avatar;
        $newAvatar = null;

        if ($request->hasFile('avatar')) {
            $newAvatar = $uploads->storeImage(
                $request->file('avatar'),
                'assistants',
                512,
                512,
                maxBytes: 2_097_152,
            );
            $data['avatar'] = $newAvatar;
        }

        try {
            $assistant->update($data);
        } catch (\Throwable $exception) {
            $uploads->delete($newAvatar);

            throw $exception;
        }

        if ($newAvatar) {
            $uploads->delete($oldAvatar);
        }

        return $this->success('Assistant updated successfully.');
    }

    public function destroy(ChatAssistant $assistant, SecureUploadService $uploads): RedirectResponse
    {
        $avatar = $assistant->avatar;
        $assistant->delete();
        $uploads->delete($avatar);

        return $this->success('Assistant deleted successfully.');
    }

    public function chat(Request $request, ChatAssistant $assistant): View
    {
        abort_unless($assistant->is_active, 404);

        $conversations = $this->conversationSummaries($request, $assistant);
        $selectedUuid = $request->query('conversation') ?: $conversations->first()?->conversation_uuid;
        $messages = collect();

        if ($selectedUuid) {
            abort_unless(Str::isUuid($selectedUuid), 404);
            $messages = ChatConversation::query()
                ->whereBelongsTo($assistant, 'assistant')
                ->whereBelongsTo($request->user())
                ->where('conversation_uuid', $selectedUuid)
                ->oldest()
                ->get();
        }

        return view('admin.backend.assistant.chat_assistant', [
            'assistant' => $assistant,
            'conversations' => $conversations,
            'messages' => $messages,
            'selectedConversationUuid' => $selectedUuid,
        ]);
    }

    public function sendMessage(
        ChatMessageRequest $request,
        ChatAssistant $assistant,
        AssistantChatService $chat,
    ): RedirectResponse {
        abort_unless($assistant->is_active, 404);

        $conversationUuid = $request->validated('conversation_uuid') ?: (string) Str::uuid();
        $chat->send(
            $request->user(),
            $assistant,
            $conversationUuid,
            $request->validated('message'),
        );

        return redirect()->route('admin.assistants.chat', [
            'assistant' => $assistant,
            'conversation' => $conversationUuid,
        ])->with([
            'message' => 'Message sent successfully.',
            'alert-type' => 'success',
        ]);
    }

    public function startConversation(ChatAssistant $assistant): RedirectResponse
    {
        abort_unless($assistant->is_active, 404);

        return redirect()->route('admin.assistants.chat', [
            'assistant' => $assistant,
            'conversation' => (string) Str::uuid(),
        ]);
    }

    public function selectConversation(
        Request $request,
        ChatAssistant $assistant,
        string $conversationUuid,
    ): RedirectResponse {
        abort_unless($assistant->is_active, 404);

        $exists = ChatConversation::query()
            ->whereBelongsTo($assistant, 'assistant')
            ->whereBelongsTo($request->user())
            ->where('conversation_uuid', $conversationUuid)
            ->exists();

        abort_unless($exists, 404);

        return redirect()->route('admin.assistants.chat', [
            'assistant' => $assistant,
            'conversation' => $conversationUuid,
        ]);
    }

    private function conversationSummaries(Request $request, ChatAssistant $assistant): Collection
    {
        $summary = ChatConversation::query()
            ->selectRaw('conversation_uuid, MAX(id) AS latest_id, COUNT(*) AS messages_count')
            ->whereBelongsTo($assistant, 'assistant')
            ->whereBelongsTo($request->user())
            ->groupBy('conversation_uuid');

        return ChatConversation::query()
            ->joinSub(
                $summary,
                'conversation_summary',
                fn ($join) => $join->on('chat_conversations.id', '=', 'conversation_summary.latest_id'),
            )
            ->select([
                'chat_conversations.conversation_uuid',
                'chat_conversations.message',
                'chat_conversations.created_at',
                'conversation_summary.messages_count',
            ])
            ->latest('chat_conversations.created_at')
            ->get();
    }

    private function success(string $message): RedirectResponse
    {
        return redirect()->route('admin.assistants.index')->with([
            'message' => $message,
            'alert-type' => 'success',
        ]);
    }
}
