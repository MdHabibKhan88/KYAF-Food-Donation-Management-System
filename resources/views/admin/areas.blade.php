@extends('layouts.dashboard')
@section('title', 'সেবা এলাকা')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="dash-title mb-0">সেবা এলাকা</h1><a href="{{ route('admin.areas.create') }}" class="btn btn-accent"><i class="bi bi-plus-circle me-1"></i> নতুন সেবা এলাকা</a></div>
<form method="get" class="card mb-3"><div class="card-body"><div class="row g-2 align-items-end">
  <div class="col-md-4"><label class="form-label small mb-1">নাম / কোড</label><input class="form-control" name="q" value="{{ request('q') }}"></div>
  <div class="col-md-4"><label class="form-label small mb-1">জেলা</label><select class="form-select" name="district_id"><option value="">সব</option>@foreach($districts as $d)<option value="{{ $d->district_id }}" @selected(request('district_id')==$d->district_id)>{{ $d->name_bn }}</option>@endforeach</select></div>
  <div class="col-md-3 d-flex gap-2"><button class="btn btn-accent flex-fill" type="submit">ফিল্টার</button><a href="{{ url()->current() }}" class="btn btn-light">রিসেট</a></div>
</div></div></form>
<div class="card"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>কোড</th><th>নাম</th><th>জেলা</th><th>থানা</th><th>ম্যানেজার</th><th>স্টাফ</th><th>অবস্থা</th><th></th></tr></thead><tbody>
@forelse($areas as $a)<tr><td>{{ $a->area_code }}</td><td>{{ $a->area_name }}</td><td>{{ $a->district->name_bn }}</td><td>{{ bn_digits($a->thanas_count) }}টি</td>
<td>{{ optional(optional($a->manager)->user)->full_name ?? '—' }}</td><td>{{ $a->manager ? bn_digits($a->manager->activeStaffCount()).'/'.bn_digits(\App\Services\TeamService::capacity()) : '—' }}</td>
<td><span class="badge text-bg-{{ $a->status === 'Active' ? 'success' : 'secondary' }}">{{ $a->status === 'Active' ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}</span></td><td><a class="btn btn-sm btn-outline-accent" href="{{ route('admin.areas.edit', $a) }}">সম্পাদনা</a></td></tr>
@empty<tr><td colspan="8"><div class="empty-state"><i class="bi bi-map"></i>কোনো সেবা এলাকা নেই।</div></td></tr>@endforelse
</tbody></table></div></div>
<div class="mt-3">{{ $areas->links() }}</div>
@endsection
