@php
    $interactive = $interactive ?? false;
    $hasData = $hasData ?? false;
@endphp
<div class="section-title-row">
    <h2>{{ $title }}</h2>
    @if ($interactive)
        <span class="add-cell">
            <button type="button" wire:click="openSection('{{ $section }}')" class="{{ $hasData ? 'rb-edit-btn' : 'rb-add-btn' }}" title="{{ $hasData ? 'Edit '.$title : 'Add '.$title }}">
                {{ $hasData ? '✎' : '+' }}
            </button>
        </span>
    @endif
</div>
