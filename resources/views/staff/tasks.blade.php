@extends('layouts.dashboard')
@section('title', $history ? 'দানের ইতিহাস' : 'নিয়োগকৃত দান')
@section('content')
<h1 class="dash-title">{{ $history ? 'দানের ইতিহাস (সমাপ্ত)' : 'আমার নিয়োগকৃত দান' }}</h1>
<form method="get" class="card mb-3"><div class="card-body"><div class="row g-2 align-items-end">
  <div class="col-md-4"><label class="form-label small mb-1">কোড</label><input class="form-control" name="q" value="{{ request('q') }}" placeholder="DON-..."></div>
  <div class="col-md-3"><label class="form-label small mb-1">অবস্থা</label><select class="form-select" name="status"><option value="">সব</option>@foreach(array_keys(__('kyaf.status')) as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ __('kyaf.status.'.$s) }}</option>@endforeach</select></div>
  <div class="col-md-3 d-flex gap-2"><button class="btn btn-accent flex-fill" type="submit">ফিল্টার</button><a href="{{ url()->current() }}" class="btn btn-light">রিসেট</a></div>
</div></div></form>
<div class="card">@if($donations->isEmpty())<div class="empty-state"><i class="bi bi-inbox"></i>কোনো দান পাওয়া যায়নি।</div>@else
<div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>কোড</th><th>খাবার</th><th>মিল</th><th>পিকআপ থানা</th><th>মেয়াদ</th><th>অবস্থা</th><th></th></tr></thead><tbody>
@foreach($donations as $d)<tr><td><strong>{{ $d->donation_code }}</strong></td><td>{{ $d->food_name }}</td><td>{{ bn_digits($d->quantity_meals) }}</td><td>{{ $d->pickupThana->name_bn }}</td><td>{{ bst($d->expiryInstant(), 'd/m/Y h:i A') }}</td><td><x-status :s="$d->status" /></td><td><a class="btn btn-sm btn-outline-accent" href="{{ route('staff.tasks.show', $d) }}">বিস্তারিত</a></td></tr>@endforeach
</tbody></table></div>@endif</div>
<div class="mt-3">{{ $donations->links() }}</div>
@endsection
