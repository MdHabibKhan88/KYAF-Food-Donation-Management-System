@extends('layouts.dashboard')
@section('title', 'দাতা ড্যাশবোর্ড')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
  <div><h1 class="dash-title mb-0">স্বাগতম, {{ auth()->user()->full_name }}</h1><div class="text-muted">দাতা আইডি: <strong>{{ $donor->donor_id }}</strong></div></div>
  <a href="{{ route('donor.donations.create') }}" class="btn btn-accent btn-lg"><i class="bi bi-plus-circle me-1"></i> নতুন দান জমা দিন</a>
</div>
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">@include('partials.kpi', ['icon' => 'bi-box-seam', 'value' => $counts->sum(), 'label' => 'মোট দান'])</div>
  <div class="col-6 col-lg-3">@include('partials.kpi', ['icon' => 'bi-hourglass-split', 'value' => collect(['Submitted','Assigned','Verified','Approved'])->sum(fn($s) => $counts[$s] ?? 0), 'label' => 'চলমান'])</div>
  <div class="col-6 col-lg-3">@include('partials.kpi', ['icon' => 'bi-check2-circle', 'value' => $counts['Completed'] ?? 0, 'label' => 'সম্পন্ন'])</div>
  <div class="col-6 col-lg-3">@include('partials.kpi', ['icon' => 'bi-basket', 'value' => $totalMeals, 'label' => 'সংগৃহীত মিল'])</div>
</div>
<div class="row g-3">
  <div class="col-lg-7"><div class="card"><div class="card-header d-flex justify-content-between"><span>সাম্প্রতিক দান</span><a href="{{ route('donor.donations') }}" class="small">সব দেখুন</a></div>
    @if($recent->isEmpty())<div class="empty-state"><i class="bi bi-box-seam"></i>এখনো কোনো দান জমা দেননি।<div class="mt-2"><a class="btn btn-accent btn-sm" href="{{ route('donor.donations.create') }}">প্রথম দান জমা দিন</a></div></div>
    @else<div class="table-responsive"><table class="table mb-0"><thead><tr><th>কোড</th><th>খাবার</th><th>মিল</th><th>অবস্থা</th></tr></thead><tbody>
      @foreach($recent as $d)<tr><td><a href="{{ route('donor.donations.show', $d) }}">{{ $d->donation_code }}</a></td><td>{{ $d->food_name }}</td><td>{{ bn_digits($d->quantity_meals) }}</td><td><x-status :s="$d->status" /></td></tr>@endforeach
    </tbody></table></div>@endif</div></div>
  <div class="col-lg-5"><div class="card h-100"><div class="card-header d-flex justify-content-between"><span>সাম্প্রতিক বিজ্ঞপ্তি</span><a href="{{ route('notifications.index') }}" class="small">সব দেখুন</a></div>
    <div class="list-group list-group-flush">@forelse($notifications as $n)<div class="list-group-item {{ $n->is_read ? '' : 'bg-light fw-semibold' }}"><div class="small">{{ $n->title }}</div><div class="small text-muted">{{ $n->message }}</div><div class="small text-muted">{{ bst($n->created_at) }}</div></div>@empty<div class="empty-state py-4"><i class="bi bi-bell"></i>কোনো বিজ্ঞপ্তি নেই</div>@endforelse</div></div></div>
</div>
<div class="card mt-3"><div class="card-body small"><i class="bi bi-person-vcard me-1"></i> <strong>আপনার প্রোফাইল:</strong> {{ $donor->thana->fullName() }} · {{ $donor->address_line }} · <a href="{{ route('profile.edit') }}">প্রোফাইল সম্পাদনা</a></div></div>
@endsection
