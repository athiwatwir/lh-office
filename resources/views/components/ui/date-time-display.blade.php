@props([
    'datetime' => null,
    'format' => 'd/m/Y H:i',
    'showRelative' => true,
    'emptyText' => '-',
    'layout' => 'stacked',
    'prefix' => null,
])

@php
    use App\View\Components\ui\DateTimeDisplay;
    use Carbon\Carbon;
    use Carbon\CarbonInterface;

    $date = null;

    if (! blank($datetime)) {
        if ($datetime instanceof CarbonInterface) {
            $date = Carbon::instance($datetime);
        } else {
            try {
                $date = Carbon::parse($datetime);
            } catch (\Throwable) {
                $date = null;
            }
        }
    }

    $formattedDate = $date?->format($format);
    $relativeText = $date && $showRelative ? DateTimeDisplay::toThaiRelative($date) : null;
@endphp

@if ($formattedDate)
    @if ($layout === 'inline')
        <span {{ $attributes->merge(['class' => 'inline-flex flex-wrap items-center gap-2']) }}>
            @if ($prefix)
                <span class="text-sm text-gray-500">{{ $prefix }}</span>
            @endif

            <span class="whitespace-nowrap text-theme-sm text-gray-500 text-xs">{{ $formattedDate }}</span>

            @if ($relativeText)
                <x-ui.badge variant="light" color="light" size="sm">{{ $relativeText }}</x-ui.badge>
            @endif
        </span>
    @else
        <div {{ $attributes->merge(['class' => 'flex flex-col items-start gap-1']) }}>
            <span class="whitespace-nowrap text-theme-sm text-gray-500 text-xs">{{ $formattedDate }}</span>

            @if ($relativeText)
                <x-ui.badge variant="light" color="light" size="sm">{{ $relativeText }}</x-ui.badge>
            @endif
        </div>
    @endif
@else
    <span {{ $attributes->merge(['class' => 'text-theme-sm text-gray-500 text-xs']) }}>{{ $emptyText }}</span>
@endif
