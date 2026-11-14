{{-- @include('partials.location-picker', ['name'=>'thana_id','selected'=>null,'required'=>true,'coveredOnly'=>false,'district'=>null,'upazila'=>null,'label'=>'...']) --}}
@php
  $name = $name ?? 'thana_id'; $required = $required ?? false; $coveredOnly = $coveredOnly ?? false;
  $selected = old($name, $selected ?? null); $district = $district ?? null; $upazila = $upazila ?? null; $noName = $noName ?? false;
@endphp
<div data-loc-picker data-covered-only="{{ $coveredOnly ? 1 : 0 }}" data-thana="{{ $selected }}" data-district="{{ $selected ? '' : $district }}" data-upazila="{{ $selected ? '' : $upazila }}" class="row g-2 mb-3">
  <div class="col-sm-6 col-lg-3"><label class="form-label fw-semibold">বিভাগ @if($required)<span class="req">*</span>@endif</label><select class="form-select" data-level="division" @if($required) required @endif><option value="">বিভাগ নির্বাচন করুন</option></select></div>
  <div class="col-sm-6 col-lg-3"><label class="form-label fw-semibold">জেলা @if($required)<span class="req">*</span>@endif</label><select class="form-select" name="{{ $noName ? '' : 'district_id' }}" data-level="district" @if($required) required @endif disabled><option value="">জেলা নির্বাচন করুন</option></select></div>
  <div class="col-sm-6 col-lg-3"><label class="form-label fw-semibold">উপজেলা @if($required)<span class="req">*</span>@endif</label><select class="form-select" name="{{ $noName ? '' : 'upazila_id' }}" data-level="upazila" @if($required) required @endif disabled><option value="">উপজেলা নির্বাচন করুন</option></select></div>
  <div class="col-sm-6 col-lg-3"><label class="form-label fw-semibold">থানা @if($required)<span class="req">*</span>@endif</label><select class="form-select @error($name) is-invalid @enderror" @unless($noName) name="{{ $name }}" @endunless data-level="thana" @if($required) required @endif disabled><option value="">থানা নির্বাচন করুন</option></select>
    @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
</div>
