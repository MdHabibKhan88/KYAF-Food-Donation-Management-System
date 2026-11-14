@extends('layouts.public')
@section('title', 'প্রবেশাধিকার নেই')
@section('content')
<div class="container py-5 text-center"><div class="display-1 fw-bold text-success">403</div><h2>প্রবেশাধিকার নেই</h2><p class="text-muted">এই পাতাটি দেখার অনুমতি আপনার নেই। এই চেষ্টাটি নিরাপত্তা লগে নথিভুক্ত হয়েছে।</p>
<a href="{{ url('/') }}" class="btn btn-kyaf">হোমে ফিরে যান</a> @auth<a href="{{ route('dashboard') }}" class="btn btn-outline-kyaf">ড্যাশবোর্ড</a>@endauth</div>
@endsection
