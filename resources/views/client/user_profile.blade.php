@extends('client.client_dashboard')
@section('title','Profile')
@section('client')
<div class="nk-content-inner"><div class="nk-content-body"><div class="nk-block-head nk-page-head"><h1 class="display-6">Profile</h1></div><div class="card"><div class="card-body"><form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data">@csrf @method('PATCH')<div class="row g-3">
<div class="col-md-6"><label class="form-label" for="name">Name</label><input id="name" name="name" class="form-control" value="{{ old('name',$profileData->name) }}" maxlength="120" required></div>
<div class="col-md-6"><label class="form-label" for="email">Email</label><input id="email" type="email" name="email" class="form-control" value="{{ old('email',$profileData->email) }}" maxlength="180" required><div class="form-note">Changing your email requires verification again.</div></div>
<div class="col-md-6"><label class="form-label" for="current_password">Current password</label><input id="current_password" type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password"><div class="form-note">Required only when changing the email address.</div>@error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="col-md-6"><label class="form-label" for="phone">Phone</label><input id="phone" name="phone" class="form-control" value="{{ old('phone',$profileData->phone) }}" maxlength="30"></div>
<div class="col-md-6"><label class="form-label" for="address">Address</label><input id="address" name="address" class="form-control" value="{{ old('address',$profileData->address) }}" maxlength="1000"></div>
<div class="col-md-6"><label class="form-label" for="photo">Profile image</label><input id="photo" type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp" data-image-preview-target="#user-profile-preview"><div class="form-note">JPEG, PNG, or WebP. Maximum 2 MB.</div></div>
<div class="col-md-6 d-flex align-items-end"><img id="user-profile-preview" src="{{ $profileData->photo_url ?: asset('assets/images/avatar-placeholder.svg') }}" data-avatar-fallback data-profile-image-preview data-fallback-src="{{ asset('assets/images/avatar-placeholder.svg') }}" width="96" height="96" class="rounded-circle object-fit-cover" alt="{{ $profileData->name }}"></div>
<div class="col-12"><button class="btn btn-primary" type="submit">Save Profile</button></div>
</div></form></div></div>
<div class="card border-danger mt-4">
    <div class="card-body">
        <h2 class="h5 text-danger">Delete account</h2>
        <p class="text-muted">This permanently disables your account, removes generated media files, and anonymizes your personal profile details. Billing records are retained for audit integrity.</p>
        <form action="{{ route('user.profile.destroy') }}" method="POST" data-confirm="Permanently delete your account?">
            @csrf
            @method('DELETE')
            <div class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label for="delete_password" class="form-label">Current password</label>
                    <input id="delete_password" type="password" name="password" class="form-control @error('password', 'accountDeletion') is-invalid @enderror" required autocomplete="current-password">
                    @error('password', 'accountDeletion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6"><button type="submit" class="btn btn-danger">Delete my account</button></div>
            </div>
        </form>
    </div>
</div>
</div></div>
@endsection
