<ul class="timeline">
  @foreach ($donation->histories as $h)
    <li>
      <div><strong>{{ __('kyaf.status.'.$h->to_status) }}</strong> <small class="text-muted">— {{ bst($h->changed_at) }}</small></div>
      <div class="small text-muted">@if($h->from_status){{ __('kyaf.status.'.$h->from_status) }} → @endif{{ __('kyaf.status.'.$h->to_status) }} · {{ optional($h->changer)->full_name ?? 'সিস্টেম' }}</div>
      @if($h->remarks)<div class="small">{{ $h->remarks }}</div>@endif
    </li>
  @endforeach
</ul>
