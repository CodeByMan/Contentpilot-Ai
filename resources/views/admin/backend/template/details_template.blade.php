@extends('admin.dashboard')
@section('title',$template->title)
@section('admin')<div class="nk-content-inner"><div class="nk-content-body"><div class="nk-block-head nk-page-head"><div><h1 class="display-6">{{ $template->title }}</h1><p class="text-light">{{ $template->description }}</p></div></div>@include('admin.backend.template._generator',['generationAction'=>route('admin.content.generate',$template)])</div></div>@endsection
