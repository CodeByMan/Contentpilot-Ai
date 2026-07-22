<div class="row g-3">
    <div class="col-12">
        <label for="title" class="form-label">Question</label>
        <input id="title" type="text" name="title" maxlength="180" required
            class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $question->title) }}">
        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label for="description" class="form-label">Answer</label>
        <textarea id="description" name="description" rows="6" maxlength="3000" required
            class="form-control @error('description') is-invalid @enderror">{{ old('description', $question->description) }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 d-flex gap-2">
        <button type="submit" class="btn btn-primary">{{ $question->exists ? 'Update question' : 'Create question' }}</button>
        <a href="{{ route('admin.cms.questions.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</div>
