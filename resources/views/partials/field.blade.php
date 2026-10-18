{{-- params: name, label, type(text), value, options[], blank(true), required(false), step, col(col-md-6) --}}
@php
    $type = $type ?? 'text';
    $val = $type === 'password' ? '' : old($name, $value ?? null);
@endphp
<div class="{{ $col ?? 'col-md-6' }}">
    <label class="form-label small fw-semibold" for="f_{{ $name }}">{{ $label }}@if($required ?? false) <span class="text-danger">*</span>@endif</label>
    @if ($type === 'select')
        <select id="f_{{ $name }}" name="{{ $name }}" class="form-select @error($name) is-invalid @enderror" {{ ($required ?? false) ? 'required' : '' }}>
            @if ($blank ?? true)<option value="">— Select —</option>@endif
            @foreach ($options as $k => $l)
                <option value="{{ $k }}" @selected((string) $val === (string) $k)>{{ $l }}</option>
            @endforeach
        </select>
    @elseif ($type === 'textarea')
        <textarea id="f_{{ $name }}" name="{{ $name }}" rows="3" class="form-control @error($name) is-invalid @enderror">{{ $val }}</textarea>
    @elseif ($type === 'password')
        <div class="password-field position-relative">
            <input id="f_{{ $name }}" type="password" name="{{ $name }}" value="{{ $val }}"
                   class="form-control pe-5 @error($name) is-invalid @enderror"
                   @if(isset($step)) step="{{ $step }}" @endif {{ ($required ?? false) ? 'required' : '' }}>
            @include('partials.password-toggle-button', ['target' => 'f_' . $name])
        </div>
    @else
        <input id="f_{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ $val }}"
               class="form-control @error($name) is-invalid @enderror"
               @if(isset($step)) step="{{ $step }}" @endif {{ ($required ?? false) ? 'required' : '' }}>
    @endif
    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>