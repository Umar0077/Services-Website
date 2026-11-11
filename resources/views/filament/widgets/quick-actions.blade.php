<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Quick actions</x-slot>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px">
            @foreach ($actions as $action)
                <x-filament::button tag="a" :href="$action['url']" :icon="$action['icon']" color="gray" outlined
                    :target="($action['external'] ?? false) ? '_blank' : null">
                    {{ $action['label'] }}
                </x-filament::button>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
