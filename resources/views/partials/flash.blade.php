@if (session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert"><i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="বন্ধ করুন"></button></div>
@endif
@if (session('error'))
  <div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="বন্ধ করুন"></button></div>
@endif
@if (session('invite_link'))
  <div class="alert alert-warning">
    <strong><i class="bi bi-link-45deg"></i> আমন্ত্রণ লিংক (একবারই দেখানো হবে):</strong>
    <div class="input-group mt-2"><input type="text" class="form-control" id="inviteLink" readonly value="{{ session('invite_link') }}"><button class="btn btn-outline-dark" type="button" onclick="navigator.clipboard.writeText(document.getElementById('inviteLink').value);this.textContent='কপি হয়েছে'">কপি</button></div>
    <small class="text-muted">লিংকটি ৭২ ঘণ্টা বৈধ এবং একবারই ব্যবহার করা যাবে। ইমেইল সার্ভার কনফিগার না থাকায় লিংকটি নিজে আবেদনকারীকে পাঠান।</small>
  </div>
@endif
@if (session('dev_link'))
  <div class="alert alert-info"><strong>স্থানীয় পরীক্ষার জন্য:</strong> ইমেইল সার্ভার নেই, তাই রিসেট লিংকটি এখানে দেখানো হলো: <a href="{{ session('dev_link') }}">{{ session('dev_link') }}</a></div>
@endif
@if ($errors->any())
  <div class="alert alert-danger"><strong><i class="bi bi-exclamation-octagon-fill me-1"></i> ফর্মে কিছু ভুল আছে:</strong>
    <ul class="mb-0 mt-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif
@if (session('import_errors'))
  <div class="alert alert-secondary"><strong>বাদ পড়া সারি (প্রথম ১০টি):</strong><ul class="mb-0">@foreach (session('import_errors') as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif
