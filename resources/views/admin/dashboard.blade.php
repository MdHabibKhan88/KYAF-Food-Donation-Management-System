@extends('layouts.dashboard')
@section('title', 'অ্যাডমিন ড্যাশবোর্ড')
@section('content')
<h1 class="dash-title">অ্যাডমিন ড্যাশবোর্ড</h1>
<div class="row g-3 mb-3">
  @foreach([['bi-people','users','মোট ব্যবহারকারী'],['bi-heart','donors','দাতা'],['bi-diagram-3','managers','এরিয়া ম্যানেজার'],['bi-person-badge','staff','স্টাফ']] as [$i,$k,$l])
    <div class="col-6 col-lg-3">@include('partials.kpi', ['icon' => $i, 'value' => $c[$k], 'label' => $l])</div>
  @endforeach
  @foreach([['bi-box-seam','donations','মোট দান'],['bi-hourglass-split','pending','চলমান দান'],['bi-check2-circle','completed','সম্পন্ন দান'],['bi-map','areas','সেবা এলাকা']] as [$i,$k,$l])
    <div class="col-6 col-lg-3">@include('partials.kpi', ['icon' => $i, 'value' => $c[$k], 'label' => $l])</div>
  @endforeach
</div>
<div class="row g-3 mb-3">
  <div class="col-md-6"><a class="text-decoration-none text-dark" href="{{ route('admin.applications', ['status' => 'Submitted']) }}">@include('partials.kpi', ['icon' => 'bi-person-plus', 'value' => $c['apps'], 'label' => 'অপেক্ষমাণ স্টাফ আবেদন'])</a></div>
  <div class="col-md-6"><a class="text-decoration-none text-dark" href="{{ route('admin.messages', ['status' => 'New']) }}">@include('partials.kpi', ['icon' => 'bi-envelope', 'value' => $c['messages'], 'label' => 'নতুন যোগাযোগ বার্তা'])</a></div>
</div>
<div class="row g-3 mb-3">
  <div class="col-lg-5"><div class="card h-100"><div class="card-header">অবস্থা অনুযায়ী দান</div><div class="card-body">
    @forelse($byStatus as $s => $n)<div class="d-flex justify-content-between mb-1"><x-status :s="$s" /><strong>{{ bn_digits($n) }}</strong></div>@empty<div class="text-muted">এখনো কোনো দান নেই।</div>@endforelse</div></div></div>
  <div class="col-lg-7"><div class="card h-100"><div class="card-header">সতর্কতা</div><div class="card-body small">
    @forelse($noManager as $a)<div class="text-danger mb-1"><i class="bi bi-exclamation-octagon me-1"></i> <strong>{{ $a->area_name }}</strong> — কোনো এরিয়া ম্যানেজার নেই (দান গ্রহণ বন্ধ)</div>@empty @endforelse
    @forelse($understaffed as $a)<div class="text-warning-emphasis mb-1"><i class="bi bi-person-exclamation me-1"></i> <strong>{{ $a->area_name }}</strong> — স্টাফ {{ bn_digits($a->manager->activeStaffCount()) }}/{{ bn_digits(\App\Services\TeamService::capacity()) }} (শূন্যপদ আছে)</div>@empty @endforelse
    @if($noManager->isEmpty() && $understaffed->isEmpty())<div class="text-muted">কোনো সতর্কতা নেই।</div>@endif</div></div></div>
</div>
<div class="card"><div class="card-header d-flex justify-content-between"><span>সাম্প্রতিক অডিট কার্যক্রম</span><a href="{{ route('admin.audit') }}" class="small">সব দেখুন</a></div>
<div class="table-responsive"><table class="table mb-0 small"><thead><tr><th>সময়</th><th>ব্যবহারকারী</th><th>কাজ</th><th>ফলাফল</th></tr></thead><tbody>
@forelse($logs as $l)<tr><td>{{ bst($l->created_at) }}</td><td>{{ optional($l->user)->full_name ?? 'অজ্ঞাত/সিস্টেম' }}</td><td>{{ $l->action_type }} <span class="text-muted">{{ $l->entity_name }}</span></td><td><span class="badge text-bg-{{ $l->result === 'Success' ? 'success' : 'danger' }}">{{ $l->result === 'Success' ? 'সফল' : 'ব্যর্থ' }}</span></td></tr>@empty<tr><td colspan="4" class="text-center text-muted">কোনো লগ নেই</td></tr>@endforelse
</tbody></table></div></div>
@endsection
