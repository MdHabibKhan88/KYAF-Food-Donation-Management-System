@extends('layouts.dashboard')
@section('title', 'স্টাফ আবেদন')
@section('content')
<h1 class="dash-title">স্টাফ আবেদন (সব এলাকা)</h1>
<form method="get" class="card mb-3"><div class="card-body"><div class="row g-2 align-items-end">
  <div class="col-md-3"><label class="form-label small mb-1">নাম / ইমেইল</label><input class="form-control" name="q" value="{{ request('q') }}"></div>
  <div class="col-md-3"><label class="form-label small mb-1">অবস্থা</label><select class="form-select" name="status"><option value="">সব</option>@foreach(__('kyaf.app_status') as $k => $v)<option value="{{ $k }}" @selected(request('status')===$k)>{{ $v }}</option>@endforeach</select></div>
  <div class="col-md-3"><label class="form-label small mb-1">সেবা এলাকা</label><select class="form-select" name="area_id"><option value="">সব</option>@foreach($areas as $a)<option value="{{ $a->area_id }}" @selected(request('area_id')==$a->area_id)>{{ $a->area_name }}</option>@endforeach</select></div>
  <div class="col-md-3 d-flex gap-2"><button class="btn btn-accent flex-fill" type="submit">ফিল্টার</button><a href="{{ url()->current() }}" class="btn btn-light">রিসেট</a></div>
</div></div></form>
<div class="card">@if($apps->isEmpty())<div class="empty-state"><i class="bi bi-inbox"></i>কোনো আবেদন নেই।</div>@else
<div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>#</th><th>আবেদনকারী</th><th>সেবা এলাকা</th><th>অবস্থা</th><th>জমার তারিখ</th><th></th></tr></thead><tbody>
@foreach($apps as $a)<tr><td>{{ bn_digits($a->application_id) }}</td><td>{{ $a->applicant_name }}<div class="small text-muted">{{ $a->email }}</div></td><td>{{ $a->area->area_name }}</td><td><span class="badge text-bg-secondary">{{ __('kyaf.app_status.'.$a->status) }}</span></td><td>{{ bst($a->submitted_at, 'd/m/Y') }}</td><td><a class="btn btn-sm btn-outline-accent" href="{{ route('admin.applications.show', $a) }}">দেখুন</a></td></tr>@endforeach
</tbody></table></div>@endif</div>
<div class="mt-3">{{ $apps->links() }}</div>
@endsection
