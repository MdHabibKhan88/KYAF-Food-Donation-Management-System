@props(['name', 'label', 'required' => false, 'value' => null, 'help' => null, 'rows' => 3])
<div {{ $attributes->only('class')->merge(['class' => 'mb-3']) }}>
  <label for="{{ $name }}" class="form-label fw-semibold">{{ $label }}@if($required)<span class="req" title="আবশ্যক">*</span>@endif</label>
  <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" {{ $attributes->except('class')->merge(['class' => 'form-control'.($errors->has($name) ? ' is-invalid' : '')]) }} @if($required) required @endif>{{ old($name, $value) }}</textarea>
  @if($help)<div class="form-text">{{ $help }}</div>@endif
  @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
