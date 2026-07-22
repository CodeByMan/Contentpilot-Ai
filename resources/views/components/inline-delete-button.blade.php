<form method="POST" action="{{ $action }}" class="d-inline" data-confirm="{{ $confirm ?? 'Are you sure you want to delete this item?' }}">
    @csrf
    @method('DELETE')
    <button type="submit" class="{{ $class ?? 'btn btn-danger btn-sm' }}">
        {{ $slot->isEmpty() ? 'Delete' : $slot }}
    </button>
</form>
