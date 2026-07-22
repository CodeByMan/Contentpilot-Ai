@extends('admin.dashboard')
@section('admin')
    <div class="nk-content-inner"><div class="nk-content-body">
        <div class="nk-block-head nk-page-head"><div class="nk-block-head-content"><h2 class="display-6">Create Heading</h2></div></div>
        <div class="card shadow-none"><div class="card-body">
            <form action="{{ route('admin.cms.headings.store') }}" method="POST">@csrf @include('admin.backend.heading._form')</form>
        </div></div>
    </div></div>
@endsection
