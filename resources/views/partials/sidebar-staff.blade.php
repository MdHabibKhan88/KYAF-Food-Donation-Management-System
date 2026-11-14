@php $a = fn ($p) => request()->routeIs($p) ? 'active' : ''; @endphp
<a class="nav-link {{ $a('staff.dashboard') }}" href="{{ route('staff.dashboard') }}"><i class="bi bi-speedometer2"></i> ড্যাশবোর্ড</a>
<div class="nav-heading">আমার কাজ</div>
<a class="nav-link {{ $a('staff.tasks*') }}" href="{{ route('staff.tasks') }}"><i class="bi bi-list-check"></i> নিয়োগকৃত দান</a>
<a class="nav-link {{ $a('staff.history') }}" href="{{ route('staff.history') }}"><i class="bi bi-clock-history"></i> দানের ইতিহাস</a>
<a class="nav-link {{ $a('reports.*') }}" href="{{ route('reports.index') }}"><i class="bi bi-bar-chart-line"></i> আমার রিপোর্ট</a>
<div class="nav-heading">অ্যাকাউন্ট</div>
<a class="nav-link {{ $a('notifications.*') }}" href="{{ route('notifications.index') }}"><i class="bi bi-bell"></i> বিজ্ঞপ্তি</a>
<a class="nav-link {{ $a('profile.*') }}" href="{{ route('profile.edit') }}"><i class="bi bi-person"></i> প্রোফাইল</a>
