<dl class="row mb-0">
  <dt class="col-sm-4">ট্র্যাকিং কোড</dt><dd class="col-sm-8"><strong>{{ $donation->donation_code }}</strong></dd>
  <dt class="col-sm-4">খাবারের নাম</dt><dd class="col-sm-8">{{ $donation->food_name }}</dd>
  <dt class="col-sm-4">খাবারের ধরন</dt><dd class="col-sm-8">{{ __('kyaf.category.'.$donation->food_category) }}</dd>
  <dt class="col-sm-4">পরিমাণ</dt><dd class="col-sm-8">{{ bn_digits($donation->quantity_meals) }} জনের খাবার</dd>
  <dt class="col-sm-4">মেয়াদ উত্তীর্ণ হবে</dt><dd class="col-sm-8">{{ bn_digits($donation->expiry_date->format('d/m/Y')) }}, {{ bst($donation->expiryInstant(), 'h:i A') }}
    @if($donation->isExpired() && ! $donation->isFinal())<span class="badge text-bg-danger">মেয়াদ শেষ</span>@endif</dd>
  <dt class="col-sm-4">পিকআপ এলাকা</dt><dd class="col-sm-8">{{ $donation->pickupThana->fullName() }}</dd>
  <dt class="col-sm-4">পিকআপ ঠিকানা</dt><dd class="col-sm-8">{{ $donation->pickup_address }}</dd>
  <dt class="col-sm-4">যোগাযোগের নম্বর</dt><dd class="col-sm-8">{{ \App\Support\Phone::display($donation->contact_phone) }}</dd>
  <dt class="col-sm-4">সেবা এলাকা</dt><dd class="col-sm-8">{{ optional($donation->area)->area_name }}</dd>
  @if($donation->description)<dt class="col-sm-4">বিবরণ</dt><dd class="col-sm-8">{{ $donation->description }}</dd>@endif
  <dt class="col-sm-4">জমার সময়</dt><dd class="col-sm-8">{{ bst($donation->submitted_at) }}</dd>
  @if($donation->photo_path)<dt class="col-sm-4">ছবি</dt><dd class="col-sm-8"><a href="{{ route('donations.photo', $donation) }}" target="_blank" rel="noopener"><img src="{{ route('donations.photo', $donation) }}" alt="খাবারের ছবি" class="img-thumbnail" style="max-height:140px"></a></dd>@endif
</dl>
