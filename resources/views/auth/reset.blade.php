@extends('layouts.public')
@section('title', 'নতুন পাসওয়ার্ড')
@section('head')<h1>নতুন পাসওয়ার্ড দিন</h1>@endsection
@section('content')
<div class="container"><div class="auth-card"><div class="card"><div class="card-body p-4">
  @if(! $valid)
    <div class="alert alert-danger mb-3">এই লিংকটি অবৈধ, ব্যবহৃত অথবা মেয়াদোত্তীর্ণ।</div>
    <a href="{{ route('password.forgot') }}" class="btn btn-kyaf">নতুন লিংক চান</a>
  @else
    <form method="post" action="{{ route('password.update', $token) }}" novalidate>@csrf
      <x-password name="password" label="নতুন পাসওয়ার্ড" :rules="true" help="সর্বশেষ তিনটি পাসওয়ার্ড আবার ব্যবহার করা যাবে না।" />
      <x-password name="password_confirmation" label="পাসওয়ার্ড নিশ্চিত করুন" />
      <button class="btn btn-kyaf w-100" type="submit">পাসওয়ার্ড পরিবর্তন করুন</button>
    </form>
  @endif
</div></div></div></div>
@endsection
