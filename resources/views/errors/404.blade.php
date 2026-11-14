@extends('layouts.public')
@section('title', 'পাতাটি পাওয়া যায়নি')
@section('content')
<div class="container py-5 text-center"><div class="display-1 fw-bold text-success">404</div><h2>পাতাটি পাওয়া যায়নি</h2><p class="text-muted">আপনি যে পাতাটি খুঁজছেন তা নেই বা সরানো হয়েছে।</p>
<a href="{{ url('/') }}" class="btn btn-kyaf">হোমে ফিরে যান</a> @auth<a href="{{ route('dashboard') }}" class="btn btn-outline-kyaf">ড্যাশবোর্ড</a>@endauth</div>
@endsection
