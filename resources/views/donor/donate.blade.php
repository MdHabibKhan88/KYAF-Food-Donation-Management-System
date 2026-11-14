@extends('layouts.dashboard')
@section('title', 'খাবার দান করুন')
@section('content')
<h1 class="dash-title">খাবার দান করুন</h1>
@if (session('duplicate'))
  <div class="alert alert-warning"><strong><i class="bi bi-exclamation-triangle me-1"></i> সতর্কতা:</strong> গত ১০ মিনিটে আপনি একই খাবার, মেয়াদ ও ঠিকানায় একটি দান জমা দিয়েছেন। এটি আবার জমা দিতে চাইলে নিচের বাক্সে টিক দিয়ে ছবি (যদি থাকে) আবার যুক্ত করে জমা দিন।
    <div class="form-check mt-2"><input class="form-check-input" type="checkbox" form="donateForm" name="confirm_duplicate" value="1" id="confirmDup"><label class="form-check-label" for="confirmDup">হ্যাঁ, এটি আলাদা একটি দান — আবার জমা দিতে চাই।</label></div></div>
@endif
<form id="donateForm" method="post" action="{{ route('donor.donations.store') }}" enctype="multipart/form-data" novalidate>@csrf
<div class="row g-3"><div class="col-lg-8">
  <div class="card mb-3"><div class="card-header">১. খাবারের তথ্য</div><div class="card-body"><div class="row">
    <x-input class="col-md-6" name="food_name" label="খাবারের নাম" required maxlength="100" placeholder="যেমন: খিচুড়ি, বিরিয়ানি" />
    <x-select class="col-md-6" name="food_category" label="খাবারের ধরন" required :options="__('kyaf.category')" />
    <x-input class="col-md-6" name="quantity_meals" label="খাবারের পরিমাণ (কত জন মানুষের খাবার)" required inputmode="numeric" bn placeholder="যেমন: ৫০" help="পূর্ণ সংখ্যা লিখুন (১ বা তার বেশি)" />
    <div class="col-md-6"><div class="row">
      <x-input class="col-7" name="expiry_date" type="date" label="মেয়াদ উত্তীর্ণের তারিখ" required :min="now()->timezone('Asia/Dhaka')->toDateString()" />
      <x-input class="col-5" name="expiry_time" type="time" label="সময়" required />
    </div></div>
    <div class="col-12"><div class="form-text mb-3 mt-n2">খাবারের মেয়াদ এখন থেকে কমপক্ষে {{ bn_digits((int) setting('MIN_EXPIRY_LEAD_HOURS', 2)) }} ঘণ্টা পরে হতে হবে।</div></div>
    <x-textarea name="description" label="অতিরিক্ত বিবরণ" maxlength="500" help="ঐচ্ছিক — সর্বোচ্চ ৫০০ অক্ষর (যেমন: রান্নার সময়, উপকরণ)" />
    <div class="mb-0"><label class="form-label fw-semibold" for="photo">খাবারের ছবি</label><input class="form-control @error('photo') is-invalid @enderror" type="file" id="photo" name="photo" accept="image/jpeg,image/png"><div class="form-text">ঐচ্ছিক — JPG বা PNG, সর্বোচ্চ ৫ MB</div>@error('photo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
  </div></div></div>

  <div class="card mb-3"><div class="card-header">২. পিকআপের স্থান</div><div class="card-body">
    <div data-loc-modal="areaModal">
      <label class="form-label fw-semibold">পিকআপ এলাকা (সেবা এলাকা)<span class="req">*</span></label>
      <input type="hidden" name="pickup_thana_id" value="{{ old('pickup_thana_id') }}" data-loc-target>
      <div class="loc-summary mb-2 {{ old('pickup_thana_id') ? '' : 'd-none' }}"><i class="bi bi-geo-alt-fill text-success me-1"></i><span data-loc-label></span></div>
      <button type="button" class="btn btn-outline-kyaf" data-bs-toggle="modal" data-bs-target="#areaModal"><i class="bi bi-map me-1"></i> এলাকা নির্বাচন করুন</button>
      @error('pickup_thana_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
    <x-input class="mt-3" name="pickup_address" label="পিকআপের বিস্তারিত ঠিকানা" required maxlength="250" :value="$address" help="বাড়ি/রাস্তা/ল্যান্ডমার্ক সহ" />
    <x-input name="contact_phone" type="tel" label="যোগাযোগের মোবাইল নম্বর" required inputmode="numeric" bn :value="\App\Support\Phone::display($phone) === '—' ? '' : '0'.substr($phone, 4)" help="স্টাফ এই নম্বরে যোগাযোগ করবেন" />
  </div></div>
  <button type="submit" class="btn btn-accent btn-lg"><i class="bi bi-send-check me-1"></i> দান জমা দিন</button>
  <a href="{{ route('donor.dashboard') }}" class="btn btn-light btn-lg">বাতিল</a>
</div>
<div class="col-lg-4"><div class="card"><div class="card-header">নির্দেশনা</div><div class="card-body small">
  <ul class="ps-3 mb-0"><li>শুধু নিরাপদ ও খাওয়ার উপযোগী খাবার দিন।</li><li>পরিমাণ বলতে কত জন মানুষ খেতে পারবে তা বোঝায়।</li><li>মেয়াদ উত্তীর্ণের সময় সঠিকভাবে দিন; মেয়াদ শেষ হলে খাবার গ্রহণ করা হবে না।</li><li>জমা দেওয়ার পর একটি ট্র্যাকিং কোড পাবেন।</li><li>সংগ্রহের আগে পর্যন্ত আপনি দান বাতিল করতে পারবেন।</li></ul>
</div></div></div></div>
</form>

<div class="modal fade" id="areaModal" tabindex="-1" aria-labelledby="areaModalLabel" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-xl"><div class="modal-content">
  <div class="modal-header"><h5 class="modal-title" id="areaModalLabel"><i class="bi bi-map me-1"></i> পিকআপ এলাকা নির্বাচন করুন</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="বন্ধ করুন"></button></div>
  <div class="modal-body">
    <p class="text-muted small">বিভাগ → জেলা → উপজেলা → থানা ক্রমে নির্বাচন করুন। শুধু আমাদের সেবা এলাকার অন্তর্ভুক্ত থানা নির্বাচন করা যাবে।</p>
    @include('partials.location-picker', ['name' => 'modal_thana', 'noName' => true, 'required' => true, 'coveredOnly' => true, 'selected' => old('pickup_thana_id')])
    <div class="alert alert-warning d-none" data-loc-warning><i class="bi bi-exclamation-triangle me-1"></i> এই থানা এখনো আমাদের সেবা এলাকার অন্তর্ভুক্ত নয়।</div>
  </div>
  <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">বন্ধ করুন</button><button type="button" class="btn btn-accent btn-kyaf" data-loc-confirm disabled><i class="bi bi-check2 me-1"></i> এই এলাকা নিশ্চিত করুন</button></div>
</div></div></div>
@endsection
