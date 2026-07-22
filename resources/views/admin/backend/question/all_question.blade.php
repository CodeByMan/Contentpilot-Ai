@extends('admin.dashboard')
@section('admin')
    <div class="nk-content-inner"><div class="nk-content-body">
        <div class="nk-block-head nk-page-head"><div class="nk-block-head-between flex-wrap gap g-2">
            <div class="nk-block-head-content"><h2 class="display-6">Frequently Asked Questions</h2></div>
            <div class="nk-block-head-content"><a class="btn btn-primary" href="{{ route('admin.cms.questions.create') }}">Create question</a></div>
        </div></div>
        <div class="card"><div class="table-responsive"><table class="table table-middle mb-0">
            <thead class="table-light"><tr><th>#</th><th>Question</th><th>Answer</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse ($questions as $question)
                    <tr>
                        <td>{{ $questions->firstItem() + $loop->index }}</td>
                        <td>{{ $question->title }}</td>
                        <td>{{ Illuminate\Support\Str::limit($question->description, 120) }}</td>
                        <td class="text-nowrap">
                            <a href="{{ route('admin.cms.questions.edit', $question) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <x-inline-delete-button :action="route('admin.cms.questions.destroy', $question)" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No questions found.</td></tr>
                @endforelse
            </tbody>
        </table></div></div>
        <div class="mt-3">{{ $questions->links() }}</div>
    </div></div>
@endsection
