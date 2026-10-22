@props(['series' => [], 'max' => 0.0, 'currency' => 'USD', 'total' => 0.0, 'orders' => 0, 'range' => 30])

@php
    $points = count($series);
    $labelEvery = max(1, (int) ceil($points / 7));
@endphp

<div class="px-5 pb-5 pt-4">
    <div class="flex items-end gap-0.5" style="height: 10rem;" role="img"
        aria-label="Recognized revenue over the last {{ $points }} days: {{ \App\Support\Money::formatWithCode($total, $currency) }} across {{ $orders }} {{ Str::plural('order', $orders) }}.">
        @foreach ($series as $index => $point)
            @php($height = $max > 0 ? max(1.5, round(($point['total'] / $max) * 100, 2)) : 1.5)
            <div class="group relative flex h-full flex-1 items-end">
                <div class="w-full rounded-t bg-slate-900/80 transition-colors duration-150 group-hover:bg-slate-900" style="height: {{ $height }}%;"></div>

                <span class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-1 hidden -translate-x-1/2 whitespace-nowrap rounded-md bg-slate-900 px-2 py-1 text-[11px] font-medium text-white group-hover:block">
                    {{ $point['label'] }} · {{ \App\Support\Money::formatWithCode($point['total'], $currency) }} · {{ $point['orders'] }} {{ Str::plural('order', $point['orders']) }}
                </span>
            </div>
        @endforeach
    </div>

    <div class="mt-2 flex gap-0.5" aria-hidden="true">
        @foreach ($series as $index => $point)
            <div class="flex-1 text-center text-[10px] text-slate-400">
                @if ($index % $labelEvery === 0 || $index === $points - 1)
                    {{ $point['label'] }}
                @endif
            </div>
        @endforeach
    </div>

    <table class="sr-only">
        <caption>Recognized revenue by day</caption>
        <thead>
            <tr>
                <th scope="col">Date</th>
                <th scope="col">Orders</th>
                <th scope="col">Revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($series as $point)
                <tr>
                    <td>{{ $point['date'] }}</td>
                    <td>{{ $point['orders'] }}</td>
                    <td>{{ \App\Support\Money::formatWithCode($point['total'], $currency) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
