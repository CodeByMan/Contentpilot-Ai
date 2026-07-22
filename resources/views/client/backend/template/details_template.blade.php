@extends('client.client_dashboard')
@section('title',$template->title)
@section('client')<div class="nk-content-inner"><div class="nk-content-body"><div class="nk-block-head nk-page-head"><div><h1 class="display-6">{{ $template->title }}</h1><p class="text-light">{{ $template->description }}</p><p class="small">{{ number_format(max(0,$user->word_limit-$user->words_used-$user->words_reserved)) }} words currently available.</p></div></div>@include('admin.backend.template._generator',['generationAction'=>route('user.content.generate',$template)])</div></div>@endsection
