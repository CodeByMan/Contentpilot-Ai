@extends('admin.dashboard')

@section('title', 'Audio Library')

@section('admin')
<div class="nk-content-inner">
    <div class="nk-content-body">
        <div class="nk-block-head nk-page-head">
            <div class="nk-block-head-between">
                <div>
                    <h1 class="display-6">Audio Library</h1>
                    <p class="text-light">Review generated audio across all user accounts.</p>
                </div>
                <a href="{{ route('admin.audio.create') }}" class="btn btn-primary">Generate Audio</a>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Text</th>
                            <th>Audio</th>
                            <th>Model</th>
                            <th>Created</th>
                            <th><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($genaudio as $item)
                            <tr>
                                <td>{{ $item->user?->name ?? 'Deleted user' }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($item->prompt, 120) }}</td>
                                <td>
                                    <audio controls preload="none" src="{{ route('admin.audio.file', $item) }}">
                                        Your browser does not support audio playback.
                                    </audio>
                                </td>
                                <td>{{ $item->model }} / {{ $item->voice }}</td>
                                <td>{{ $item->created_at->format('M j, Y H:i') }}</td>
                                <td><x-inline-delete-button :action="route('admin.audio.destroy', $item)" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="6">No generated audio found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $genaudio->links() }}</div>
        </div>
    </div>
</div>
@endsection
