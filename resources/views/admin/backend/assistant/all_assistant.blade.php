@extends('admin.dashboard')
@section('admin')
    <div class="nk-content-inner">
        <div class="nk-content-body">
            <div class="nk-block-head nk-page-head">
                <div class="nk-block-head-between flex-wrap gap g-2">
                    <div class="nk-block-head-content">
                        <h2 class="display-6">Assistant Library</h2>
                        <p class="text-muted">Manage administrator-only AI assistants.</p>
                    </div>
                    <div class="nk-block-head-content">
                        <a href="{{ route('admin.assistants.create') }}" class="btn btn-primary">Create assistant</a>
                    </div>
                </div>
            </div>

            <div class="nk-block">
                <div class="row g-gs">
                    @forelse ($assistants as $assistant)
                        <div class="col-sm-6 col-xl-4 col-xxl-3">
                            <div class="card h-100 shadow-none">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <img src="{{ $assistant->avatar_url ?: asset('assets/images/avatar-placeholder.svg') }}" data-avatar-fallback data-fallback-src="{{ asset('assets/images/avatar-placeholder.svg') }}" alt="{{ $assistant->name }} avatar"
                                            class="rounded-circle object-fit-cover" width="72" height="72">
                                        <div>
                                            <h5 class="mb-1">{{ $assistant->name }}</h5>
                                            <span class="badge {{ $assistant->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $assistant->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                    </div>
                                    <p class="text-muted flex-grow-1">{{ $assistant->role_description }}</p>
                                    <p class="small mb-3"><strong>Category:</strong> {{ $assistant->category }}</p>
                                    <div class="d-flex flex-wrap gap-2">
                                        @if ($assistant->is_active)
                                            <a href="{{ route('admin.assistants.chat', $assistant) }}" class="btn btn-sm btn-primary">Open chat</a>
                                        @endif
                                        <a href="{{ route('admin.assistants.edit', $assistant) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                        <form action="{{ route('admin.assistants.destroy', $assistant) }}" method="POST"
                                            data-confirm="Delete this assistant and all of its conversations?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-info">No assistants have been created.</div>
                        </div>
                    @endforelse
                </div>

                <div class="mt-4">{{ $assistants->links() }}</div>
            </div>
        </div>
    </div>
@endsection
