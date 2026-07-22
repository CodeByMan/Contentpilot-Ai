@php($editing = $assistant->exists)
<div class="row g-3 gx-gs">
    <div class="col-md-6">
        <label for="name" class="form-label">Assistant name</label>
        <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $assistant->name) }}" maxlength="120" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="category" class="form-label">Category</label>
        <select id="category" name="category" class="form-select @error('category') is-invalid @enderror" required>
            <option value="">Select a category</option>
            @foreach (config('ai.assistant_categories') as $category)
                <option value="{{ $category }}" @selected(old('category', $assistant->category) === $category)>{{ $category }}</option>
            @endforeach
        </select>
        @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label for="role_description" class="form-label">Role description</label>
        <input id="role_description" type="text" name="role_description"
            class="form-control @error('role_description') is-invalid @enderror"
            value="{{ old('role_description', $assistant->role_description) }}" maxlength="255" required>
        @error('role_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label for="welcome_message" class="form-label">Welcome message</label>
        <textarea id="welcome_message" name="welcome_message" rows="3"
            class="form-control @error('welcome_message') is-invalid @enderror" maxlength="1000" required>{{ old('welcome_message', $assistant->welcome_message) }}</textarea>
        @error('welcome_message')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label for="instructions" class="form-label">System instructions</label>
        <textarea id="instructions" name="instructions" rows="8"
            class="form-control @error('instructions') is-invalid @enderror" maxlength="6000" required>{{ old('instructions', $assistant->instructions) }}</textarea>
        <small class="text-muted">Define the assistant's role, boundaries, response style, and safety requirements.</small>
        @error('instructions')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-8">
        <label for="avatar" class="form-label">Avatar image</label>
        <input id="avatar" type="file" name="avatar" accept="image/jpeg,image/png,image/webp"
            class="form-control @error('avatar') is-invalid @enderror">
        <small class="text-muted">JPEG, PNG, or WebP. Maximum 2 MB.</small>
        @error('avatar')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4 d-flex align-items-center">
        @if ($editing)
            <img src="{{ $assistant->avatar_url ?: asset('assets/images/avatar-placeholder.svg') }}" data-avatar-fallback data-fallback-src="{{ asset('assets/images/avatar-placeholder.svg') }}" alt="{{ $assistant->name }} avatar" class="rounded-circle me-3 object-fit-cover"
                width="72" height="72">
        @endif
        <div class="form-check form-switch">
            <input type="hidden" name="is_active" value="0">
            <input id="is_active" type="checkbox" name="is_active" value="1" class="form-check-input"
                @checked(old('is_active', $assistant->is_active))>
            <label for="is_active" class="form-check-label">Active</label>
        </div>
    </div>

    <div class="col-12 d-flex gap-2">
        <button type="submit" class="btn btn-primary">{{ $editing ? 'Update assistant' : 'Create assistant' }}</button>
        <a href="{{ route('admin.assistants.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</div>
