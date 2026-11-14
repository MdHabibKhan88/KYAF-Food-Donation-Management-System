<form method="get" class="card mb-3"><div class="card-body"><div class="row g-2 align-items-end">
  <div class="col-md-3"><label class="form-label small mb-1">খুঁজুন</label><input class="form-control" name="q" value="{{ request('q') }}" placeholder="কোড বা খাবারের নাম"></div>
  <div class="col-md-2"><label class="form-label small mb-1">অবস্থা</label><select class="form-select" name="status"><option value="">সব</option>@foreach(array_keys(__('kyaf.status')) as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ __('kyaf.status.'.$s) }}</option>@endforeach</select></div>
  <div class="col-md-2"><label class="form-label small mb-1">তারিখ থেকে</label><input type="date" class="form-control" name="from" value="{{ request('from') }}"></div>
  <div class="col-md-2"><label class="form-label small mb-1">তারিখ পর্যন্ত</label><input type="date" class="form-control" name="to" value="{{ request('to') }}"></div>
  <div class="col-md-3 d-flex gap-2"><button class="btn btn-accent btn-kyaf flex-fill" type="submit"><i class="bi bi-funnel"></i> ফিল্টার</button><a href="{{ url()->current() }}" class="btn btn-light">রিসেট</a></div>
</div></div></form>
