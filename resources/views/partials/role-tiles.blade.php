<div class="row g-3">
  @foreach ([['admin','bi-shield-lock','অ্যাডমিন','সিস্টেম পরিচালনা'],['manager','bi-diagram-3','এরিয়া ম্যানেজার','সেবা এলাকা ব্যবস্থাপনা'],['staff','bi-person-badge','স্টাফ','যাচাই, সংগ্রহ ও বিতরণ'],['donor','bi-heart','দাতা','খাবার দান করুন']] as [$slug,$icon,$name,$desc])
    <div class="col-6 col-md-3">
      <a class="role-tile" href="{{ route('login.show', $slug) }}"><i class="bi {{ $icon }}"></i><div class="fw-bold mt-2">{{ $name }}</div><small class="text-muted">{{ $desc }}</small></a>
    </div>
  @endforeach
</div>
