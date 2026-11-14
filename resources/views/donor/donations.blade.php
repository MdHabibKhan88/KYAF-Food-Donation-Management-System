@extends('layouts.dashboard')
@section('title', 'আমার দানসমূহ')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="dash-title mb-0">আমার দানসমূহ</h1><a href="{{ route('donor.donations.create') }}" class="btn btn-accent"><i class="bi bi-plus-circle me-1"></i> নতুন দান</a></div>
@include('partials.filters-donations')
<div class="card">@if($donations->isEmpty())<div class="empty-state"><i class="bi bi-inbox"></i>কোনো দান পাওয়া যায়নি।</div>@else
<div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>কোড</th><th>খাবার</th><th>মিল</th><th>সেবা এলাকা</th><th>অবস্থা</th><th>জমার তারিখ</th><th></th></tr></thead><tbody>
@foreach($donations as $d)<tr><td><strong>{{ $d->donation_code }}</strong></td><td>{{ $d->food_name }}</td><td>{{ bn_digits($d->quantity_meals) }}</td><td>{{ optional($d->area)->area_name }}</td><td><x-status :s="$d->status" /></td><td>{{ bst($d->submitted_at, 'd/m/Y') }}</td><td><a class="btn btn-sm btn-outline-accent" href="{{ route('donor.donations.show', $d) }}">বিস্তারিত</a></td></tr>@endforeach
</tbody></table></div>@endif</div>
<div class="mt-3">{{ $donations->links() }}</div>
@endsection
