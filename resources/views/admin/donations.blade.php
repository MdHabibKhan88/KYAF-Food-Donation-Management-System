@extends('layouts.dashboard')
@section('title', 'দান ব্যবস্থাপনা')
@section('content')
<h1 class="dash-title">দান ব্যবস্থাপনা</h1>
<form method="get" class="card mb-3"><div class="card-body"><div class="row g-2 align-items-end">
  <div class="col-md-3"><label class="form-label small mb-1">খুঁজুন</label><input class="form-control" name="q" value="{{ request('q') }}" placeholder="কোড / খাবার"></div>
  <div class="col-md-2"><label class="form-label small mb-1">অবস্থা</label><select class="form-select" name="status"><option value="">সব</option>@foreach(array_keys(__('kyaf.status')) as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ __('kyaf.status.'.$s) }}</option>@endforeach</select></div>
  <div class="col-md-2"><label class="form-label small mb-1">সেবা এলাকা</label><select class="form-select" name="area_id"><option value="">সব</option>@foreach($areas as $a)<option value="{{ $a->area_id }}" @selected(request('area_id')==$a->area_id)>{{ $a->area_name }}</option>@endforeach</select></div>
  <div class="col-md-2"><label class="form-label small mb-1">থেকে</label><input type="date" class="form-control" name="from" value="{{ request('from') }}"></div>
  <div class="col-md-2"><label class="form-label small mb-1">পর্যন্ত</label><input type="date" class="form-control" name="to" value="{{ request('to') }}"></div>
  <div class="col-md-1 d-grid"><button class="btn btn-accent" type="submit" aria-label="ফিল্টার"><i class="bi bi-funnel"></i></button></div>
</div></div></form>
<div class="card">@if($donations->isEmpty())<div class="empty-state"><i class="bi bi-inbox"></i>কোনো দান পাওয়া যায়নি।</div>@else
<div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>কোড</th><th>খাবার</th><th>মিল</th><th>সেবা এলাকা</th><th>স্টাফ</th><th>অবস্থা</th><th>তারিখ</th><th></th></tr></thead><tbody>
@foreach($donations as $d)<tr><td><strong>{{ $d->donation_code }}</strong></td><td>{{ $d->food_name }}</td><td>{{ bn_digits($d->quantity_meals) }}</td><td>{{ optional($d->area)->area_name }}</td><td>{{ optional(optional($d->assignedStaff)->user)->full_name ?? '—' }}</td><td><x-status :s="$d->status" /></td><td>{{ bst($d->submitted_at, 'd/m/Y') }}</td><td><a class="btn btn-sm btn-outline-accent" href="{{ route('admin.donations.show', $d) }}">খুলুন</a></td></tr>@endforeach
</tbody></table></div>@endif</div>
<div class="mt-3">{{ $donations->links() }}</div>
@endsection
