@extends('layouts.public')
@section('title', 'দাতা নিবন্ধন')
@section('head')<h1>দাতা নিবন্ধন</h1><p class="mb-0 opacity-75">কয়েক মিনিটেই অ্যাকাউন্ট খুলুন এবং খাবার দান শুরু করুন</p>@endsection
@section('content')
<div class="container"><div class="auth-card wide"><div class="card"><div class="card-body p-4">
  <form method="post" action="{{ route('register.store') }}" novalidate>@csrf
    <h5 class="mb-3">ব্যক্তিগত তথ্য</h5>
    <div class="row">
      <x-input class="col-md-6" name="full_name" label="পূর্ণ নাম" required maxlength="100" autocomplete="name" />
      <x-input class="col-md-6" name="email" type="email" label="ইমেইল ঠিকানা" required maxlength="150" autocomplete="email" />
      <x-input class="col-md-6" name="phone" type="tel" label="মোবাইল নম্বর" required inputmode="numeric" bn placeholder="01XXXXXXXXX" help="01XXXXXXXXX, +8801XXXXXXXXX বা 8801XXXXXXXXX ফরম্যাট গ্রহণযোগ্য" autocomplete="tel" />
      <x-select class="col-md-6" name="donor_type" label="দাতার ধরন" required :options="__('kyaf.donor_type')" value="Individual" />
      <x-input class="col-md-12" name="organization_name" label="রেস্তোরাঁ / প্রতিষ্ঠানের নাম" maxlength="120" help="রেস্তোরাঁ বা প্রতিষ্ঠান হলে আবশ্যক" />
    </div>
    <h5 class="mb-2 mt-2">ঠিকানা</h5>
    @include('partials.location-picker', ['name' => 'thana_id', 'required' => true, 'selected' => null])
    <x-input name="address_line" label="বিস্তারিত ঠিকানা (বাড়ি/রাস্তা/এলাকা)" required maxlength="250" />
    <h5 class="mb-3 mt-2">পাসওয়ার্ড</h5>
    <div class="row">
      <div class="col-md-6"><x-password name="password" label="পাসওয়ার্ড" :rules="true" /></div>
      <div class="col-md-6"><x-password name="password_confirmation" label="পাসওয়ার্ড নিশ্চিত করুন" /></div>
    </div>
    <div class="form-check mb-3">
      <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" name="terms" id="terms" value="1" @checked(old('terms')) required>
      <label class="form-check-label" for="terms">আমি শর্তাবলি ও গোপনীয়তা নীতিতে সম্মত।<span class="req">*</span></label>
      @error('terms')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <button class="btn btn-kyaf btn-lg" type="submit"><i class="bi bi-person-plus me-1"></i> নিবন্ধন সম্পন্ন করুন</button>
    <span class="ms-2">ইতোমধ্যে অ্যাকাউন্ট আছে? <a href="{{ route('login.show', 'donor') }}">লগইন করুন</a></span>
  </form>
</div></div></div></div>
@endsection
