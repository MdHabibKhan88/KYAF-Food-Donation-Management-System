@props(['name', 'label', 'required' => false, 'help' => null, 'options' => [], 'value' => null, 'placeholder' => 'নির্বাচন করুন'])
<div {{ $attributes->only('class')->merge(['class' => 'mb-3']) }}>
  <label for="{{ $name }}" class="form-label fw-semibold">{{ $label }}@if($required)<span class="req" title="আবশ্যক">*</span>@endif</label>
  <select id="{{ $name }}" name="{{ $name }}" class="form-select @error($name) is-invalid @enderror" @if($required) required @endif {{ $attributes->except('class') }}>
    <option value="">{{ $placeholder }}</option>
    @foreach ($options as $k => $v)
      <option value="{{ $k }}" @selected((string) old($name, $value) === (string) $k)>{{ $v }}</option>
    @endforeach
  </select>
  @if($help)<div class="form-text">{{ $help }}</div>@endif
  @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
