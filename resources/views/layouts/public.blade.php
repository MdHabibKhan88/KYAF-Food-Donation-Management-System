<!doctype html>
<html lang="bn">
<head>
  @include('partials.head')
  <title>@yield('title', 'KYAF খাদ্য দান ব্যবস্থাপনা') — {{ config('app.name') }}</title>
</head>
<body>
<div class="topbar d-none d-md-block">
  <div class="container d-flex justify-content-between">
    <span><i class="bi bi-telephone-fill me-1"></i> {{ bn_digits(setting('CONTACT_PHONE')) }} &nbsp;|&nbsp; <i class="bi bi-envelope-fill me-1"></i> {{ setting('CONTACT_EMAIL') }}</span>
    <span>ক্ষুধামুক্ত বাংলাদেশের পথে আমাদের সঙ্গী হোন</span>
  </div>
</div>
<nav class="navbar navbar-expand-lg navbar-kyaf sticky-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
      <img src="{{ asset('images/logo.svg') }}" width="42" height="42" alt="KYAF লোগো">
      <span class="brand-name">KYAF<small>খাদ্য দান ব্যবস্থাপনা</small></span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="মেনু"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">হোম</a></li>
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">আমাদের সম্পর্কে</a></li>
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('find-food') ? 'active' : '' }}" href="{{ route('find-food') }}">খাবার খুঁজুন</a></li>
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('staff.apply') ? 'active' : '' }}" href="{{ route('staff.apply') }}">স্টাফ হোন</a></li>
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">যোগাযোগ</a></li>
        @auth
          <li class="nav-item ms-lg-2"><a class="btn btn-kyaf" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-1"></i> ড্যাশবোর্ড</a></li>
        @else
          <li class="nav-item ms-lg-2"><a class="btn btn-outline-kyaf" href="{{ route('register') }}">নিবন্ধন</a></li>
          <li class="nav-item"><button type="button" class="btn btn-kyaf" data-bs-toggle="modal" data-bs-target="#loginModal"><i class="bi bi-box-arrow-in-right me-1"></i> লগইন</button></li>
        @endauth
      </ul>
    </div>
  </div>
</nav>

<main>
  @hasSection('head')
    <div class="page-head"><div class="container">@yield('head')</div></div>
  @endif
  <div class="container @empty(trim($__env->yieldContent('head'))) pt-4 @endempty">@include('partials.flash')</div>
  @yield('content')
</main>

<footer class="site-footer">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-5"><div class="d-flex align-items-center gap-2 mb-2"><img src="{{ asset('images/logo.svg') }}" width="40" alt=""><strong class="text-white fs-5">KYAF খাদ্য দান ব্যবস্থাপনা</strong></div>
        <p class="small">উদ্বৃত্ত খাবার সংগ্রহ করে ক্ষুধার্ত মানুষের কাছে নিরাপদে পৌঁছে দেওয়ার একটি স্বচ্ছ ও জবাবদিহিমূলক প্ল্যাটফর্ম।</p></div>
      <div class="col-6 col-md-3"><h6 class="text-white">দ্রুত লিংক</h6><ul class="list-unstyled small">
        <li><a href="{{ route('about') }}">আমাদের সম্পর্কে</a></li><li><a href="{{ route('find-food') }}">খাবার খুঁজুন</a></li><li><a href="{{ route('staff.apply') }}">স্টাফ হোন</a></li><li><a href="{{ route('register') }}">দাতা নিবন্ধন</a></li></ul></div>
      <div class="col-6 col-md-4"><h6 class="text-white">যোগাযোগ</h6><ul class="list-unstyled small">
        <li><i class="bi bi-telephone me-1"></i> {{ bn_digits(setting('CONTACT_PHONE')) }}</li><li><i class="bi bi-envelope me-1"></i> {{ setting('CONTACT_EMAIL') }}</li><li><i class="bi bi-geo-alt me-1"></i> {{ setting('CONTACT_ADDRESS') }}</li></ul></div>
    </div>
    <hr class="border-secondary"><div class="small text-center">© {{ bn_digits(now()->format('Y')) }} KYAF। সর্বস্বত্ব সংরক্ষিত।</div>
  </div>
</footer>
@guest @include('partials.login-modal') @endguest
@include('partials.scripts')
</body>
</html>
