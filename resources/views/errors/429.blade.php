@extends('layouts.public')
@section('title', 'অনেক বেশি অনুরোধ')
@section('content')
<div class="container py-5 text-center"><div class="display-1 fw-bold text-success">429</div><h2>অনেক বেশি অনুরোধ</h2><p class="text-muted">অল্প সময়ে অনেকবার চেষ্টা করা হয়েছে। কিছুক্ষণ পরে আবার চেষ্টা করুন।</p>
<a href="{{ url('/') }}" class="btn btn-kyaf">হোমে ফিরে যান</a> @auth<a href="{{ route('dashboard') }}" class="btn btn-outline-kyaf">ড্যাশবোর্ড</a>@endauth</div>
@endsection
