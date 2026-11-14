@extends('layouts.dashboard')
@section('title', 'দান '.$donation->donation_code)
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3"><h1 class="dash-title mb-0">{{ $donation->donation_code }} <x-status :s="$donation->status" /></h1><a href="{{ route('admin.donations') }}" class="btn btn-light">← তালিকা</a></div>
<div class="card mb-3"><div class="card-body">@include('partials.donation-progress')</div></div>
<div class="row g-3">
<div class="col-lg-7">
  <div class="card mb-3"><div class="card-header">দানের তথ্য</div><div class="card-body">@include('partials.donation-details')
    <hr class="my-2"><div class="small"><strong>দাতা:</strong> {{ $donation->donor->user->full_name }} ({{ $donation->donor_id }}) · {{ \App\Support\Phone::display($donation->donor->user->phone) }}<br>
    <strong>এরিয়া ম্যানেজার:</strong> {{ optional(optional($donation->area->manager)->user)->full_name ?? '—' }} · <strong>স্টাফ:</strong> {{ optional(optional($donation->assignedStaff)->user)->full_name ?? '—' }}</div></div></div>
  @if($donation->verifications->isNotEmpty() || $donation->approvals->isNotEmpty() || $donation->collection || $donation->distributions->isNotEmpty())
  <div class="card mb-3"><div class="card-header">যাচাই, অনুমোদন, সংগ্রহ ও বিতরণ</div><div class="card-body small">
    @foreach($donation->verifications as $v)<div>যাচাই: {{ $v->result === 'Verified' ? 'যাচাইকৃত' : 'প্রত্যাখ্যাত' }} ({{ __('kyaf.condition.'.$v->food_condition) }}, {{ bn_digits($v->verified_quantity_meals) }} মিল) — {{ optional($v->staff->user)->full_name }} {{ $v->remarks }}</div>@endforeach
    @foreach($donation->approvals as $a)<div>অনুমোদন: {{ $a->decision === 'Approved' ? 'অনুমোদিত' : 'প্রত্যাখ্যাত' }} — {{ optional($a->decider)->full_name }} {{ $a->remarks }}</div>@endforeach
    @if($donation->collection)<div>সংগ্রহ: {{ $donation->collection->status }} {{ $donation->collection->collected_quantity_meals ? bn_digits($donation->collection->collected_quantity_meals).' মিল' : '' }}</div>@endif
    @foreach($donation->distributions as $b)<div>বিতরণ: {{ $b->point_name }} — {{ bn_digits($b->meals_distributed) }}/{{ bn_digits($b->meals_planned) }} ({{ __('kyaf.dist_status.'.$b->status) }})</div>@endforeach</div></div>@endif

  @unless($donation->isFinal())
  <div class="card border-danger mb-3"><div class="card-header text-danger"><i class="bi bi-shield-exclamation me-1"></i> অ্যাডমিন ওভাররাইড (কারণ আবশ্যক, অডিট লগে নথিভুক্ত হবে)</div><div class="card-body">
    @if($donation->status === 'Submitted' && $team->isNotEmpty())
      <form method="post" action="{{ route('admin.donations.assign', $donation) }}" class="row g-2 mb-3">@csrf
        <div class="col-md-4"><select name="staff_id" class="form-select" required aria-label="স্টাফ"><option value="">স্টাফ নির্বাচন</option>@foreach($team as $s)<option value="{{ $s->staff_id }}">{{ $s->user->full_name }}</option>@endforeach</select></div>
        <div class="col-md-5"><input name="reason" class="form-control" placeholder="নিয়োগের কারণ" required minlength="5" aria-label="কারণ"></div><div class="col-md-3"><button class="btn btn-outline-danger w-100" type="submit">স্টাফ নিয়োগ</button></div></form>
    @endif
    @if($donation->status === 'Verified')
      <form method="post" action="{{ route('admin.donations.decide', $donation) }}" class="row g-2 mb-3">@csrf
        <div class="col-md-3"><select name="decision" class="form-select" required aria-label="সিদ্ধান্ত"><option value="Approved">অনুমোদন</option><option value="Rejected">প্রত্যাখ্যান</option></select></div>
        <div class="col-md-3"><input name="reason" class="form-control" placeholder="ওভাররাইডের কারণ" required minlength="5" aria-label="কারণ"></div>
        <div class="col-md-4"><input name="remarks" class="form-control" placeholder="প্রত্যাখ্যানের কারণ (দাতাকে দেখানো হবে)" aria-label="মন্তব্য"></div><div class="col-md-2"><button class="btn btn-outline-danger w-100" type="submit">জমা দিন</button></div></form>
    @endif
    @if($donation->status === 'Distributed')
      <form method="post" action="{{ route('admin.donations.close', $donation) }}" class="row g-2 mb-3">@csrf<div class="col-md-8"><input name="reason" class="form-control" placeholder="সম্পন্ন করার কারণ" required minlength="5" aria-label="কারণ"></div><div class="col-md-4"><button class="btn btn-outline-danger w-100" type="submit">সম্পন্ন করুন</button></div></form>
    @endif
    @if(in_array($donation->status, ['Submitted','Assigned','Verified','Approved']))
      <form method="post" action="{{ route('admin.donations.cancel', $donation) }}" class="row g-2" data-confirm="দানটি বাতিল করবেন?">@csrf<div class="col-md-8"><input name="reason" class="form-control" placeholder="বাতিলের কারণ (দাতাকে দেখানো হবে)" required minlength="5" aria-label="কারণ"></div><div class="col-md-4"><button class="btn btn-danger w-100" type="submit">দান বাতিল</button></div></form>
    @endif
  </div></div>@endunless
</div>
<div class="col-lg-5"><div class="card"><div class="card-header">অবস্থার ইতিহাস</div><div class="card-body">@include('partials.timeline')</div></div></div>
</div>
@endsection
