<div class="flex items-center justify-center w-8 h-8 rounded-full" style="background-color: {{ $getRecord()->color }};">
    <x-filament::icon
        :icon="$getRecord()->icon"
        class="w-5 h-5 text-white"
    />
</div>
