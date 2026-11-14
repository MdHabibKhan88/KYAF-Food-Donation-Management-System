@extends('layouts.public')
@section('title', 'পাসওয়ার্ড রিসেট')
@section('head')<h1>পাসওয়ার্ড ভুলে গেছেন?</h1>@endsection
@section('content')
<div class="container"><div class="auth-card"><div class="card"><div class="card-body p-4">
  <p class="text-muted">আপনার ইমেইল বা মোবাইল নম্বর দিন। আমরা একটি রিসেট লিংক পাঠাব (৩০ মিনিট বৈধ, একবার ব্যবহারযোগ্য)।</p>
  <form method="post" action="{{ route('password.send') }}" novalidate>@csrf
    <x-input name="identifier" label="ইমেইল অথবা মোবাইল নম্বর" required autofocus />
    <button class="btn btn-kyaf w-100" type="submit">রিসেট লিংক পাঠান</button>
  </form>
  <p class="text-center mt-3 mb-0"><a href="{{ route('login') }}">← লগইনে ফিরে যান</a></p>
</div></div></div></div>
@endsection
