@extends('layouts.dashboard')
@section('title', 'আমার দল')
@section('content')
<h1 class="dash-title">আমার দল ({{ bn_digits($active) }}/{{ bn_digits($cap) }} সক্রিয়)</h1>
@if($vacancies > 0)<div class="alert alert-warning"><i class="bi bi-person-exclamation me-1"></i> দলে {{ bn_digits($vacancies) }}টি শূন্যপদ আছে। <a href="{{ route('manager.applications') }}">স্টাফ আবেদন দেখুন</a></div>@endif
<div class="card">@if($staff->isEmpty())<div class="empty-state"><i class="bi bi-people"></i>দলে এখনো কোনো স্টাফ নেই।</div>@else
<div class="table-responsive"><table class="table mb-0"><thead><tr><th>নাম</th><th>মোবাইল</th><th>যোগদান</th><th>চলমান দান</th><th>অবস্থা</th><th>কাজ</th></tr></thead><tbody>
@foreach($staff as $s)<tr><td>{{ $s->user->full_name }}<div class="small text-muted">{{ $s->user->email }}</div></td><td>{{ \App\Support\Phone::display($s->user->phone) }}</td><td>{{ bst($s->joined_at, 'd/m/Y') }}</td><td>{{ bn_digits($s->open_count) }}</td>
<td><span class="badge text-bg-{{ ['Active' => 'success', 'Suspended' => 'warning', 'Removed' => 'secondary'][$s->status] }}">{{ ['Active' => 'সক্রিয়', 'Suspended' => 'স্থগিত', 'Removed' => 'অপসারিত'][$s->status] }}</span></td>
<td>@if($s->status === 'Active')
  <form method="post" action="{{ route('manager.team.update', $s) }}" class="d-flex gap-1 flex-wrap" data-confirm="স্টাফের অবস্থা পরিবর্তন করবেন?">@csrf<input name="reason" class="form-control form-control-sm" style="width:150px" placeholder="কারণ" required aria-label="কারণ"><button class="btn btn-sm btn-outline-warning" name="action" value="suspend" type="submit">স্থগিত</button><button class="btn btn-sm btn-outline-danger" name="action" value="remove" type="submit">অপসারণ</button></form>
@elseif($s->status === 'Suspended')
  <form method="post" action="{{ route('manager.team.update', $s) }}">@csrf<button class="btn btn-sm btn-outline-success" name="action" value="reactivate" type="submit">পুনরায় সক্রিয়</button></form>@endif</td></tr>@endforeach
</tbody></table></div>@endif</div>
@endsection
