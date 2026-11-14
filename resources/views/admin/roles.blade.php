@extends('layouts.dashboard')
@section('title', 'ভূমিকা')
@section('content')
<h1 class="dash-title">ভূমিকা (Roles)</h1>
<div class="alert alert-info small"><i class="bi bi-info-circle me-1"></i> প্রতিটি ব্যবহারকারীর ঠিক একটি ভূমিকা থাকে এবং তা পরিবর্তন করা যায় না। ভূমিকা পরিবর্তন করতে হলে নতুন অ্যাকাউন্ট তৈরি করতে হবে।</div>
<div class="row g-3">@foreach($roles as $r)<div class="col-md-6"><div class="card h-100"><div class="card-body"><h5>{{ __('kyaf.role.'.$r->role_name) }} <small class="text-muted">({{ $r->role_name }})</small></h5><p class="text-muted small mb-2">{{ $r->description }}</p>
  <div>মোট অ্যাকাউন্ট: <strong>{{ bn_digits($r->users_count) }}</strong> · সক্রিয়: <strong>{{ bn_digits($r->active_count) }}</strong></div>
  <a class="btn btn-sm btn-outline-accent mt-2" href="{{ route('admin.users', ['role' => $r->role_name]) }}">অ্যাকাউন্ট দেখুন</a></div></div></div>@endforeach</div>
@endsection
