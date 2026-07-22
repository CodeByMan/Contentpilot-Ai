@extends('client.client_dashboard')
@section('title','Payment Submitted')
@section('client')<div class="nk-content-inner"><div class="nk-content-body"><div class="card"><div class="card-body text-center py-5"><em class="cp-icon cp-icon-clock fs-1 text-warning"></em><h1 class="h2 mt-3">Payment Submitted for Review</h1><p>Your plan has not been changed. An administrator will review the payment, then approve or reject the request.</p><a href="{{ route('user.checkout') }}" class="btn btn-primary">View Billing Status</a></div></div></div></div>@endsection
