@props([
    'title'       => 'Are you sure?',
    'text'        => 'This action cannot be undone.',
    'action'      => '',
    'confirmText' => 'Yes, confirm',
    'cancelText'  => 'Cancel',
    'icon'        => 'warning',
])

<button
    type="button"
    x-data
    @click="Swal.fire({
        title: @js($title),
        text: @js($text),
        icon: '{{ $icon }}',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#374151',
        confirmButtonText: @js($confirmText),
        cancelButtonText: @js($cancelText),
        background: '#111118',
        color: '#e5e7eb',
        customClass: {
            popup: 'rounded-xl border border-dark-700',
            confirmButton: 'rounded-lg font-medium text-sm px-5 py-2.5',
            cancelButton: 'rounded-lg font-medium text-sm px-5 py-2.5',
        },
    }).then(result => { if (result.isConfirmed) { {{ $action }} } })"
    {{ $attributes }}
>{{ $slot }}</button>
