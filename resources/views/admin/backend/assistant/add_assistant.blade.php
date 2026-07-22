@extends('admin.dashboard')
@section('admin')
    <div class="nk-content-inner">
        <div class="nk-content-body">
            <div class="nk-block-head nk-page-head">
                <div class="nk-block-head-content">
                    <h2 class="display-6">Create Assistant</h2>
                    <p class="text-muted">Configure a focused AI assistant for administrator use.</p>
                </div>
            </div>
            <div class="nk-block">
                <div class="card shadow-none">
                    <div class="card-body">
                        <form action="{{ route('admin.assistants.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @include('admin.backend.assistant._form')
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
