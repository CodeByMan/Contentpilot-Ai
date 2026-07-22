@extends('home.page.main_master')
@section('title', 'Pricing · ContentPilot')
@section('page')
<section class="section cp-page-hero cp-pricing-hero"><div class="container"><div class="cp-page-hero-inner cp-reveal"><div class="cp-eyebrow"><span class="cp-eyebrow-dot"></span> Flexible creative capacity</div><h1 class="title">A plan for every stage of your content journey</h1><p class="lead">Begin with the essentials, then expand your word, image, audio, and template limits as your workload grows.</p></div></div></section>
@include('home.layout.pricing')
@include('home.layout.asked_questions', ['questions' => $questions])
@endsection
