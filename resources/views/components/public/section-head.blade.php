@props([
    'block' => null,
    'eyebrow' => null,
    'title' => null,
    'lead' => null,
    'left' => false,
])

@php
    $b = $block instanceof \App\Domains\Cms\Models\ContentBlock ? $block : null;
    $show = $b?->hasSectionHead();
    $e = $show && $b->eyebrow ? $b->eyebrow : $eyebrow;
    $t = $show && $b->title ? $b->title : $title;
    $l = $show && $b->lead ? $b->lead : $lead;
    $appName = \App\Domains\Settings\Models\Setting::schoolName();
    $tx = static fn (?string $v) => $v === null ? null : str_replace(':school', $appName, $v);
@endphp

@if ($e || $t || $l)
    <div class="section-head">
        @if ($e)
            <p class="eyebrow @if ($left) left @endif">{{ $tx($e) }}</p>
        @endif
        @if ($t)
            <h2>{{ $tx($t) }}</h2>
        @endif
        @if ($l)
            <p>{!! $tx($l) !!}</p>
        @endif
    </div>
@endif