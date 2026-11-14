@php
  $u = auth()->user();
  $role = $u->roleName();
  $theme = ['ADMIN' => 'admin', 'AREA_MANAGER' => 'manager', 'STAFF' => 'staff', 'DONOR' => 'donor'][$role];
  $unread = $u->unreadCount();
@endphp
<!doctype html>
<html lang="bn">
<head>
  @include('partials.head')
  <title>@yield('title', 'ড্যাশবোর্ড') — {{ config('app.name') }}</title>
</head>
<body class="theme-{{ $theme }}">
<div class="dash-wrap">
  <aside class="sidebar" id="sidebar">
    <a class="brand" href="{{ route('dashboard') }}"><img src="{{ asset('images/logo.svg') }}" width="38" alt=""><span class="brand-name">KYAF<small>খাদ্য দান ব্যবস্থাপনা</small></span></a>
    <span class="role-pill"><i class="bi bi-person-check me-1"></i> {{ __('kyaf.role.'.$role) }}</span>
    <nav class="nav flex-column pb-4">@include('partials.sidebar-'.$theme)</nav>
  </aside>
  <div class="dash-main">
    <header class="dash-top">
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-light sidebar-toggle" id="sidebarToggle" type="button" aria-label="মেনু"><i class="bi bi-list fs-5"></i></button>
        <a class="text-decoration-none small" href="{{ route('home') }}"><i class="bi bi-globe2"></i> ওয়েবসাইট</a>
      </div>
      <div class="d-flex align-items-center gap-3">
        <a class="bell text-dark fs-5" href="{{ route('notifications.index') }}" title="বিজ্ঞপ্তি" aria-label="বিজ্ঞপ্তি"><i class="bi bi-bell"></i>@if($unread)<span class="badge rounded-pill bg-danger">{{ bn_digits($unread > 99 ? '99+' : $unread) }}</span>@endif</a>
        <div class="dropdown">
          <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">@include('partials.avatar', ['user' => $u, 'size' => 28]) {{ \Illuminate\Support\Str::limit($u->full_name, 18) }}</button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>আমার প্রোফাইল</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><form method="post" action="{{ route('logout') }}">@csrf<button class="dropdown-item text-danger" type="submit" data-no-loading><i class="bi bi-box-arrow-right me-2"></i>লগআউট</button></form></li>
          </ul>
        </div>
      </div>
    </header>
    <div class="dash-content">
      @include('partials.flash')
      @yield('content')
    </div>
  </div>
</div>
@include('partials.scripts')
</body>
</html>
