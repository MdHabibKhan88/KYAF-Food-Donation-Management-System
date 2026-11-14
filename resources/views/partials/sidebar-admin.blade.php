@php $a = fn ($p) => request()->routeIs($p) ? 'active' : ''; @endphp
<a class="nav-link {{ $a('admin.dashboard') }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i> ড্যাশবোর্ড</a>
<div class="nav-heading">ব্যবহারকারী</div>
<a class="nav-link {{ $a('admin.users*') }}" href="{{ route('admin.users') }}"><i class="bi bi-people"></i> ব্যবহারকারী ব্যবস্থাপনা</a>
<a class="nav-link {{ $a('admin.roles') }}" href="{{ route('admin.roles') }}"><i class="bi bi-person-lock"></i> ভূমিকা (Roles)</a>
<a class="nav-link {{ $a('admin.applications*') }}" href="{{ route('admin.applications') }}"><i class="bi bi-person-plus"></i> স্টাফ আবেদন</a>
<div class="nav-heading">কার্যক্রম</div>
<a class="nav-link {{ $a('admin.donations*') }}" href="{{ route('admin.donations') }}"><i class="bi bi-box-seam"></i> দান ব্যবস্থাপনা</a>
<a class="nav-link {{ $a('admin.areas*') }}" href="{{ route('admin.areas') }}"><i class="bi bi-map"></i> সেবা এলাকা</a>
<a class="nav-link {{ $a('admin.locations*') }}" href="{{ route('admin.locations') }}"><i class="bi bi-geo-alt"></i> অবস্থান (বিভাগ–থানা)</a>
<a class="nav-link {{ $a('reports.*') }}" href="{{ route('reports.index') }}"><i class="bi bi-bar-chart-line"></i> রিপোর্ট</a>
<div class="nav-heading">সিস্টেম</div>
<a class="nav-link {{ $a('admin.messages*') }}" href="{{ route('admin.messages') }}"><i class="bi bi-envelope"></i> যোগাযোগ বার্তা</a>
<a class="nav-link {{ $a('admin.audit') }}" href="{{ route('admin.audit') }}"><i class="bi bi-journal-text"></i> অডিট লগ</a>
<a class="nav-link {{ $a('admin.settings*') }}" href="{{ route('admin.settings') }}"><i class="bi bi-gear"></i> সেটিংস</a>
<a class="nav-link {{ $a('notifications.*') }}" href="{{ route('notifications.index') }}"><i class="bi bi-bell"></i> বিজ্ঞপ্তি</a>
