@extends('admin.dashboard')
@section('title','Edit Template')
@section('admin')<div class="nk-content-inner"><div class="nk-content-body"><div class="nk-block-head nk-page-head"><h1 class="display-6">Edit Template</h1></div><div class="card"><div class="card-body"><form action="{{ route('admin.templates.update',$template) }}" method="POST">@include('admin.backend.template._form',['editingTemplate'=>$template])</form></div></div></div></div>@endsection
