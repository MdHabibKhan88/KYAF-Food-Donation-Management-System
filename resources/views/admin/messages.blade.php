@extends('layouts.dashboard')
@section('title', 'যোগাযোগ বার্তা')
@section('content')
<h1 class="dash-title">যোগাযোগ বার্তা</h1>
<form method="get" class="card mb-3"><div class="card-body"><div class="row g-2 align-items-end">
  <div class="col-md-5"><label class="form-label small mb-1">খুঁজুন</label><input class="form-control" name="q" value="{{ request('q') }}" placeholder="নাম / বিষয় / ইমেইল"></div>
  <div class="col-md-3"><label class="form-label small mb-1">অবস্থা</label><select class="form-select" name="status"><option value="">সব</option>@foreach(__('kyaf.msg_status') as $k => $v)<option value="{{ $k }}" @selected(request('status')===$k)>{{ $v }}</option>@endforeach</select></div>
  <div class="col-md-3 d-flex gap-2"><button class="btn btn-accent flex-fill" type="submit">ফিল্টার</button><a href="{{ url()->current() }}" class="btn btn-light">রিসেট</a></div>
</div></div></form>
<div class="card">@if($messages->isEmpty())<div class="empty-state"><i class="bi bi-envelope-open"></i>কোনো বার্তা নেই।</div>@else
<div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>প্রেরক</th><th>বিষয়</th><th>অবস্থা</th><th>তারিখ</th><th></th></tr></thead><tbody>
@foreach($messages as $m)<tr><td>{{ $m->sender_name }}<div class="small text-muted">{{ $m->email }}</div></td><td>{{ $m->subject }}</td><td><span class="badge text-bg-{{ ['New' => 'danger', 'InProgress' => 'warning', 'Resolved' => 'success'][$m->status] }}">{{ __('kyaf.msg_status.'.$m->status) }}</span></td><td>{{ bst($m->created_at) }}</td><td><a class="btn btn-sm btn-outline-accent" href="{{ route('admin.messages.show', $m) }}">পড়ুন</a></td></tr>@endforeach
</tbody></table></div>@endif</div>
<div class="mt-3">{{ $messages->links() }}</div>
@endsection
