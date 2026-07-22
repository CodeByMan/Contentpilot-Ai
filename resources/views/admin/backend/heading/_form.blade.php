<div class="row g-3">
    <div class="col-12">
        <label for="title" class="form-label">Heading title</label>
        <input id="title" type="text" name="title" maxlength="180" required
            class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $heading->title) }}">
        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label for="description" class="form-label">Description</label>
        <textarea id="description" name="description" rows="5" maxlength="2000" required
            class="form-control @error('description') is-invalid @enderror">{{ old('description', $heading->description) }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 d-flex gap-2">
        <button type="submit" class="btn btn-primary">{{ $heading->exists ? 'Update heading' : 'Create heading' }}</button>
        <a href="{{ route('admin.cms.headings.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</div>
