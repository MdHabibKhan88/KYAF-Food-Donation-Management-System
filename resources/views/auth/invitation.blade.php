@extends('layouts.public')
@section('title', 'স্টাফ অ্যাকাউন্ট তৈরি')
@section('head')<h1>স্টাফ অ্যাকাউন্ট তৈরি করুন</h1>@endsection
@section('content')
<div class="container"><div class="auth-card"><div class="card"><div class="card-body p-4">
  @if(! $app)
    <div class="alert alert-danger">আমন্ত্রণ লিংকটি অবৈধ, ব্যবহৃত অথবা মেয়াদোত্তীর্ণ (৭২ ঘণ্টা)। অনুগ্রহ করে এরিয়া ম্যানেজারের সাথে যোগাযোগ করুন।</div>
    <a href="{{ route('home') }}" class="btn btn-kyaf">হোমে ফিরুন</a>
  @else
    <div class="alert alert-success small">আপনার আবেদন অনুমোদিত হয়েছে। সেবা এলাকা: <strong>{{ $app->area->area_name }}</strong></div>
    <dl class="row small"><dt class="col-4">নাম</dt><dd class="col-8">{{ $app->applicant_name }}</dd><dt class="col-4">ইমেইল</dt><dd class="col-8">{{ $app->email }}</dd><dt class="col-4">মোবাইল</dt><dd class="col-8">{{ \App\Support\Phone::display($app->phone) }}</dd></dl>
    <form method="post" action="{{ route('invitation.store', $token) }}" novalidate>@csrf
      <x-password name="password" label="পাসওয়ার্ড তৈরি করুন" :rules="true" />
      <x-password name="password_confirmation" label="পাসওয়ার্ড নিশ্চিত করুন" />
      <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="terms" id="terms" value="1" required><label class="form-check-label" for="terms">আমি শর্তাবলিতে সম্মত।<span class="req">*</span></label></div>
      <button class="btn btn-kyaf w-100" type="submit">অ্যাকাউন্ট তৈরি করুন</button>
    </form>
  @endif
</div></div></div></div>
@endsection
