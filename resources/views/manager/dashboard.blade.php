@extends('layouts.dashboard')
@section('title', 'ম্যানেজার ড্যাশবোর্ড')
@section('content')
<h1 class="dash-title">স্বাগতম, {{ auth()->user()->full_name }}</h1>
@unless($area)
  <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-1"></i> আপনাকে এখনো কোনো সেবা এলাকায় নিয়োগ দেওয়া হয়নি। অ্যাডমিনের সাথে যোগাযোগ করুন।</div>
@else
<div class="card mb-3"><div class="card-body small d-flex flex-wrap gap-4">
  <div><i class="bi bi-map text-primary me-1"></i> <strong>সেবা এলাকা:</strong> {{ $area->area_name }} ({{ $area->area_code }}) — {{ $area->district->name_bn }}</div>
  <div><i class="bi bi-geo me-1"></i> <strong>থানা:</strong> {{ $area->thanas->pluck('name_bn')->implode(', ') ?: '—' }}</div>
</div></div>
@if($understaffed)<div class="alert alert-warning"><i class="bi bi-person-exclamation me-1"></i> <strong>দলে স্টাফ কম:</strong> বর্তমানে {{ bn_digits($active) }}/{{ bn_digits($cap) }} জন সক্রিয় স্টাফ আছেন। {{ bn_digits($vacancies) }}টি শূন্যপদ — <a href="{{ route('manager.applications') }}">আবেদনগুলো পর্যালোচনা করুন</a>।</div>@endif
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">@include('partials.kpi', ['icon' => 'bi-people', 'value' => $active.'/'.$cap, 'label' => 'সক্রিয় স্টাফ (সর্বোচ্চ '.bn_digits($cap).')'])</div>
  <div class="col-6 col-lg-3">@include('partials.kpi', ['icon' => 'bi-person-plus', 'value' => $pendingApps, 'label' => 'অপেক্ষমাণ আবেদন'])</div>
  <div class="col-6 col-lg-3">@include('partials.kpi', ['icon' => 'bi-box-seam', 'value' => $counts->sum(), 'label' => 'এলাকার মোট দান'])</div>
  <div class="col-6 col-lg-3">@include('partials.kpi', ['icon' => 'bi-hourglass-split', 'value' => ($counts['Submitted'] ?? 0), 'label' => 'স্টাফ নিয়োগ বাকি'])</div>
</div>
<div class="row g-3 mb-3">
  <div class="col-6 col-lg-3">@include('partials.kpi', ['icon' => 'bi-clipboard-check', 'value' => $counts['Verified'] ?? 0, 'label' => 'অনুমোদনের অপেক্ষায়'])</div>
  <div class="col-6 col-lg-3">@include('partials.kpi', ['icon' => 'bi-truck', 'value' => ($counts['Approved'] ?? 0) + ($counts['Collected'] ?? 0), 'label' => 'সংগ্রহ/বিতরণ চলমান'])</div>
  <div class="col-6 col-lg-3">@include('partials.kpi', ['icon' => 'bi-flag', 'value' => $counts['Distributed'] ?? 0, 'label' => 'বন্ধ করার অপেক্ষায়'])</div>
  <div class="col-6 col-lg-3">@include('partials.kpi', ['icon' => 'bi-check2-circle', 'value' => $counts['Completed'] ?? 0, 'label' => 'সম্পন্ন'])</div>
</div>
<div class="card"><div class="card-header d-flex justify-content-between"><span>সাম্প্রতিক দান</span><a href="{{ route('manager.donations') }}" class="small">সব দেখুন</a></div>
@if($recent->isEmpty())<div class="empty-state"><i class="bi bi-inbox"></i>এখনো কোনো দান আসেনি।</div>@else
<div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>কোড</th><th>খাবার</th><th>মিল</th><th>স্টাফ</th><th>অবস্থা</th><th></th></tr></thead><tbody>
@foreach($recent as $d)<tr><td>{{ $d->donation_code }}</td><td>{{ $d->food_name }}</td><td>{{ bn_digits($d->quantity_meals) }}</td><td>{{ optional(optional($d->assignedStaff)->user)->full_name ?? '—' }}</td><td><x-status :s="$d->status" /></td><td><a class="btn btn-sm btn-outline-accent" href="{{ route('manager.donations.show', $d) }}">খুলুন</a></td></tr>@endforeach
</tbody></table></div>@endif</div>
@endunless
@endsection
