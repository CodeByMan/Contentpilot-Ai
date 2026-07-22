@extends('admin.dashboard')
@section('admin')
    <div class="nk-content-body">
        <div class="nk-block-head nk-page-head">
            <div class="nk-block-head-content">
                <h2 class="title display-6">Chat with <strong>{{ $assistant->name }}</strong></h2>
                <p class="text-muted">{{ $assistant->welcome_message }}</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-header text-bg-info-soft">
                        <h5 class="mb-0">Conversations</h5>
                    </div>
                    <div class="card-body p-0">
                        <form method="POST" action="{{ route('admin.assistants.conversations.store', $assistant) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100 rounded-0">New Conversation</button>
                        </form>
                        <div class="list-group list-group-flush">
                            @forelse ($conversations as $conversation)
                                <a href="{{ route('admin.assistants.conversations.show', [$assistant, $conversation->conversation_uuid]) }}"
                                    class="list-group-item list-group-item-action {{ $selectedConversationUuid === $conversation->conversation_uuid ? 'active' : '' }}">
                                    <div class="d-flex justify-content-between gap-2">
                                        <strong>Conversation</strong>
                                        <small>{{ $conversation->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-1 text-truncate">{{ Illuminate\Support\Str::limit($conversation->message, 40) }}</p>
                                    <small>{{ $conversation->messages_count }} messages</small>
                                </a>
                            @empty
                                <p class="p-3 mb-0 text-muted">No conversations yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">{{ $selectedConversationUuid ? 'Conversation' : 'Start a conversation' }}</h5>
                    </div>
                    <div class="card-body p-3 assistant-chat-scroll">
                        @forelse ($messages as $message)
                            <div class="mb-4">
                                <div class="d-flex justify-content-end">
                                    <div class="p-3 rounded bg-primary text-white assistant-message-bubble">
                                        <strong class="d-block mb-1">You</strong>
                                        <p class="mb-0">{{ $message->message }}</p>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-start mt-2">
                                    <div class="p-3 rounded bg-light border assistant-message-bubble">
                                        <strong class="d-block mb-1">{{ $assistant->name }}</strong>
                                        <p class="mb-0 preformatted-text">{{ $message->response }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-muted py-5">Send a message to begin.</p>
                        @endforelse
                    </div>
                    <div class="card-footer bg-white border-top">
                        <form action="{{ route('admin.assistants.messages.store', $assistant) }}" method="POST" class="input-group">
                            @csrf
                            <input type="hidden" name="conversation_uuid" value="{{ $selectedConversationUuid }}">
                            <input type="text" name="message" class="form-control @error('message') is-invalid @enderror" maxlength="{{ config('ai.chat_max_characters') }}"
                                value="{{ old('message') }}" placeholder="Type your message" autocomplete="off" required>
                            <button type="submit" class="btn btn-primary">Send</button>
                        </form>
                        @error('message')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
