@php
    $record = $getRecord();
    $color = $record->color ?? $record->parent?->color ?? '#gray';
    $icon = $record->icon ?? $record->parent?->icon;
@endphp


<div class="flex items-center justify-center w-8 h-8 rounded-full" style="background-color: {{ $color  }};">
    <x-filament::icon
        :icon="$icon"
        class="w-5 h-5 text-white"
    />
</div>
