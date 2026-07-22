@extends('admin.dashboard')
@section('admin')
    <div class="nk-content-inner">
        <div class="nk-content-body">
            <div class="nk-block-head nk-page-head">
                <div class="nk-block-head-content">
                    <h2 class="display-6">Edit Assistant</h2>
                    <p class="text-muted">Update {{ $assistant->name }} without exposing its system instructions publicly.</p>
                </div>
            </div>
            <div class="nk-block">
                <div class="card shadow-none">
                    <div class="card-body">
                        <form action="{{ route('admin.assistants.update', $assistant) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            @include('admin.backend.assistant._form')
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
