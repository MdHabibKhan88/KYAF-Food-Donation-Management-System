@props(['name' => 'password', 'label' => 'পাসওয়ার্ড', 'required' => true, 'rules' => false, 'help' => null, 'autocomplete' => 'new-password'])
<div {{ $attributes->only('class')->merge(['class' => 'mb-3']) }}>
  <label for="{{ $name }}" class="form-label fw-semibold">{{ $label }}@if($required)<span class="req" title="আবশ্যক">*</span>@endif</label>
  <div class="pw-wrap">
    <input type="password" id="{{ $name }}" name="{{ $name }}" autocomplete="{{ $autocomplete }}" maxlength="64"
      class="form-control @error($name) is-invalid @enderror" @if($required) required @endif @if($rules) data-pw-rules="pwRules-{{ $name }}" @endif>
    <button type="button" class="toggle-pass" data-target="{{ $name }}" aria-label="পাসওয়ার্ড দেখান"><i class="bi bi-eye"></i></button>
  </div>
  @if($help)<div class="form-text">{{ $help }}</div>@endif
  @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
  @if($rules)
    <ul class="pw-rules mt-2 mb-0 ps-3" id="pwRules-{{ $name }}">
      <li data-rule="len">৮ থেকে ৬৪ অক্ষর</li><li data-rule="upper">একটি বড় হাতের অক্ষর (A-Z)</li><li data-rule="lower">একটি ছোট হাতের অক্ষর (a-z)</li>
      <li data-rule="digit">একটি সংখ্যা (0-9)</li><li data-rule="special">একটি বিশেষ চিহ্ন (@#$! ইত্যাদি)</li>
    </ul>
  @endif
</div>
