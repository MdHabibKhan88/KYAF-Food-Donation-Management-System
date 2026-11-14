@extends('layouts.dashboard')
@section('title', 'দান পর্যালোচনা')
@section('content')
<h1 class="dash-title">দান পর্যালোচনা — {{ $area->area_name }}</h1>
@include('partials.filters-donations')
<div class="card">@if($donations->isEmpty())<div class="empty-state"><i class="bi bi-inbox"></i>কোনো দান পাওয়া যায়নি।</div>@else
<div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>কোড</th><th>খাবার</th><th>মিল</th><th>পিকআপ থানা</th><th>স্টাফ</th><th>অবস্থা</th><th>জমার তারিখ</th><th></th></tr></thead><tbody>
@foreach($donations as $d)<tr><td><strong>{{ $d->donation_code }}</strong></td><td>{{ $d->food_name }}</td><td>{{ bn_digits($d->quantity_meals) }}</td><td>{{ $d->pickupThana->name_bn }}</td><td>{{ optional(optional($d->assignedStaff)->user)->full_name ?? '—' }}</td><td><x-status :s="$d->status" /></td><td>{{ bst($d->submitted_at, 'd/m/Y') }}</td><td><a class="btn btn-sm btn-outline-accent" href="{{ route('manager.donations.show', $d) }}">খুলুন</a></td></tr>@endforeach
</tbody></table></div>@endif</div>
<div class="mt-3">{{ $donations->links() }}</div>
@endsection
