@extends('layouts.dashboard')
@section('title', 'নতুন অ্যাকাউন্ট')
@section('content')
@php $titles = ['admin' => 'নতুন অ্যাডমিন', 'manager' => 'নতুন এরিয়া ম্যানেজার', 'staff' => 'সরাসরি স্টাফ তৈরি (ওভাররাইড)']; @endphp
<h1 class="dash-title">{{ $titles[$type] }}</h1>
<div class="card" style="max-width:820px"><div class="card-body">
  <form method="post" action="{{ route('admin.users.store', $type) }}" novalidate>@csrf
    <div class="row">
      <x-input class="col-md-6" name="full_name" label="পূর্ণ নাম" required maxlength="100" />
      <x-input class="col-md-6" name="email" type="email" label="ইমেইল" required maxlength="150" />
      <x-input class="col-md-6" name="phone" type="tel" label="মোবাইল নম্বর" required inputmode="numeric" bn placeholder="01XXXXXXXXX" />
      @if($type === 'manager')<x-input class="col-md-6" name="nid_number" label="জাতীয় পরিচয়পত্র নম্বর" required inputmode="numeric" bn maxlength="17" help="১০, ১৩ বা ১৭ অঙ্ক" />
        <x-select class="col-md-12" name="area_id" label="সেবা এলাকা নিয়োগ (ঐচ্ছিক)" :options="$freeAreas->pluck('area_name', 'area_id')->all()" help="শুধু ম্যানেজারবিহীন সক্রিয় এলাকা দেখানো হচ্ছে। পরেও নিয়োগ দেওয়া যাবে।" />@endif
      @if($type === 'staff')<x-select class="col-md-6" name="manager_id" label="এরিয়া ম্যানেজারের দল" required :options="$managers->mapWithKeys(fn ($m) => [$m->manager_id => $m->user->full_name.' — '.optional($m->serviceArea)->area_name.' ('.bn_digits($m->activeStaffCount()).'/'.bn_digits(\App\Services\TeamService::capacity()).')'])->all()" />
        <x-textarea class="col-md-12" name="reason" label="সরাসরি তৈরির কারণ" required rows="2" maxlength="300" help="এটি ওভাররাইড হিসেবে অডিট লগে নথিভুক্ত হবে। দলে পাঁচজনের বেশি সক্রিয় স্টাফ রাখা যাবে না।" />@endif
    </div>
    <div class="row"><div class="col-md-6"><x-password name="password" label="প্রাথমিক পাসওয়ার্ড" :rules="true" /></div><div class="col-md-6"><x-password name="password_confirmation" label="পাসওয়ার্ড নিশ্চিত করুন" /></div></div>
    <button class="btn btn-accent" type="submit"><i class="bi bi-person-check me-1"></i> অ্যাকাউন্ট তৈরি করুন</button> <a href="{{ route('admin.users') }}" class="btn btn-light">বাতিল</a>
  </form>
</div></div>
@endsection
