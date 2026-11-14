@extends('layouts.dashboard')
@section('title', 'রিপোর্ট')
@section('content')
@php $isAdmin = auth()->user()->hasRole('ADMIN'); $scopeLabel = ['ADMIN' => 'সব এলাকা', 'AREA_MANAGER' => 'আপনার সেবা এলাকা', 'STAFF' => 'আপনার নিয়োগকৃত দান', 'DONOR' => 'আপনার দান'][auth()->user()->roleName()]; @endphp
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3"><div><h1 class="dash-title mb-0">রিপোর্ট ও পরিসংখ্যান</h1><small class="text-muted">পরিধি: {{ $scopeLabel }}</small></div>
  <a class="btn btn-outline-accent" href="{{ route('reports.export', request()->query()) }}"><i class="bi bi-download me-1"></i> CSV ডাউনলোড</a></div>

<form method="get" class="card mb-3"><div class="card-body"><div class="row g-2 align-items-end">
  <div class="col-md-3"><label class="form-label small mb-1">খুঁজুন</label><input class="form-control" name="q" value="{{ $f['q'] ?? '' }}" placeholder="কোড বা খাবার"></div>
  <div class="col-md-2"><label class="form-label small mb-1">অবস্থা</label><select class="form-select" name="status"><option value="">সব</option>@foreach(array_keys(__('kyaf.status')) as $s)<option value="{{ $s }}" @selected(($f['status'] ?? '')===$s)>{{ __('kyaf.status.'.$s) }}</option>@endforeach</select></div>
  @if($isAdmin)<div class="col-md-2"><label class="form-label small mb-1">সেবা এলাকা</label><select class="form-select" name="area_id"><option value="">সব</option>@foreach($areas as $a)<option value="{{ $a->area_id }}" @selected(($f['area_id'] ?? '')==$a->area_id)>{{ $a->area_name }}</option>@endforeach</select></div>@endif
  <div class="col-md-2"><label class="form-label small mb-1">থেকে</label><input type="date" class="form-control" name="from" value="{{ $f['from'] ?? '' }}"></div>
  <div class="col-md-2"><label class="form-label small mb-1">পর্যন্ত</label><input type="date" class="form-control" name="to" value="{{ $f['to'] ?? '' }}"></div>
  <div class="col-md-1 d-grid"><button class="btn btn-accent" type="submit" aria-label="ফিল্টার"><i class="bi bi-funnel"></i></button></div>
</div></div></form>

<div class="row g-3 mb-3">
  <div class="col-6 col-lg-3">@include('partials.kpi', ['icon' => 'bi-box-seam', 'value' => $r['total'], 'label' => 'মোট দান'])</div>
  <div class="col-6 col-lg-3">@include('partials.kpi', ['icon' => 'bi-basket2', 'value' => $r['meals_donated'], 'label' => 'মোট দানকৃত মিল'])</div>
  <div class="col-6 col-lg-3">@include('partials.kpi', ['icon' => 'bi-truck', 'value' => $r['meals_collected'], 'label' => 'সংগৃহীত মিল'])</div>
  <div class="col-6 col-lg-3">@include('partials.kpi', ['icon' => 'bi-people', 'value' => $r['meals_distributed'], 'label' => 'বিতরিত মিল'])</div>
</div>

<div class="row g-3 mb-3">
  <div class="col-lg-4"><div class="card h-100"><div class="card-header">অবস্থা অনুযায়ী দান</div><div class="card-body">
    @forelse($r['by_status'] as $s => $c)
      <div class="d-flex justify-content-between align-items-center mb-1"><x-status :s="$s" /><strong>{{ bn_digits($c) }}</strong></div>
    @empty<div class="text-muted">তথ্য নেই</div>@endforelse
  </div></div></div>
  <div class="col-lg-8"><div class="card h-100"><div class="card-header">মাসভিত্তিক পরিসংখ্যান</div>
    @if($r['monthly']->isEmpty())<div class="empty-state py-4">তথ্য নেই</div>@else
    <div class="table-responsive"><table class="table mb-0"><thead><tr><th>মাস</th><th>দান</th><th>মিল</th><th style="width:40%"></th></tr></thead><tbody>
      @php $max = max(1, $r['monthly']->max('c')); @endphp
      @foreach($r['monthly'] as $m)<tr><td>{{ bn_digits($m->m) }}</td><td>{{ bn_digits($m->c) }}</td><td>{{ bn_digits((int) $m->meals) }}</td><td><div class="progress" style="height:10px"><div class="progress-bar" style="width:{{ round($m->c / $max * 100) }}%;background:var(--accent)"></div></div></td></tr>@endforeach
    </tbody></table></div>@endif</div></div>
</div>

@if(isset($r['by_area']))
<div class="card mb-3"><div class="card-header">সেবা এলাকা অনুযায়ী দান</div>
  @if($r['by_area']->isEmpty())<div class="empty-state py-4">তথ্য নেই</div>@else<div class="table-responsive"><table class="table mb-0"><thead><tr><th>সেবা এলাকা</th><th>দান</th><th>মিল</th></tr></thead><tbody>@foreach($r['by_area'] as $a)<tr><td>{{ $a->name }}</td><td>{{ bn_digits($a->c) }}</td><td>{{ bn_digits((int) $a->meals) }}</td></tr>@endforeach</tbody></table></div>@endif</div>
@endif
@if(isset($r['staff_perf']))
<div class="card mb-3"><div class="card-header">স্টাফ পারফরম্যান্স</div>
  @if($r['staff_perf']->isEmpty())<div class="empty-state py-4">তথ্য নেই</div>@else<div class="table-responsive"><table class="table mb-0"><thead><tr><th>স্টাফ</th><th>নিয়োগকৃত দান</th><th>সংগৃহীত</th><th>বিতরিত</th></tr></thead><tbody>@foreach($r['staff_perf'] as $p)<tr><td>{{ $p->name }}</td><td>{{ bn_digits($p->assigned) }}</td><td>{{ bn_digits((int) $p->collected) }}</td><td>{{ bn_digits((int) $p->distributed) }}</td></tr>@endforeach</tbody></table></div>@endif</div>
@endif

<div class="card"><div class="card-header">দানের তালিকা</div>
  @if($rows->isEmpty())<div class="empty-state"><i class="bi bi-inbox"></i>কোনো তথ্য পাওয়া যায়নি।</div>@else
  <div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>কোড</th><th>খাবার</th><th>মিল</th><th>সেবা এলাকা</th><th>অবস্থা</th><th>জমার তারিখ</th></tr></thead><tbody>
    @foreach($rows as $d)<tr><td>{{ $d->donation_code }}</td><td>{{ $d->food_name }}</td><td>{{ bn_digits($d->quantity_meals) }}</td><td>{{ optional($d->area)->area_name }}</td><td><x-status :s="$d->status" /></td><td>{{ bst($d->submitted_at, 'd/m/Y') }}</td></tr>@endforeach
  </tbody></table></div>@endif</div>
<div class="mt-3">{{ $rows->links() }}</div>
@endsection
