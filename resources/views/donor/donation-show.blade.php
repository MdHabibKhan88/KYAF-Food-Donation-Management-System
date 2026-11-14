@extends('layouts.dashboard')
@section('title', 'দান '.$donation->donation_code)
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3"><h1 class="dash-title mb-0">দান: {{ $donation->donation_code }} <x-status :s="$donation->status" /></h1><a href="{{ route('donor.donations') }}" class="btn btn-light">← তালিকায় ফিরুন</a></div>
<div class="card mb-3"><div class="card-body">@include('partials.donation-progress')</div></div>
<div class="row g-3">
  <div class="col-lg-7"><div class="card mb-3"><div class="card-header">দানের তথ্য</div><div class="card-body">@include('partials.donation-details')</div></div>
    @php $rejected = $donation->histories->whereIn('to_status', ['Rejected', 'Cancelled'])->last(); @endphp
    @if($rejected && $rejected->remarks)<div class="alert alert-danger"><strong>কারণ:</strong> {{ $rejected->remarks }}</div>@endif
    @if($donation->collection && $donation->collection->status === 'Collected')<div class="card mb-3"><div class="card-header">সংগ্রহ ও বিতরণ</div><div class="card-body small">সংগৃহীত: {{ bn_digits($donation->collection->collected_quantity_meals) }} মিল ({{ bst($donation->collection->collected_at) }})
      @if($donation->distributions->isNotEmpty())<br>বিতরিত: {{ bn_digits($donation->distributions->sum('meals_distributed')) }} মিল@endif</div></div>@endif
  </div>
  <div class="col-lg-5">
    <div class="card mb-3"><div class="card-header">অবস্থার ইতিহাস</div><div class="card-body">@include('partials.timeline')</div></div>
    @if(in_array($donation->status, ['Submitted','Assigned','Verified','Approved']))
      <div class="card border-danger"><div class="card-header text-danger">দান বাতিল করুন</div><div class="card-body">
        <form method="post" action="{{ route('donor.donations.cancel', $donation) }}" data-confirm="আপনি কি নিশ্চিত যে এই দান বাতিল করতে চান? এই কাজ ফেরানো যাবে না.">@csrf
          <x-textarea name="reason" label="বাতিলের কারণ" required rows="2" maxlength="500" />
          <button class="btn btn-danger" type="submit"><i class="bi bi-x-circle me-1"></i> বাতিল করুন</button>
        </form><small class="text-muted">সংগ্রহের আগে পর্যন্ত বাতিল করা যায়।</small></div></div>
    @endif
  </div>
</div>
@endsection
