@extends('layouts.public')
@section('title', 'হোম')
@section('content')
<section class="hero" style="margin-top:-1.5rem">
  <div class="container"><div class="row align-items-center g-4">
    <div class="col-lg-6">
      <span class="badge text-bg-success mb-2">ক্ষুধামুক্ত বাংলাদেশের উদ্যোগ</span>
      <h1 class="mb-3">উদ্বৃত্ত খাবার দিন, একটি মুখে হাসি ফোটান</h1>
      <p class="lead text-muted">আপনার ঘর, রেস্তোরাঁ বা অনুষ্ঠানের বাড়তি খাবার আমাদের স্বেচ্ছাসেবকরা সংগ্রহ করে যাচাই করে ক্ষুধার্ত মানুষের কাছে পৌঁছে দেন — স্বচ্ছভাবে, নিরাপদে।</p>
      <div class="d-flex flex-wrap gap-2 mt-4">
        @auth
          @if(auth()->user()->hasRole('DONOR'))<a href="{{ route('donor.donations.create') }}" class="btn btn-kyaf btn-lg"><i class="bi bi-heart-fill me-1"></i> খাবার দান করুন</a>@else<a href="{{ route('dashboard') }}" class="btn btn-kyaf btn-lg">ড্যাশবোর্ডে যান</a>@endif
        @else
          <a href="{{ route('register') }}" class="btn btn-kyaf btn-lg"><i class="bi bi-heart-fill me-1"></i> দাতা হিসেবে নিবন্ধন করুন</a>
        @endauth
        <a href="{{ route('find-food') }}" class="btn btn-outline-kyaf btn-lg"><i class="bi bi-search me-1"></i> খাবার খুঁজুন</a>
      </div>
    </div>
    <div class="col-lg-6 text-center"><img src="{{ asset('images/hero.svg') }}" class="img-fluid" alt="খাদ্য দানের চিত্র" style="max-height:340px"></div>
  </div></div>
</section>

<section class="container my-5">
  <div class="row g-3">
    <div class="col-md-4"><div class="card stat-card"><div class="num">{{ bn_digits(number_format($stats['donations'])) }}</div><div class="text-muted">সম্পন্ন দান</div></div></div>
    <div class="col-md-4"><div class="card stat-card"><div class="num">{{ bn_digits(number_format($stats['meals'])) }}</div><div class="text-muted">বিতরণকৃত মিল</div></div></div>
    <div class="col-md-4"><div class="card stat-card"><div class="num">{{ bn_digits(number_format($stats['areas'])) }}</div><div class="text-muted">সক্রিয় সেবা এলাকা</div></div></div>
  </div>
</section>

<section class="container my-5">
  <h2 class="text-center mb-4">কীভাবে কাজ করে?</h2>
  <div class="row g-4 text-center">
    @foreach ([['bi-pencil-square','১. দান জমা দিন','খাবারের নাম, পরিমাণ, মেয়াদ ও পিকআপ ঠিকানা দিন।'],['bi-person-check','২. যাচাই','এলাকার স্টাফ খাবারের মান ও পরিমাণ যাচাই করেন।'],['bi-truck','৩. সংগ্রহ','অনুমোদনের পর স্টাফ আপনার ঠিকানা থেকে খাবার সংগ্রহ করেন।'],['bi-people','৪. বিতরণ','খাবার নির্ধারিত কেন্দ্রে ক্ষুধার্ত মানুষের মাঝে বিতরণ করা হয়।']] as [$i,$t,$d])
      <div class="col-sm-6 col-lg-3"><div class="step-circle"><i class="bi {{ $i }}"></i></div><h5>{{ $t }}</h5><p class="text-muted small">{{ $d }}</p></div>
    @endforeach
  </div>
</section>

<section class="container my-5"><div class="card"><div class="card-body p-4">
  <div class="row align-items-center g-3"><div class="col-lg-8"><h4 class="mb-1">আমাদের সাথে যুক্ত হোন</h4><p class="mb-0 text-muted">আপনার এলাকায় স্বেচ্ছাসেবী স্টাফ হিসেবে কাজ করতে চান? আবেদন করুন — এরিয়া ম্যানেজার আপনার আবেদন পর্যালোচনা করবেন।</p></div>
  <div class="col-lg-4 text-lg-end"><a href="{{ route('staff.apply') }}" class="btn btn-kyaf"><i class="bi bi-person-plus me-1"></i> স্টাফ হওয়ার আবেদন</a></div></div>
</div></div></section>
@endsection
