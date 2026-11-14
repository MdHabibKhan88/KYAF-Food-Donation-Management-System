@extends('layouts.dashboard')
@section('title', 'কাজ '.$donation->donation_code)
@section('content')
@php
  $expired = $donation->isExpired() && ! $donation->isFinal();
  $verifiedQty = $donation->verifiedQuantity();
  $col = $donation->collection;
  $collected = $col && $col->status === 'Collected' ? (int) $col->collected_quantity_meals : 0;
  $planned = (int) $donation->distributions->where('status', '!=', 'Cancelled')->sum('meals_planned');
@endphp
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3"><h1 class="dash-title mb-0">{{ $donation->donation_code }} <x-status :s="$donation->status" /></h1><a href="{{ route('staff.tasks') }}" class="btn btn-light">← তালিকা</a></div>
<div class="card mb-3"><div class="card-body">@include('partials.donation-progress')</div></div>
@if($expired)<div class="alert alert-danger"><i class="bi bi-exclamation-octagon me-1"></i> এই খাবারের মেয়াদ উত্তীর্ণ হয়েছে। মেয়াদোত্তীর্ণ খাবার যাচাই, সংগ্রহ বা বিতরণ করা যাবে না।</div>@endif
<div class="row g-3">
<div class="col-lg-7">
  <div class="card mb-3"><div class="card-header">দানের তথ্য</div><div class="card-body">@include('partials.donation-details')
    <hr><div class="small"><strong>দাতা:</strong> {{ $donation->donor->user->full_name }} · {{ \App\Support\Phone::display($donation->donor->user->phone) }}</div></div></div>

  {{-- ===== Step 1: verification ===== --}}
  @if($donation->status === 'Assigned')
  <div class="card mb-3 border-warning"><div class="card-header">ধাপ ১: খাবার যাচাই করুন</div><div class="card-body">
    <form method="post" action="{{ route('staff.tasks.verify', $donation) }}" novalidate>@csrf
      <div class="row">
        <x-select class="col-md-6" name="result" label="যাচাইয়ের ফলাফল" required :options="['Verified' => 'যাচাই সম্পন্ন (গ্রহণযোগ্য)', 'Rejected' => 'প্রত্যাখ্যান']" />
        <x-select class="col-md-6" name="food_condition" label="খাবারের অবস্থা" required :options="__('kyaf.condition')" />
        <x-input class="col-md-6" name="verified_quantity_meals" label="যাচাইকৃত পরিমাণ (মিল)" inputmode="numeric" bn :value="$donation->quantity_meals" :help="'ঘোষিত পরিমাণ: '.bn_digits($donation->quantity_meals)" />
      </div>
      <x-textarea name="remarks" label="মন্তব্য / প্রত্যাখ্যানের কারণ" rows="2" maxlength="500" help="প্রত্যাখ্যান করলে কারণ লেখা আবশ্যক; দাতাকে কারণটি দেখানো হবে।" />
      <button class="btn btn-accent" type="submit" @disabled($expired)><i class="bi bi-clipboard-check me-1"></i> যাচাই জমা দিন</button>
    </form></div></div>
  @endif

  {{-- ===== Step 2: collection ===== --}}
  @if($donation->status === 'Approved')
  <div class="card mb-3 border-warning"><div class="card-header">ধাপ ২: খাবার সংগ্রহ</div><div class="card-body">
    <p class="small text-muted">যাচাইকৃত পরিমাণ: <strong>{{ bn_digits($verifiedQty ?? $donation->quantity_meals) }}</strong> মিল @if($col)· নির্ধারিত সময়: {{ bst($col->scheduled_at) }}@endif
      @if($col && $col->status === 'Failed')<br><span class="text-danger">আগের চেষ্টা ব্যর্থ: {{ $col->remarks }}</span>@endif</p>
    <form method="post" action="{{ route('staff.tasks.collect', $donation) }}" novalidate>@csrf
      <div class="row">
        <x-select class="col-md-6" name="outcome" label="ফলাফল" required :options="['collected' => 'খাবার সংগ্রহ হয়েছে', 'failed' => 'সংগ্রহ করা যায়নি']" />
        <x-input class="col-md-6" name="collected_quantity_meals" label="সংগৃহীত পরিমাণ (মিল)" inputmode="numeric" bn :value="$verifiedQty ?? $donation->quantity_meals" />
      </div>
      <x-textarea name="remarks" label="মন্তব্য / ব্যর্থতার কারণ" rows="2" maxlength="500" />
      <button class="btn btn-accent" type="submit" @disabled($expired)><i class="bi bi-truck me-1"></i> জমা দিন</button>
    </form></div></div>
  @endif

  {{-- ===== Step 3: distribution ===== --}}
  @if(in_array($donation->status, ['Collected', 'Distributed', 'Completed']))
  <div class="card mb-3"><div class="card-header d-flex justify-content-between"><span>ধাপ ৩: বিতরণ</span><small class="text-muted">সংগৃহীত {{ bn_digits($collected) }} · পরিকল্পিত {{ bn_digits($planned) }} · বিতরিত {{ bn_digits($donation->distributions->sum('meals_distributed')) }}</small></div>
    @if($donation->distributions->isEmpty())<div class="empty-state py-4">এখনো কোনো বিতরণ ব্যাচ নেই।</div>@else
    <div class="table-responsive"><table class="table mb-0"><thead><tr><th>কেন্দ্র</th><th>সময়</th><th>পরিকল্পিত</th><th>বিতরিত</th><th>অবস্থা</th><th></th></tr></thead><tbody>
    @foreach($donation->distributions as $b)<tr>
      <td>{{ $b->point_name }}<div class="small text-muted">{{ $b->point_address }} @if($b->is_public)<span class="badge text-bg-info">প্রকাশ্য</span>@endif</div></td>
      <td class="small">{{ bst($b->scheduled_start) }}<br>{{ bst($b->scheduled_end) }}</td><td>{{ bn_digits($b->meals_planned) }}</td><td>{{ bn_digits($b->meals_distributed) }}</td>
      <td><span class="badge text-bg-secondary">{{ __('kyaf.dist_status.'.$b->status) }}</span></td>
      <td>@if($donation->status === 'Collected' && $b->status === 'Planned')
        <form method="post" action="{{ route('staff.distributions.complete', $b) }}" class="d-flex gap-1 mb-1">@csrf<input class="form-control form-control-sm" style="width:90px" name="meals_distributed" value="{{ $b->meals_planned }}" inputmode="numeric" data-bn-digits aria-label="বিতরিত মিল"><button class="btn btn-sm btn-accent" type="submit" @disabled($expired)>সম্পন্ন</button></form>
        <form method="post" action="{{ route('staff.distributions.cancel', $b) }}" data-confirm="ব্যাচটি বাতিল করবেন?">@csrf<button class="btn btn-sm btn-outline-danger" type="submit">বাতিল</button></form>@endif</td></tr>@endforeach
    </tbody></table></div>@endif</div>

  @if($donation->status === 'Collected')
  <div class="card mb-3"><div class="card-header">নতুন বিতরণ ব্যাচ পরিকল্পনা</div><div class="card-body">
    <form method="post" action="{{ route('staff.distributions.plan', $donation) }}" novalidate>@csrf
      <div class="row">
        <x-input class="col-md-6" name="point_name" label="বিতরণ কেন্দ্রের নাম" required maxlength="100" />
        <x-input class="col-md-6" name="meals_planned" label="পরিকল্পিত মিল" required inputmode="numeric" bn :help="'অবশিষ্ট: '.bn_digits(max(0, $collected - $planned))" />
        <x-input class="col-md-12" name="point_address" label="কেন্দ্রের ঠিকানা" required maxlength="250" />
      </div>
      <label class="form-label fw-semibold">কেন্দ্রের থানা<span class="req">*</span></label>
      @include('partials.location-picker', ['name' => 'thana_id', 'required' => true, 'selected' => $donation->pickup_thana_id])
      <div class="row">
        <x-input class="col-md-6" name="scheduled_start" type="datetime-local" label="শুরুর সময়" required />
        <x-input class="col-md-6" name="scheduled_end" type="datetime-local" label="শেষের সময়" required />
      </div>
      <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_public" value="1" id="is_public" @checked(old('is_public'))><label class="form-check-label" for="is_public">"খাবার খুঁজুন" পাতায় প্রকাশ করুন (দাতা/স্টাফের ব্যক্তিগত তথ্য দেখানো হয় না)</label></div>
      <x-textarea name="remarks" label="মন্তব্য" rows="2" maxlength="500" />
      <button class="btn btn-accent" type="submit" @disabled($expired)><i class="bi bi-calendar-plus me-1"></i> ব্যাচ যুক্ত করুন</button>
    </form></div></div>

  <form method="post" action="{{ route('staff.tasks.finish', $donation) }}" data-confirm="সব বিতরণ শেষ হয়েছে বলে নিশ্চিত করছেন? এরপর ম্যানেজার দানটি সম্পন্ন করবেন।">@csrf
    <button class="btn btn-success" type="submit" @disabled($expired)><i class="bi bi-flag-fill me-1"></i> বিতরণ সমাপ্ত করুন</button></form>
  @endif
  @endif
</div>
<div class="col-lg-5"><div class="card"><div class="card-header">অবস্থার ইতিহাস</div><div class="card-body">@include('partials.timeline')</div></div></div>
</div>
@endsection
