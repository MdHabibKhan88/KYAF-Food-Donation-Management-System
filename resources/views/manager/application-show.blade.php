@extends('layouts.dashboard')
@section('title', 'আবেদন #'.$app->application_id)
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3"><h1 class="dash-title mb-0">আবেদন পর্যালোচনা</h1><a href="{{ route('manager.applications') }}" class="btn btn-light">← তালিকা</a></div>
<div class="row g-3"><div class="col-lg-7"><div class="card"><div class="card-header">আবেদনকারীর তথ্য</div><div class="card-body">@include('partials.application-details')
  @if($app->cv_file_path)<a href="{{ route('manager.applications.cv', $app) }}" class="btn btn-sm btn-outline-secondary mt-3"><i class="bi bi-file-earmark-pdf me-1"></i> সিভি ডাউনলোড</a>@endif</div></div></div>
<div class="col-lg-5">
  @if(in_array($app->status, ['Submitted','UnderReview','Expired']))
  <div class="card mb-3"><div class="card-header">সিদ্ধান্ত</div><div class="card-body">
    @unless($vacancy)<div class="alert alert-warning small">দলে কোনো শূন্যপদ নেই; শূন্যপদ তৈরি না হওয়া পর্যন্ত অনুমোদন করা যাবে না।</div>@endunless
    @if($app->status === 'Submitted')<form method="post" action="{{ route('manager.applications.review', $app) }}" class="mb-2">@csrf<button class="btn btn-outline-accent btn-sm" type="submit">পর্যালোচনাধীন করুন</button></form>@endif
    <form method="post" action="{{ route('manager.applications.approve', $app) }}" data-confirm="আবেদন অনুমোদন করে আমন্ত্রণ লিংক তৈরি করবেন?" class="mb-3">@csrf<button class="btn btn-success w-100" type="submit" @disabled(! $vacancy)><i class="bi bi-check2-circle me-1"></i> অনুমোদন ও আমন্ত্রণ লিংক তৈরি</button></form>
    <hr>
    <form method="post" action="{{ route('manager.applications.reject', $app) }}" data-confirm="আবেদনটি প্রত্যাখ্যান করবেন?">@csrf
      <x-textarea name="remarks" label="প্রত্যাখ্যানের কারণ" required rows="2" maxlength="500" />
      <button class="btn btn-outline-danger w-100" type="submit"><i class="bi bi-x-circle me-1"></i> প্রত্যাখ্যান করুন</button>
    </form></div></div>
  @elseif($app->status === 'Approved')
    <div class="card"><div class="card-body small">আবেদনটি অনুমোদিত হয়েছে। আমন্ত্রণ লিংকটি তৈরির সময় একবারই দেখানো হয়। হারিয়ে গেলে আবেদনটি প্রত্যাখ্যান করে নতুন করে আবেদন করতে বলুন, অথবা মেয়াদ শেষ হলে আবার অনুমোদন করুন।
    <form method="post" action="{{ route('manager.applications.reject', $app) }}" class="mt-3" data-confirm="আমন্ত্রণ বাতিল করে আবেদন প্রত্যাখ্যান করবেন?">@csrf<x-textarea name="remarks" label="বাতিলের কারণ" required rows="2" /><button class="btn btn-outline-danger btn-sm" type="submit">আমন্ত্রণ বাতিল</button></form></div></div>
  @endif
</div></div>
@endsection
