@extends('layouts.dashboard')
@section('title', 'বিজ্ঞপ্তি')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="dash-title mb-0">বিজ্ঞপ্তি</h1>
  <form method="post" action="{{ route('notifications.readAll') }}">@csrf<button class="btn btn-outline-accent btn-sm" type="submit"><i class="bi bi-check2-all me-1"></i> সব পঠিত করুন</button></form></div>
<div class="card">
  @forelse($items as $n)
    @if($loop->first)<div class="list-group list-group-flush">@endif
    <div class="list-group-item d-flex justify-content-between gap-3 {{ $n->is_read ? '' : 'bg-light' }}">
      <div><div class="{{ $n->is_read ? '' : 'fw-bold' }}">@unless($n->is_read)<span class="badge text-bg-danger me-1">নতুন</span>@endunless{{ $n->title }}</div><div class="small">{{ $n->message }}</div><div class="small text-muted">{{ bst($n->created_at) }}</div></div>
      <div class="d-flex gap-1 align-items-start">
        @unless($n->is_read)<form method="post" action="{{ route('notifications.read', $n) }}">@csrf<button class="btn btn-sm btn-light" title="পঠিত করুন" aria-label="পঠিত করুন" type="submit"><i class="bi bi-check2"></i></button></form>@endunless
        <form method="post" action="{{ route('notifications.destroy', $n) }}" data-confirm="বিজ্ঞপ্তিটি মুছে ফেলবেন?">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger" title="মুছুন" aria-label="মুছুন" type="submit"><i class="bi bi-trash"></i></button></form>
      </div>
    </div>
    @if($loop->last)</div>@endif
  @empty
    <div class="empty-state"><i class="bi bi-bell-slash"></i>কোনো বিজ্ঞপ্তি নেই।</div>
  @endforelse
</div>
<div class="mt-3">{{ $items->links() }}</div>
@endsection
