@php $size = $size ?? 40; @endphp
<span class="avatar" @if(! empty($id)) id="{{ $id }}" @endif style="width:{{ $size }}px;height:{{ $size }}px;font-size:{{ round($size / 2.4) }}px">
  @if($user->profile_photo_path)<img src="{{ $user->photoUrl() }}" alt="{{ $user->full_name }}">@else{{ $user->initials() }}@endif
</span>