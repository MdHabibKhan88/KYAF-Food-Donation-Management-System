@extends('layouts.public')
@section('title', 'খাবার খুঁজুন')
@section('head')<h1>খাবার খুঁজুন</h1><p class="mb-0 opacity-75">আপনার এলাকার চলমান ও আসন্ন খাদ্য বিতরণ কেন্দ্র</p>@endsection
@section('content')
<div class="container">
  <form method="get" class="card mb-4"><div class="card-body">
    @include('partials.location-picker', ['name' => 'thana_id', 'selected' => request('thana_id'), 'district' => request('district_id'), 'upazila' => request('upazila_id'), 'required' => false])
    <button class="btn btn-kyaf" type="submit"><i class="bi bi-search me-1"></i> খুঁজুন</button>
    <a href="{{ route('find-food') }}" class="btn btn-light">সব দেখুন</a>
  </div></form>

  @forelse ($batches as $b)
    @if ($loop->first)<div class="row g-3">@endif
    <div class="col-md-6 col-lg-4"><div class="card h-100"><div class="card-body">
      <h5 class="mb-1"><i class="bi bi-geo-alt-fill text-success me-1"></i> {{ $b->point_name }}</h5>
      <div class="text-muted small mb-2">{{ $b->point_address }}</div>
      <div class="small mb-1"><i class="bi bi-pin-map me-1"></i> {{ $b->thana->name_bn }}, {{ $b->thana->upazila->name_bn }}, {{ $b->thana->upazila->district->name_bn }}</div>
      <div class="small mb-1"><i class="bi bi-clock me-1"></i> {{ bst($b->scheduled_start) }} — {{ bst($b->scheduled_end, 'h:i A') }}</div>
      <div class="small"><i class="bi bi-basket me-1"></i> প্রায় {{ bn_digits($b->meals_planned) }} মিল @if($b->status === 'Ongoing')<span class="badge text-bg-success ms-1">চলমান</span>@endif</div>
    </div></div></div>
    @if ($loop->last)</div><div class="mt-4">{{ $batches->links() }}</div>@endif
  @empty
    <div class="card"><div class="empty-state"><i class="bi bi-basket"></i><h5>এই মুহূর্তে কোনো বিতরণ কেন্দ্র পাওয়া যায়নি</h5><p class="mb-0">অন্য এলাকা নির্বাচন করুন অথবা কিছুক্ষণ পরে আবার দেখুন।</p></div></div>
  @endforelse
</div>
@endsection
