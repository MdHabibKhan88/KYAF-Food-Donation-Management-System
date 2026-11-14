@extends('layouts.dashboard')
@section('title', 'সেটিংস')
@section('content')
<h1 class="dash-title">সিস্টেম সেটিংস</h1>
<div class="alert alert-info small">স্টাফ দলের আকার (৫ জন) এবং পাসওয়ার্ড রিসেট লিংকের মেয়াদ (৩০ মিনিট) ব্যবসায়িক নিয়ম অনুযায়ী নির্দিষ্ট; এগুলো পরিবর্তনযোগ্য নয়।</div>
<div class="card" style="max-width:760px"><div class="card-body">
  <form method="post" action="{{ route('admin.settings.update') }}" novalidate>@csrf @method('PUT')
    @foreach($fields as $key => [$label, $rule])
      <x-input :name="$key" :label="$label" required :value="$values[$key]" :bn="str_contains($rule, 'integer')" />
    @endforeach
    <button class="btn btn-accent" type="submit"><i class="bi bi-save me-1"></i> সংরক্ষণ করুন</button>
  </form></div></div>
@endsection
