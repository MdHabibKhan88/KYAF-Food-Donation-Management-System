@extends('layouts.public')
@section('title', 'সেশন মেয়াদোত্তীর্ণ')
@section('content')
<div class="container py-5 text-center"><div class="display-1 fw-bold text-success">419</div><h2>সেশন মেয়াদোত্তীর্ণ</h2><p class="text-muted">পাতাটি অনেকক্ষণ খোলা ছিল। অনুগ্রহ করে পাতাটি রিফ্রেশ করে আবার চেষ্টা করুন।</p>
<a href="{{ url('/') }}" class="btn btn-kyaf">হোমে ফিরে যান</a> @auth<a href="{{ route('dashboard') }}" class="btn btn-outline-kyaf">ড্যাশবোর্ড</a>@endauth</div>
@endsection
