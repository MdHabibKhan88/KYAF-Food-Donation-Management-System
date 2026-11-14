@extends('layouts.dashboard')
@section('title', 'আবেদন #'.$app->application_id)
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3"><h1 class="dash-title mb-0">আবেদন #{{ bn_digits($app->application_id) }}</h1><a href="{{ route('admin.applications') }}" class="btn btn-light">← তালিকা</a></div>
<div class="row g-3"><div class="col-lg-7"><div class="card"><div class="card-header">আবেদনকারীর তথ্য</div><div class="card-body">@include('partials.application-details')
  <div class="small mt-3">এরিয়া ম্যানেজার: {{ optional(optional($app->area->manager)->user)->full_name ?? '—' }}</div>
  @if($app->cv_file_path)<a href="{{ route('admin.applications.cv', $app) }}" class="btn btn-sm btn-outline-secondary mt-2"><i class="bi bi-file-earmark-pdf me-1"></i> সিভি ডাউনলোড</a>@endif</div></div></div>
<div class="col-lg-5">@if(in_array($app->status, ['Submitted','UnderReview','Expired','Approved']))
  <div class="card border-danger"><div class="card-header text-danger">অ্যাডমিন ওভাররাইড (কারণ আবশ্যক)</div><div class="card-body">
    @if($app->status !== 'Approved')<form method="post" action="{{ route('admin.applications.approve', $app) }}" class="mb-3" data-confirm="ওভাররাইড করে আবেদন অনুমোদন করবেন?">@csrf<x-textarea name="reason" label="অনুমোদনের কারণ" required rows="2" maxlength="300" /><button class="btn btn-success w-100" type="submit">অনুমোদন ও আমন্ত্রণ লিংক</button></form><hr>@endif
    <form method="post" action="{{ route('admin.applications.reject', $app) }}" data-confirm="ওভাররাইড করে আবেদন প্রত্যাখ্যান করবেন?">@csrf
      <x-textarea name="remarks" label="প্রত্যাখ্যানের কারণ (আবেদনকারীর জন্য)" required rows="2" maxlength="500" />
      <x-textarea name="reason" label="ওভাররাইডের কারণ (অডিট লগের জন্য)" required rows="2" maxlength="300" />
      <button class="btn btn-outline-danger w-100" type="submit">প্রত্যাখ্যান করুন</button></form>
  </div></div>@endif</div></div>
@endsection
