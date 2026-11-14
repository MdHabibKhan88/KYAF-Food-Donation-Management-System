@extends('layouts.public')
@section('title', 'লগইন')
@section('head')<h1>লগইন</h1><p class="mb-0 opacity-75">আপনার ভূমিকা নির্বাচন করুন</p>@endsection
@section('content')
<div class="container"><div class="auth-card wide"><div class="card"><div class="card-body p-4">
  @include('partials.role-tiles')
  <p class="text-center mt-4 mb-0">দাতা হিসেবে নতুন? <a href="{{ route('register') }}" class="fw-bold">নিবন্ধন করুন</a></p>
</div></div></div></div>
@endsection
