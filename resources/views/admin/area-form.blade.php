@extends('layouts.dashboard')
@section('title', $area->exists ? 'সেবা এলাকা সম্পাদনা' : 'নতুন সেবা এলাকা')
@section('content')
<h1 class="dash-title">{{ $area->exists ? 'সেবা এলাকা সম্পাদনা' : 'নতুন সেবা এলাকা' }}</h1>
<form method="post" action="{{ $area->exists ? route('admin.areas.update', $area) : route('admin.areas.store') }}" novalidate>@csrf @if($area->exists) @method('PUT') @endif
<div class="row g-3"><div class="col-lg-6"><div class="card"><div class="card-header">এলাকার তথ্য</div><div class="card-body">
  <x-input name="area_code" label="এলাকার কোড" required :value="$area->area_code" maxlength="12" help="ইংরেজি বড় হাতের অক্ষর/সংখ্যা/হাইফেন, যেমন: DHK-01" />
  <x-input name="area_name" label="এলাকার নাম" required :value="$area->area_name" maxlength="100" />
  <x-select name="district_id" label="জেলা" required :value="$area->district_id" :options="$districts->pluck('name_bn', 'district_id')->all()" />
  <x-select name="manager_id" label="এরিয়া ম্যানেজার" :value="$area->manager_id" :options="$managers->mapWithKeys(fn ($m) => [$m->manager_id => $m->user->full_name])->all()" help="একজন ম্যানেজার শুধু একটি এলাকার দায়িত্বে থাকতে পারেন। ম্যানেজার না থাকলে দান গ্রহণ বন্ধ থাকবে।" placeholder="— কেউ নয় —" />
  <x-select name="status" label="অবস্থা" required :value="$area->status ?: 'Active'" :options="['Active' => 'সক্রিয়', 'Inactive' => 'নিষ্ক্রিয়']" />
</div></div></div>
<div class="col-lg-6"><div class="card"><div class="card-header">এলাকার অন্তর্ভুক্ত থানা<span class="req">*</span></div><div class="card-body" data-area-thanas data-initial="{{ $selected->pluck('thana_id')->implode(',') }}">
  <p class="small text-muted">একটি থানা শুধু একটি সেবা এলাকায় থাকতে পারে। থানা নির্বাচন করে "যোগ করুন" চাপুন।</p>
  @include('partials.location-picker', ['name' => 'picker_thana', 'noName' => true, 'selected' => null])
  <button type="button" class="btn btn-outline-accent btn-sm" data-chip-add disabled><i class="bi bi-plus-lg"></i> যোগ করুন</button> <span class="text-danger small" data-chip-msg></span>
  <hr><div data-chip-list>@foreach($selected as $t)<span class="thana-chip" data-id="{{ $t->thana_id }}"><span>{{ $t->name_bn }}, {{ $t->upazila->name_bn }}</span><input type="hidden" name="thana_ids[]" value="{{ $t->thana_id }}"><button type="button" aria-label="সরান">&times;</button></span>@endforeach</div>
  @error('thana_ids')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
</div></div></div></div>
<div class="mt-3"><button class="btn btn-accent btn-lg" type="submit"><i class="bi bi-save me-1"></i> সংরক্ষণ করুন</button> <a href="{{ route('admin.areas') }}" class="btn btn-light btn-lg">বাতিল</a></div>
</form>
@endsection
