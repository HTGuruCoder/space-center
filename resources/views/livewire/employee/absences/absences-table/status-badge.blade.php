@php
    $badgeClass = match($status->value) {
        'pending' => 'badge-warning',
        'approved' => 'badge-success',
        'rejected' => 'badge-error',
        default => 'badge-ghost',
    };
@endphp

<span class="badge {{ $badgeClass }}">
    {{ $status->label() }}
</span>
