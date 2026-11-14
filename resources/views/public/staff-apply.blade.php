@extends('layouts.public')
@section('title', 'স্টাফ হোন')
@section('head')<h1>স্টাফ হওয়ার আবেদন</h1><p class="mb-0 opacity-75">নিজ এলাকায় স্বেচ্ছাসেবী হিসেবে ক্ষুধার্ত মানুষের পাশে দাঁড়ান</p>@endsection
@section('content')
<div class="container"><div class="auth-card wide"><div class="card"><div class="card-body p-4">
  <div class="alert alert-light border small"><i class="bi bi-info-circle me-1"></i> আবেদনকারীর বয়স কমপক্ষে ১৮ বছর হতে হবে। একটি জাতীয় পরিচয়পত্র নম্বর দিয়ে একটি চলমান আবেদনই করা যায়। অনুমোদনের পর আপনাকে একটি আমন্ত্রণ লিংক (৭২ ঘণ্টা বৈধ) দেওয়া হবে।</div>
  <form method="post" action="{{ route('staff.apply.store') }}" enctype="multipart/form-data" novalidate>@csrf
    <div class="row">
      <x-input class="col-md-6" name="applicant_name" label="পূর্ণ নাম" required maxlength="100" />
      <x-input class="col-md-6" name="email" type="email" label="ইমেইল" required maxlength="150" />
      <x-input class="col-md-6" name="phone" type="tel" label="মোবাইল নম্বর" required inputmode="numeric" placeholder="01XXXXXXXXX" bn />
      <x-input class="col-md-6" name="nid_number" label="জাতীয় পরিচয়পত্র নম্বর" required inputmode="numeric" bn help="১০, ১৩ বা ১৭ অঙ্ক" maxlength="17" />
      <x-input class="col-md-6" name="date_of_birth" type="date" label="জন্ম তারিখ" required :max="now()->subYears(18)->toDateString()" />
      <x-select class="col-md-6" name="education_level" label="শিক্ষাগত যোগ্যতা" required :options="array_combine(__('kyaf.education'), __('kyaf.education'))" />
    </div>
    <label class="form-label fw-semibold">আপনার বাসস্থানের থানা <span class="req">*</span></label>
    @include('partials.location-picker', ['name' => 'thana_id', 'required' => true, 'selected' => null])
    <x-select name="area_id" label="যে সেবা এলাকায় কাজ করতে চান" required :options="$areas->mapWithKeys(fn ($a) => [$a->area_id => $a->area_name.' ('.$a->district->name_bn.')'])->all()" help="শুধু সক্রিয় ম্যানেজার আছে এমন এলাকা দেখানো হচ্ছে।" />
    <x-textarea name="experience_summary" label="অভিজ্ঞতা ও অতিরিক্ত তথ্য" maxlength="1000" help="ঐচ্ছিক, সর্বোচ্চ ১০০০ অক্ষর" />
    <div class="mb-3"><label class="form-label fw-semibold" for="cv">সিভি (PDF)</label><input class="form-control @error('cv') is-invalid @enderror" type="file" id="cv" name="cv" accept="application/pdf"><div class="form-text">ঐচ্ছিক। শুধু PDF, সর্বোচ্চ ২ MB।</div>@error('cv')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
    <button class="btn btn-kyaf btn-lg" type="submit"><i class="bi bi-send-check me-1"></i> আবেদন জমা দিন</button>
  </form>
</div></div></div></div>
@endsection
