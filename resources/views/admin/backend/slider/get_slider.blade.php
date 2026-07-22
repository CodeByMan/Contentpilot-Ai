@extends('admin.dashboard')
@section('admin')
    <div class="nk-content-inner">
        <div class="nk-content-body">
            <div class="nk-block-head nk-page-head">
                <div class="nk-block-head-content">
                    <h2 class="display-6">Homepage Slider</h2>
                    <p class="text-muted">Update the public homepage hero content and image.</p>
                </div>
            </div>
            <div class="nk-block">
                <div class="card shadow-none">
                    <div class="card-body">
                        <form action="{{ route('admin.cms.slider.update', $slider) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="row g-3 gx-gs">
                                <div class="col-12">
                                    <label for="title" class="form-label">Title</label>
                                    <input id="title" type="text" name="title" maxlength="180" required
                                        class="form-control @error('title') is-invalid @enderror"
                                        value="{{ old('title', $slider->title) }}">
                                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea id="description" name="description" rows="4" maxlength="2000" required
                                        class="form-control @error('description') is-invalid @enderror">{{ old('description', $slider->description) }}</textarea>
                                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-7">
                                    <label for="link" class="form-label">Call-to-action URL</label>
                                    <input id="link" type="url" name="link" maxlength="2048"
                                        class="form-control @error('link') is-invalid @enderror"
                                        value="{{ old('link', $slider->link) }}" placeholder="/register or https://example.com/register">
                                    @error('link')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-5">
                                    <label for="image" class="form-label">Hero image</label>
                                    <input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp"
                                        class="form-control @error('image') is-invalid @enderror">
                                    @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <img src="{{ $slider->image_url }}" alt="Current homepage hero" class="img-fluid rounded border cms-preview-image">
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">Update slider</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
