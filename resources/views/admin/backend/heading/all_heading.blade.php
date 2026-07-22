@extends('admin.dashboard')
@section('admin')
    <div class="nk-content-inner"><div class="nk-content-body">
        <div class="nk-block-head nk-page-head"><div class="nk-block-head-between flex-wrap gap g-2">
            <div class="nk-block-head-content"><h2 class="display-6">Homepage Headings</h2></div>
            <div class="nk-block-head-content"><a class="btn btn-primary" href="{{ route('admin.cms.headings.create') }}">Create heading</a></div>
        </div></div>
        <div class="card"><div class="table-responsive"><table class="table table-middle mb-0">
            <thead class="table-light"><tr><th>#</th><th>Title</th><th>Description</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse ($headings as $heading)
                    <tr>
                        <td>{{ $headings->firstItem() + $loop->index }}</td>
                        <td>{{ $heading->title }}</td>
                        <td>{{ Illuminate\Support\Str::limit($heading->description, 100) }}</td>
                        <td class="text-nowrap">
                            <a href="{{ route('admin.cms.headings.edit', $heading) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <x-inline-delete-button :action="route('admin.cms.headings.destroy', $heading)" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No headings found.</td></tr>
                @endforelse
            </tbody>
        </table></div></div>
        <div class="mt-3">{{ $headings->links() }}</div>
    </div></div>
@endsection
