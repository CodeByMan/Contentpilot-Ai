@php
    $toastMessage = session('message') ?? session('status');
    $toastType = session('alert-type', 'success');
    $toastIsError = in_array($toastType, ['error', 'danger'], true);
@endphp
@if($toastMessage)
    <div class="cp-toast-stack" aria-live="polite" aria-atomic="true">
        <div class="cp-premium-toast {{ $toastIsError ? 'is-error' : '' }}" data-auto-dismiss="5000" role="status">
            <span class="cp-toast-icon"><em class="cp-icon {{ $toastIsError ? 'cp-icon-lock' : 'cp-icon-spark' }}"></em></span>
            <div class="cp-toast-copy"><strong>{{ $toastIsError ? 'Action required' : 'ContentPilot' }}</strong><span>{{ $toastMessage }}</span></div>
            <button type="button" class="cp-toast-close" data-toast-close aria-label="Dismiss notification">×</button>
            <span class="cp-toast-progress" aria-hidden="true"></span>
        </div>
    </div>
@endif
