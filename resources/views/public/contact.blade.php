@extends('layouts.public')
@section('title', 'যোগাযোগ')
@section('head')<h1>যোগাযোগ করুন</h1>@endsection
@section('content')
<div class="container"><div class="row g-4">
  <div class="col-lg-4"><div class="card h-100"><div class="card-body">
    <h5 class="mb-3">আমাদের ঠিকানা</h5>
    <p><i class="bi bi-telephone-fill text-success me-2"></i><strong>মোবাইল:</strong><br><a href="tel:{{ setting('CONTACT_PHONE') }}">{{ bn_digits(setting('CONTACT_PHONE')) }}</a></p>
    <p><i class="bi bi-envelope-fill text-success me-2"></i><strong>ইমেইল:</strong><br>{{ setting('CONTACT_EMAIL') }}</p>
    <p class="mb-0"><i class="bi bi-geo-alt-fill text-success me-2"></i><strong>ঠিকানা:</strong><br>{{ setting('CONTACT_ADDRESS') }}</p>
  </div></div></div>
  <div class="col-lg-8"><div class="card"><div class="card-body p-4">
    <h5 class="mb-3">বার্তা পাঠান</h5>
    <form method="post" action="{{ route('contact.store') }}" novalidate>@csrf
      <div class="row">
        <x-input class="col-md-6" name="sender_name" label="আপনার নাম" required maxlength="100" />
        <x-input class="col-md-6" name="email" type="email" label="ইমেইল" required maxlength="150" />
        <x-input class="col-md-6" name="phone" type="tel" label="মোবাইল নম্বর" inputmode="numeric" placeholder="01XXXXXXXXX" bn help="ঐচ্ছিক। যেমন: 01712345678" />
        <x-input class="col-md-6" name="subject" label="বিষয়" required maxlength="150" />
      </div>
      <x-textarea name="message_body" label="আপনার বার্তা" rows="5" required maxlength="2000" />
      <x-input name="captcha" :label="'নিরাপত্তা প্রশ্ন: '.$captcha" required inputmode="numeric" bn autocomplete="off" help="স্প্যাম রোধে সঠিক যোগফল লিখুন।" />
      <button class="btn btn-kyaf" type="submit"><i class="bi bi-send me-1"></i> বার্তা পাঠান</button>
    </form>
  </div></div></div>
</div></div>
@endsection
