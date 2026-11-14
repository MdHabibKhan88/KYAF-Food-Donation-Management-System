@php $a = fn ($p) => request()->routeIs($p) ? 'active' : ''; @endphp
<a class="nav-link {{ $a('manager.dashboard') }}" href="{{ route('manager.dashboard') }}"><i class="bi bi-speedometer2"></i> ড্যাশবোর্ড</a>
<div class="nav-heading">আমার এলাকা</div>
<a class="nav-link {{ $a('manager.donations*') }}" href="{{ route('manager.donations') }}"><i class="bi bi-box-seam"></i> দান পর্যালোচনা</a>
<a class="nav-link {{ $a('manager.team*') }}" href="{{ route('manager.team') }}"><i class="bi bi-people"></i> আমার দল</a>
<a class="nav-link {{ $a('manager.applications*') }}" href="{{ route('manager.applications') }}"><i class="bi bi-person-plus"></i> স্টাফ আবেদন</a>
<a class="nav-link {{ $a('reports.*') }}" href="{{ route('reports.index') }}"><i class="bi bi-bar-chart-line"></i> রিপোর্ট</a>
<div class="nav-heading">অ্যাকাউন্ট</div>
<a class="nav-link {{ $a('notifications.*') }}" href="{{ route('notifications.index') }}"><i class="bi bi-bell"></i> বিজ্ঞপ্তি</a>
<a class="nav-link {{ $a('profile.*') }}" href="{{ route('profile.edit') }}"><i class="bi bi-person"></i> প্রোফাইল</a>
