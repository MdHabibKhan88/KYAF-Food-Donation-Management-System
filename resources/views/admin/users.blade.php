@extends('layouts.dashboard')
@section('title', 'ব্যবহারকারী ব্যবস্থাপনা')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3"><h1 class="dash-title mb-0">ব্যবহারকারী ব্যবস্থাপনা</h1>
  <div class="dropdown"><button class="btn btn-accent dropdown-toggle" data-bs-toggle="dropdown" type="button"><i class="bi bi-person-plus me-1"></i> নতুন অ্যাকাউন্ট</button>
    <ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="{{ route('admin.users.create', 'manager') }}">এরিয়া ম্যানেজার</a></li><li><a class="dropdown-item" href="{{ route('admin.users.create', 'admin') }}">অ্যাডমিন</a></li><li><a class="dropdown-item" href="{{ route('admin.users.create', 'staff') }}">স্টাফ (সরাসরি, কারণসহ)</a></li></ul></div></div>
<form method="get" class="card mb-3"><div class="card-body"><div class="row g-2 align-items-end">
  <div class="col-md-4"><label class="form-label small mb-1">নাম / ইমেইল / মোবাইল</label><input class="form-control" name="q" value="{{ request('q') }}"></div>
  <div class="col-md-3"><label class="form-label small mb-1">ভূমিকা</label><select class="form-select" name="role"><option value="">সব</option>@foreach(__('kyaf.role') as $k => $v)<option value="{{ $k }}" @selected(request('role')===$k)>{{ $v }}</option>@endforeach</select></div>
  <div class="col-md-2"><label class="form-label small mb-1">অবস্থা</label><select class="form-select" name="status"><option value="">সব</option>@foreach(__('kyaf.user_status') as $k => $v)<option value="{{ $k }}" @selected(request('status')===$k)>{{ $v }}</option>@endforeach</select></div>
  <div class="col-md-3 d-flex gap-2"><button class="btn btn-accent flex-fill" type="submit">ফিল্টার</button><a href="{{ url()->current() }}" class="btn btn-light">রিসেট</a></div>
</div></div></form>
<div class="card"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>নাম</th><th>ভূমিকা</th><th>মোবাইল</th><th>অবস্থা</th><th>সর্বশেষ লগইন</th><th>কাজ</th></tr></thead><tbody>
@forelse($users as $u)<tr><td>{{ $u->full_name }}<div class="small text-muted">{{ $u->email }}</div></td><td>{{ __('kyaf.role.'.$u->role->role_name) }}</td><td>{{ \App\Support\Phone::display($u->phone) }}</td>
<td><span class="badge text-bg-{{ ['Active' => 'success', 'Inactive' => 'secondary', 'Suspended' => 'warning', 'Locked' => 'danger', 'PendingActivation' => 'info'][$u->status] }}">{{ __('kyaf.user_status.'.$u->status) }}</span></td>
<td class="small">{{ $u->last_login_at ? bst($u->last_login_at) : '—' }}</td>
<td class="d-flex gap-1 flex-wrap">
  @if($u->user_id !== auth()->id())
  <form method="post" action="{{ route('admin.users.toggle', $u) }}" data-confirm="{{ $u->status === 'Active' ? 'অ্যাকাউন্টটি নিষ্ক্রিয় করবেন? (ডেটা মুছবে না)' : 'অ্যাকাউন্টটি সক্রিয় করবেন?' }}">@csrf<button class="btn btn-sm {{ $u->status === 'Active' ? 'btn-outline-danger' : 'btn-outline-success' }}" type="submit">{{ $u->status === 'Active' ? 'নিষ্ক্রিয় করুন' : 'সক্রিয় করুন' }}</button></form>@endif
  @if($u->status === 'Locked' || $u->locked_until)<form method="post" action="{{ route('admin.users.unlock', $u) }}">@csrf<button class="btn btn-sm btn-outline-warning" type="submit">আনলক</button></form>@endif
</td></tr>@empty<tr><td colspan="6"><div class="empty-state"><i class="bi bi-people"></i>কোনো ব্যবহারকারী পাওয়া যায়নি।</div></td></tr>@endforelse
</tbody></table></div></div>
<div class="mt-3">{{ $users->links() }}</div>
@endsection
