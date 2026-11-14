@php $a = fn ($p) => request()->routeIs($p) ? 'active' : ''; @endphp
<a class="nav-link {{ $a('donor.dashboard') }}" href="{{ route('donor.dashboard') }}"><i class="bi bi-speedometer2"></i> ড্যাশবোর্ড</a>
<div class="nav-heading">দান</div>
<a class="nav-link {{ $a('donor.donations.create') }}" href="{{ route('donor.donations.create') }}"><i class="bi bi-plus-circle"></i> নতুন দান জমা দিন</a>
<a class="nav-link {{ $a('donor.donations') }} {{ $a('donor.donations.show') }}" href="{{ route('donor.donations') }}"><i class="bi bi-box-seam"></i> আমার দানসমূহ</a>
<a class="nav-link {{ $a('reports.*') }}" href="{{ route('reports.index') }}"><i class="bi bi-bar-chart-line"></i> দানের ইতিহাস ও রিপোর্ট</a>
<div class="nav-heading">অ্যাকাউন্ট</div>
<a class="nav-link {{ $a('notifications.*') }}" href="{{ route('notifications.index') }}"><i class="bi bi-bell"></i> বিজ্ঞপ্তি</a>
<a class="nav-link {{ $a('profile.*') }}" href="{{ route('profile.edit') }}"><i class="bi bi-person"></i> প্রোফাইল ও পাসওয়ার্ড</a>
