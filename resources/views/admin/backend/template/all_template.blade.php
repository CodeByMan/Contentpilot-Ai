@extends('admin.dashboard')

@section('title', 'Templates')

@section('admin')
<div class="nk-content-inner">
    <div class="nk-content-body">
        <div class="nk-block-head nk-page-head">
            <div class="nk-block-head-between">
                <div>
                    <h1 class="display-6">Templates</h1>
                    <p class="text-light">Manage structured prompts and their input fields.</p>
                </div>
                <a href="{{ route('admin.templates.create') }}" class="btn btn-primary">Create Template</a>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Template</th>
                            <th>Category</th>
                            <th>Inputs</th>
                            <th>Generated</th>
                            <th>Status</th>
                            <th>Created by</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($template as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->title }}</strong>
                                    <div class="small text-light">{{ \Illuminate\Support\Str::limit($item->description, 80) }}</div>
                                </td>
                                <td>{{ $item->category }}</td>
                                <td>{{ $item->input_fields_count }}</td>
                                <td>{{ $item->generated_contents_count }}</td>
                                <td>
                                    <span class="badge {{ $item->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ $item->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>{{ $item->createdBy?->name ?? 'Deleted administrator' }}</td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('admin.templates.show', $item) }}" class="btn btn-sm btn-primary">Test</a>
                                    <a href="{{ route('admin.templates.edit', $item) }}" class="btn btn-sm btn-secondary">Edit</a>
                                    @if ($item->generated_contents_count === 0)
                                        <form action="{{ route('admin.templates.destroy', $item) }}" method="POST" class="d-inline" data-confirm="Delete this template permanently?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-4">No templates found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($template->hasPages())
                <div class="card-footer">{{ $template->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
