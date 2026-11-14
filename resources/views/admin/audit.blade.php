@extends('layouts.dashboard')
@section('title', 'অডিট লগ')
@section('content')
<h1 class="dash-title">অডিট লগ <small class="text-muted fs-6">(শুধু পঠনযোগ্য — মোছা বা পরিবর্তন করা যায় না)</small></h1>
<form method="get" class="card mb-3"><div class="card-body"><div class="row g-2 align-items-end">
  <div class="col-md-3"><label class="form-label small mb-1">ব্যবহারকারী</label><input class="form-control" name="user" value="{{ request('user') }}" placeholder="নাম বা ইমেইল"></div>
  <div class="col-md-2"><label class="form-label small mb-1">কাজের ধরন</label><select class="form-select" name="action_type"><option value="">সব</option>@foreach($actions as $a)<option value="{{ $a }}" @selected(request('action_type')===$a)>{{ $a }}</option>@endforeach</select></div>
  <div class="col-md-2"><label class="form-label small mb-1">ফলাফল</label><select class="form-select" name="result"><option value="">সব</option><option value="Success" @selected(request('result')==='Success')>সফল</option><option value="Failure" @selected(request('result')==='Failure')>ব্যর্থ</option></select></div>
  <div class="col-md-2"><label class="form-label small mb-1">থেকে</label><input type="date" class="form-control" name="from" value="{{ request('from') }}"></div>
  <div class="col-md-2"><label class="form-label small mb-1">পর্যন্ত</label><input type="date" class="form-control" name="to" value="{{ request('to') }}"></div>
  <div class="col-md-1 d-grid"><button class="btn btn-accent" type="submit" aria-label="ফিল্টার"><i class="bi bi-funnel"></i></button></div>
</div></div></form>
<div class="card"><div class="table-responsive"><table class="table table-sm table-hover mb-0 small"><thead><tr><th>সময়</th><th>ব্যবহারকারী</th><th>কাজ</th><th>বস্তু</th><th>ফলাফল</th><th>আইপি</th><th>বিবরণ</th></tr></thead><tbody>
@forelse($logs as $l)<tr><td class="text-nowrap">{{ bst($l->created_at, 'd/m/Y h:i:s A') }}</td><td>{{ optional($l->user)->full_name ?? '—' }}</td><td>{{ $l->action_type }}</td><td>{{ $l->entity_name }} {{ $l->entity_id }}</td>
<td><span class="badge text-bg-{{ $l->result === 'Success' ? 'success' : 'danger' }}">{{ $l->result === 'Success' ? 'সফল' : 'ব্যর্থ' }}</span></td><td>{{ $l->ip_address }}</td>
<td style="max-width:320px"><code class="text-break">{{ \Illuminate\Support\Str::limit(json_encode(['আগে' => $l->old_value, 'পরে' => $l->new_value], JSON_UNESCAPED_UNICODE), 160) }}</code></td></tr>
@empty<tr><td colspan="7"><div class="empty-state"><i class="bi bi-journal"></i>কোনো লগ নেই।</div></td></tr>@endforelse
</tbody></table></div></div>
<div class="mt-3">{{ $logs->links() }}</div>
@endsection
