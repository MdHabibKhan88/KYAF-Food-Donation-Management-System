<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title"><i class="bi bi-question-circle me-1"></i> নিশ্চিত করুন</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="বন্ধ করুন"></button></div>
      <div class="modal-body" data-confirm-text>আপনি কি নিশ্চিত?</div>
      <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">না, ফিরে যান</button><button type="button" class="btn btn-accent btn-kyaf" data-confirm-ok>হ্যাঁ, নিশ্চিত</button></div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  window.KYAF = { urls: {
    divisions: @json(route('locations.divisions')), districts: @json(route('locations.districts')),
    upazilas: @json(route('locations.upazilas')), thanas: @json(route('locations.thanas')), chain: @json(route('locations.chain'))
  } };
</script>
<script src="{{ asset('js/kyaf.js') }}?v=1"></script>
@stack('scripts')
