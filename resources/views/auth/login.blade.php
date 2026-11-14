@extends('layouts.public')
@php
  $meta = ['admin' => ['অ্যাডমিন লগইন', 'bi-shield-lock'], 'manager' => ['এরিয়া ম্যানেজার লগইন', 'bi-diagram-3'], 'staff' => ['স্টাফ লগইন', 'bi-person-badge'], 'donor' => ['দাতা লগইন', 'bi-heart']][$slug];
@endphp
@section('title', $meta[0])
@section('head')<h1><i class="bi {{ $meta[1] }} me-2"></i>{{ $meta[0] }}</h1>@endsection
@section('content')
<div class="container"><div class="auth-card"><div class="card"><div class="card-body p-4">
  <form method="post" action="{{ route('login.store', $slug) }}" novalidate>@csrf
    <x-input name="identifier" label="ইমেইল অথবা মোবাইল নম্বর" required autocomplete="username" autofocus placeholder="example@mail.com বা 01XXXXXXXXX" />
    <x-password name="password" label="পাসওয়ার্ড" :rules="false" autocomplete="current-password" />
    <button class="btn btn-kyaf w-100 btn-lg" type="submit"><i class="bi bi-box-arrow-in-right me-1"></i> লগইন</button>
  </form>
  <div class="d-flex justify-content-between mt-3 small">
    <a href="{{ route('password.forgot') }}">পাসওয়ার্ড ভুলে গেছেন?</a>
    @if($slug === 'donor')<a href="{{ route('register') }}">নতুন দাতা নিবন্ধন</a>@elseif($slug === 'staff')<a href="{{ route('staff.apply') }}">স্টাফ হতে আবেদন</a>@endif
  </div>
  <hr><div class="text-center small text-muted">অন্য ভূমিকায় লগইন করুন:
    @foreach(['admin'=>'অ্যাডমিন','manager'=>'ম্যানেজার','staff'=>'স্টাফ','donor'=>'দাতা'] as $k=>$v) @if($k !== $slug)<a href="{{ route('login.show', $k) }}" class="ms-1">{{ $v }}</a>@endif @endforeach</div>
</div></div></div></div>
@endsection
