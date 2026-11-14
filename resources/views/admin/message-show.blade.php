@extends('layouts.dashboard')
@section('title', 'বার্তা')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="dash-title mb-0">{{ $m->subject }}</h1><a href="{{ route('admin.messages') }}" class="btn btn-light">← তালিকা</a></div>
<div class="card mb-3"><div class="card-body">
  <dl class="row small"><dt class="col-sm-3">প্রেরক</dt><dd class="col-sm-9">{{ $m->sender_name }}</dd><dt class="col-sm-3">ইমেইল</dt><dd class="col-sm-9"><a href="mailto:{{ $m->email }}">{{ $m->email }}</a></dd><dt class="col-sm-3">মোবাইল</dt><dd class="col-sm-9">{{ $m->phone ? \App\Support\Phone::display($m->phone) : '—' }}</dd><dt class="col-sm-3">তারিখ</dt><dd class="col-sm-9">{{ bst($m->created_at) }}</dd></dl>
  <hr><div style="white-space:pre-wrap">{{ $m->message_body }}</div></div></div>
<form method="post" action="{{ route('admin.messages.update', $m) }}" class="card"><div class="card-body d-flex gap-2 align-items-end flex-wrap">@csrf @method('PUT')
  <div><label class="form-label small mb-1">অবস্থা পরিবর্তন</label><select name="status" class="form-select">@foreach(__('kyaf.msg_status') as $k => $v)<option value="{{ $k }}" @selected($m->status===$k)>{{ $v }}</option>@endforeach</select></div>
  <button class="btn btn-accent" type="submit">হালনাগাদ</button>@if($m->handler)<small class="text-muted">সর্বশেষ: {{ $m->handler->full_name }}</small>@endif</div></form>
@endsection
