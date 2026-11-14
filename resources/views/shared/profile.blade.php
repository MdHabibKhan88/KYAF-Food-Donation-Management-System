@extends('layouts.dashboard')
@section('title', 'আমার প্রোফাইল')
@section('content')
<h1 class="dash-title">আমার প্রোফাইল</h1>
<div class="card mb-3">
  <div class="card-header">প্রোফাইল ছবি</div>
  <div class="card-body d-flex flex-wrap align-items-center gap-3">
    @include('partials.avatar', ['user' => $user, 'size' => 96, 'id' => 'avatarPreview'])
    <div class="flex-grow-1">
      <form method="post" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data" class="d-flex flex-wrap gap-2 align-items-center">
        @csrf
        <input type="file" name="photo" id="photoInput" accept="image/jpeg,image/png" class="form-control" style="max-width:320px" required>
        <button class="btn btn-accent" type="submit"><i class="bi bi-upload me-1"></i> ছবি আপলোড করুন</button>
      </form>
      <div class="form-text">শুধু JPG বা PNG, সর্বোচ্চ ২ MB।</div>
      @if($user->profile_photo_path)
        <form method="post" action="{{ route('profile.photo.delete') }}" class="mt-2" data-confirm="প্রোফাইল ছবি মুছে ফেলবেন?">
          @csrf @method('DELETE')
          <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash me-1"></i> ছবি মুছুন</button>
        </form>
      @endif
    </div>
  </div>
 </div>
 <div class="row g-3">
  <div class="col-lg-7"><div class="card"><div class="card-header">প্রোফাইল তথ্য</div><div class="card-body">
    <form method="post" action="{{ route('profile.update') }}" novalidate>@csrf @method('PUT')
      <div class="row">
        <x-input class="col-md-6" name="full_name" label="পূর্ণ নাম" required :value="$user->full_name" maxlength="100" />
        <x-input class="col-md-6" name="email" type="email" label="ইমেইল" required :value="$user->email" maxlength="150" />
        <x-input class="col-md-6" name="phone" type="tel" label="মোবাইল নম্বর" required inputmode="numeric" bn :value="'0'.substr($user->phone, 4)" />
        <div class="col-md-6 mb-3"><label class="form-label fw-semibold">ভূমিকা</label><input class="form-control" value="{{ __('kyaf.role.'.$user->roleName()) }}" disabled></div>
      </div>
      @if($user->donor)
        <div class="alert alert-light border small">দাতা আইডি: <strong>{{ $user->donor->donor_id }}</strong> · ধরন: {{ __('kyaf.donor_type.'.$user->donor->donor_type) }}</div>
        @if($user->donor->donor_type !== 'Individual')<x-input name="organization_name" label="প্রতিষ্ঠানের নাম" :value="$user->donor->organization_name" maxlength="120" />@endif
        <label class="form-label fw-semibold">থানা<span class="req">*</span></label>
        @include('partials.location-picker', ['name' => 'thana_id', 'required' => true, 'selected' => $user->donor->thana_id])
        <x-input name="address_line" label="বিস্তারিত ঠিকানা" required :value="$user->donor->address_line" maxlength="250" />
      @endif
      @if($user->manager)<div class="alert alert-light border small">সেবা এলাকা: <strong>{{ optional($user->manager->serviceArea)->area_name ?? 'এখনো নিয়োগ দেওয়া হয়নি' }}</strong></div>@endif
      @if($user->staff)<div class="alert alert-light border small">সেবা এলাকা: <strong>{{ optional($user->staff->manager->serviceArea)->area_name }}</strong> · ম্যানেজার: {{ optional($user->staff->manager->user)->full_name }}</div>@endif
      <button class="btn btn-accent" type="submit"><i class="bi bi-save me-1"></i> সংরক্ষণ করুন</button>
    </form>
  </div></div></div>
  <div class="col-lg-5"><div class="card"><div class="card-header">পাসওয়ার্ড পরিবর্তন</div><div class="card-body">
    <form method="post" action="{{ route('password.change') }}" novalidate>@csrf @method('PUT')
      <x-password name="current_password" label="বর্তমান পাসওয়ার্ড" autocomplete="current-password" />
      <x-password name="password" label="নতুন পাসওয়ার্ড" :rules="true" help="সর্বশেষ তিনটি পাসওয়ার্ড আবার ব্যবহার করা যাবে না।" />
      <x-password name="password_confirmation" label="নতুন পাসওয়ার্ড নিশ্চিত করুন" />
      <button class="btn btn-accent" type="submit"><i class="bi bi-key me-1"></i> পাসওয়ার্ড পরিবর্তন</button>
    </form>
  </div></div></div>
 </div>
 @endsection


 @push('scripts')
 <script>
  document.getElementById('photoInput').addEventListener('change', function () {
    const f = this.files[0];
    if (!f) return;
    const box = document.getElementById('avatarPreview');
    box.innerHTML = '';
    const img = document.createElement('img');
    img.src = URL.createObjectURL(f);
    img.alt = 'প্রিভিউ';
    box.appendChild(img);
  });
 </script>
@endpush