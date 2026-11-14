<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <div><h5 class="modal-title fw-bold" id="loginModalLabel"><i class="bi bi-box-arrow-in-right text-success me-1"></i> লগইন করুন</h5><small class="text-muted">আপনার ভূমিকা অনুযায়ী লগইন পাতা নির্বাচন করুন</small></div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="বন্ধ করুন"></button>
      </div>
      <div class="modal-body">
        @include('partials.role-tiles')
      </div>
      <div class="modal-footer border-0 pt-0 justify-content-center">
        <span class="text-muted">দাতা হিসেবে নতুন?</span> <a href="{{ route('register') }}" class="fw-bold">এখনই নিবন্ধন করুন</a>
      </div>
    </div>
  </div>
</div>
