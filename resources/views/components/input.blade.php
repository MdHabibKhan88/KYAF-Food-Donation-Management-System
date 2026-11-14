@props(['name', 'label', 'type' => 'text', 'required' => false, 'value' => null, 'help' => null, 'bn' => false])
<div {{ $attributes->only('class')->merge(['class' => 'mb-3']) }}>
  <label for="{{ $name }}" class="form-label fw-semibold">{{ $label }}@if($required)<span class="req" title="আবশ্যক">*</span>@endif</label>
  <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $value) }}"
    {{ $attributes->except('class')->merge(['class' => 'form-control'.($errors->has($name) ? ' is-invalid' : '')]) }}
    @if($required) required @endif @if($bn) data-bn-digits @endif>
  @if($help)<div class="form-text">{{ $help }}</div>@endif
  @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
