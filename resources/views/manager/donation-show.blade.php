@extends('layouts.dashboard')
@section('title', 'দান '.$donation->donation_code)
@section('content')
@php $expired = $donation->isExpired() && ! $donation->isFinal(); @endphp
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3"><h1 class="dash-title mb-0">{{ $donation->donation_code }} <x-status :s="$donation->status" /></h1><a href="{{ route('manager.donations') }}" class="btn btn-light">← তালিকা</a></div>
<div class="card mb-3"><div class="card-body">@include('partials.donation-progress')</div></div>
@if($expired)<div class="alert alert-danger">খাবারের মেয়াদ উত্তীর্ণ হয়েছে। এই দানের কোনো কাজ আর করা যাবে না।</div>@endif
<div class="row g-3">
<div class="col-lg-7">
  <div class="card mb-3"><div class="card-header">দানের তথ্য</div><div class="card-body">@include('partials.donation-details')
    <hr class="my-2"><div class="small"><strong>দাতা:</strong> {{ $donation->donor->user->full_name }} ({{ $donation->donor_id }}) · {{ \App\Support\Phone::display($donation->donor->user->phone) }}<br><strong>নিয়োগকৃত স্টাফ:</strong> {{ optional(optional($donation->assignedStaff)->user)->full_name ?? '—' }}</div></div></div>

  @if($donation->verifications->isNotEmpty())<div class="card mb-3"><div class="card-header">যাচাইয়ের ফলাফল</div><div class="card-body small">
    @foreach($donation->verifications as $v)<div class="mb-2"><strong>{{ $v->result === 'Verified' ? 'যাচাইকৃত' : 'প্রত্যাখ্যাত' }}</strong> — অবস্থা: {{ __('kyaf.condition.'.$v->food_condition) }}, পরিমাণ: {{ bn_digits($v->verified_quantity_meals) }} মিল · {{ optional($v->staff->user)->full_name }} · {{ bst($v->verified_at) }}@if($v->remarks)<br>{{ $v->remarks }}@endif</div>@endforeach</div></div>@endif

  @if($donation->status === 'Submitted')
  <div class="card mb-3 border-primary"><div class="card-header">স্টাফ নিয়োগ করুন</div><div class="card-body">
    @if($team->isEmpty())<div class="text-danger small">আপনার দলে কোনো সক্রিয় স্টাফ নেই।</div>@else
    <form method="post" action="{{ route('manager.donations.assign', $donation) }}" class="row g-2">@csrf
      <div class="col-md-8"><select name="staff_id" class="form-select" required aria-label="স্টাফ"><option value="">স্টাফ নির্বাচন করুন</option>@foreach($team as $s)<option value="{{ $s->staff_id }}">{{ $s->user->full_name }}</option>@endforeach</select></div>
      <div class="col-md-4"><button class="btn btn-accent w-100" type="submit" @disabled($expired)>নিয়োগ দিন</button></div>
    </form>@endif</div></div>
  @endif

  @if($donation->status === 'Verified')
  <div class="card mb-3 border-primary"><div class="card-header">অনুমোদন / প্রত্যাখ্যান</div><div class="card-body">
    <form method="post" action="{{ route('manager.donations.decide', $donation) }}" novalidate>@csrf
      <x-select name="decision" label="সিদ্ধান্ত" required :options="['Approved' => 'অনুমোদন করুন', 'Rejected' => 'প্রত্যাখ্যান করুন']" />
      <x-textarea name="remarks" label="মন্তব্য / প্রত্যাখ্যানের কারণ" rows="2" maxlength="500" help="প্রত্যাখ্যানে কারণ আবশ্যক; দাতাকে দেখানো হবে।" />
      <button class="btn btn-accent" type="submit">সিদ্ধান্ত জমা দিন</button>
    </form></div></div>
  @endif

  @if($donation->status === 'Distributed')
  <div class="card mb-3 border-success"><div class="card-body d-flex justify-content-between align-items-center">
    <div>সব বিতরণ সম্পন্ন হয়েছে। দানটি বন্ধ করুন।</div>
    <form method="post" action="{{ route('manager.donations.close', $donation) }}" data-confirm="দানটি সম্পন্ন হিসেবে বন্ধ করবেন?">@csrf<button class="btn btn-success" type="submit"><i class="bi bi-check2-circle me-1"></i> সম্পন্ন করুন</button></form></div></div>
  @endif

  @if($donation->collection || $donation->distributions->isNotEmpty())<div class="card mb-3"><div class="card-header">সংগ্রহ ও বিতরণ</div><div class="card-body small">
    @if($donation->collection)সংগ্রহ: <strong>{{ $donation->collection->status === 'Collected' ? 'সম্পন্ন, '.bn_digits($donation->collection->collected_quantity_meals).' মিল' : ($donation->collection->status === 'Failed' ? 'ব্যর্থ — '.$donation->collection->remarks : 'নির্ধারিত '.bst($donation->collection->scheduled_at)) }}</strong>@endif
    @foreach($donation->distributions as $b)<div class="mt-1">• {{ $b->point_name }}: {{ bn_digits($b->meals_distributed) }}/{{ bn_digits($b->meals_planned) }} মিল ({{ __('kyaf.dist_status.'.$b->status) }})</div>@endforeach</div></div>@endif
</div>
<div class="col-lg-5"><div class="card"><div class="card-header">অবস্থার ইতিহাস</div><div class="card-body">@include('partials.timeline')</div></div></div>
</div>
@endsection
