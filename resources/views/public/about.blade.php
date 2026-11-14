@extends('layouts.public')
@section('title', 'আমাদের সম্পর্কে')
@section('head')<h1>আমাদের সম্পর্কে</h1>@endsection
@section('content')
<div class="container"><div class="row g-4">
  <div class="col-lg-8"><div class="card"><div class="card-body p-4">
    <h4>আমাদের লক্ষ্য</h4>
    <p>খাদ্য অপচয় কমানো এবং ক্ষুধার্ত মানুষের কাছে নিরাপদ খাবার পৌঁছে দেওয়া — এই দুটি লক্ষ্য নিয়ে KYAF খাদ্য দান ব্যবস্থাপনা প্ল্যাটফর্ম কাজ করে। প্রতিটি দান জমা থেকে বিতরণ পর্যন্ত ট্র্যাক করা হয়, যাতে দাতা জানতে পারেন তাঁর দেওয়া খাবার কোথায় পৌঁছাল।</p>
    <h4 class="mt-4">আমাদের কাঠামো</h4>
    <ul>
      <li><strong>সেবা এলাকা:</strong> প্রতিটি সেবা এলাকা এক বা একাধিক থানা নিয়ে গঠিত এবং একজন এরিয়া ম্যানেজার দ্বারা পরিচালিত।</li>
      <li><strong>স্টাফ দল:</strong> প্রতিটি এরিয়া ম্যানেজারের অধীনে পাঁচজন স্বেচ্ছাসেবী স্টাফ থাকেন।</li>
      <li><strong>নিরাপত্তা:</strong> মেয়াদোত্তীর্ণ বা অনিরাপদ খাবার কখনোই সংগ্রহ বা বিতরণ করা হয় না।</li>
      <li><strong>স্বচ্ছতা:</strong> প্রতিটি পদক্ষেপ অডিট লগে সংরক্ষিত হয়।</li>
    </ul>
    <h4 class="mt-4">দান প্রক্রিয়া</h4>
    <p class="mb-0">জমা দেওয়া → স্টাফ নিয়োগ → যাচাই → অনুমোদন → সংগ্রহ → বিতরণ → সম্পন্ন।</p>
  </div></div></div>
  <div class="col-lg-4"><div class="card"><div class="card-body"><h5>যোগাযোগ</h5>
    <p class="mb-1"><i class="bi bi-telephone me-1"></i> {{ bn_digits(setting('CONTACT_PHONE')) }}</p>
    <p class="mb-1"><i class="bi bi-envelope me-1"></i> {{ setting('CONTACT_EMAIL') }}</p>
    <p><i class="bi bi-geo-alt me-1"></i> {{ setting('CONTACT_ADDRESS') }}</p>
    <a href="{{ route('contact') }}" class="btn btn-kyaf w-100">বার্তা পাঠান</a></div></div></div>
</div></div>
@endsection
