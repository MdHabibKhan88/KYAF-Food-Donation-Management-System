@php
  $steps = ['Submitted', 'Assigned', 'Verified', 'Approved', 'Collected', 'Distributed', 'Completed'];
  $idx = array_search($donation->status, $steps);
@endphp
<div class="progress-steps">
  @foreach ($steps as $i => $st)
    <span class="st {{ $idx !== false && $i < $idx ? 'done' : '' }} {{ $idx !== false && $i === $idx ? 'now' : '' }}">{{ __('kyaf.status.'.$st) }}</span>
  @endforeach
  @if ($idx === false)<span class="st bg-{{ __('kyaf.status_color.'.$donation->status) }} text-white">{{ __('kyaf.status.'.$donation->status) }}</span>@endif
</div>
