@extends('client.client_dashboard')

@section('title', 'Audio Library')

@section('client')
<div class="nk-content-inner">
    <div class="nk-content-body">
        <div class="nk-block-head nk-page-head">
            <div class="nk-block-head-between">
                <div>
                    <h1 class="display-6">Audio Library</h1>
                    <p class="text-light">Listen to and manage audio generated from your account.</p>
                </div>
                <a href="{{ route('user.audio.create') }}" class="btn btn-primary">Generate Audio</a>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
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
                                <td>{{ \Illuminate\Support\Str::limit($item->prompt, 120) }}</td>
                                <td>
                                    <audio controls preload="none" src="{{ route('user.audio.file', $item) }}">
                                        Your browser does not support audio playback.
                                    </audio>
                                </td>
                                <td>{{ $item->model }} / {{ $item->voice }}</td>
                                <td>{{ $item->created_at->format('M j, Y H:i') }}</td>
                                <td><x-inline-delete-button :action="route('user.audio.destroy', $item)" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="5">No generated audio found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $genaudio->links() }}</div>
        </div>
    </div>
</div>
@endsection
