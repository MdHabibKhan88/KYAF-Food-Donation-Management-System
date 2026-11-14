@extends('layouts.public')
@section('title', 'সার্ভারে সমস্যা')
@section('content')
<div class="container py-5 text-center"><div class="display-1 fw-bold text-success">500</div><h2>সার্ভারে সমস্যা</h2><p class="text-muted">দুঃখিত, কিছু একটা ভুল হয়েছে। আবার চেষ্টা করুন অথবা পরে আসুন।</p>
<a href="{{ url('/') }}" class="btn btn-kyaf">হোমে ফিরে যান</a> @auth<a href="{{ route('dashboard') }}" class="btn btn-outline-kyaf">ড্যাশবোর্ড</a>@endauth</div>
@endsection
