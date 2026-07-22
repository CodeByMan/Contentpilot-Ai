@extends('admin.dashboard')

@section('title', 'Image Library')

@section('admin')
<div class="nk-content-inner">
    <div class="nk-content-body">
        <div class="nk-block-head nk-page-head">
            <div class="nk-block-head-between">
                <div>
                    <h1 class="display-6">Image Library</h1>
                    <p class="text-light">Review generated images across all user accounts.</p>
                </div>
                <a href="{{ route('admin.images.create') }}" class="btn btn-primary">Generate Image</a>
            </div>
        </div>

        <div class="row g-gs">
            @forelse ($genimage as $item)
                <div class="col-md-6 col-xl-4">
                    <article class="card h-100">
                        <img src="{{ route('admin.images.file', $item) }}" class="card-img-top" alt="AI-generated image based on the saved prompt">
                        <div class="card-body d-flex flex-column">
                            <p>{{ \Illuminate\Support\Str::limit($item->prompt, 140) }}</p>
                            <div class="small text-light">{{ $item->user?->name ?? 'Deleted user' }}</div>
                            <div class="small text-light mb-3">
                                {{ $item->model }} · {{ number_format((int) $item->size_bytes / 1024) }} KB
                            </div>
                            <div class="mt-auto">
                                <x-inline-delete-button :action="route('admin.images.destroy', $item)" />
                            </div>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12"><p>No generated images found.</p></div>
            @endforelse
        </div>

        <div class="mt-4">{{ $genimage->links() }}</div>
    </div>
</div>
@endsection
