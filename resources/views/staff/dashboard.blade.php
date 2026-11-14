@extends('layouts.dashboard')
@section('title', 'স্টাফ ড্যাশবোর্ড')
@section('content')
@php $area = optional($staff->manager)->serviceArea; @endphp
<h1 class="dash-title">স্বাগতম, {{ auth()->user()->full_name }}</h1>
<div class="card mb-3"><div class="card-body d-flex flex-wrap gap-4 small">
  <div><i class="bi bi-map text-warning me-1"></i> <strong>সেবা এলাকা:</strong> {{ $area ? $area->area_name.' ('.$area->district->name_bn.')' : '—' }}</div>
  <div><i class="bi bi-person-badge text-warning me-1"></i> <strong>এরিয়া ম্যানেজার:</strong> {{ optional($staff->manager->user)->full_name }}</div>
</div></div>
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">@include('partials.kpi', ['icon' => 'bi-clipboard-check', 'value' => $counts['Assigned'] ?? 0, 'label' => 'যাচাই বাকি'])</div>
  <div class="col-6 col-lg-3">@include('partials.kpi', ['icon' => 'bi-truck', 'value' => $counts['Approved'] ?? 0, 'label' => 'সংগ্রহ বাকি'])</div>
  <div class="col-6 col-lg-3">@include('partials.kpi', ['icon' => 'bi-people', 'value' => $counts['Collected'] ?? 0, 'label' => 'বিতরণ বাকি'])</div>
  <div class="col-6 col-lg-3">@include('partials.kpi', ['icon' => 'bi-check2-circle', 'value' => ($counts['Distributed'] ?? 0) + ($counts['Completed'] ?? 0), 'label' => 'সম্পন্ন কাজ'])</div>
</div>
<div class="card"><div class="card-header">আমার অমীমাংসিত কাজ</div>
@if($pending->isEmpty())<div class="empty-state"><i class="bi bi-emoji-smile"></i>এখন কোনো কাজ বাকি নেই।</div>@else
<div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>কোড</th><th>খাবার</th><th>মিল</th><th>পিকআপ থানা</th><th>মেয়াদ</th><th>পরবর্তী কাজ</th><th></th></tr></thead><tbody>
@foreach($pending as $d)<tr>
  <td><strong>{{ $d->donation_code }}</strong></td><td>{{ $d->food_name }}</td><td>{{ bn_digits($d->quantity_meals) }}</td><td>{{ $d->pickupThana->name_bn }}</td>
  <td>{{ bst($d->expiryInstant(), 'd/m h:i A') }} @if($d->isExpired())<span class="badge text-bg-danger">শেষ</span>@endif</td>
  <td><x-status :s="$d->status" /> <small class="text-muted">{{ ['Assigned' => '→ যাচাই করুন', 'Approved' => '→ সংগ্রহ করুন', 'Collected' => '→ বিতরণ করুন'][$d->status] }}</small></td>
  <td><a class="btn btn-sm btn-accent" href="{{ route('staff.tasks.show', $d) }}">খুলুন</a></td></tr>@endforeach
</tbody></table></div>@endif</div>
@endsection
