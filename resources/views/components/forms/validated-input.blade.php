@props([
    'label',
    'name',
    'type' => 'text',
    'id' => null,
    'value' => null,
    'placeholder' => null,
    'autocomplete' => null,
    'required' => false,
    'togglePassword' => false,
])

@php
    $inputId = $id ?? str_replace(['[', ']', '.', '_'], '-', $name);
    $hasError = $errors->has($name);
    $hasValue = $type !== 'password' && $type !== 'file' && filled(old($name, $value));
@endphp

<div class="field has-validation">
    <label for="{{ $inputId }}">{{ $label }}</label>

    @if ($togglePassword)
        <div class="password-wrap">
    @endif

    <input
        {{ $attributes->class([
            'form-control',
            'auth-control',
            'is-invalid' => $hasError,
            'is-valid' => $hasValue && !$hasError,
        ]) }}
        id="{{ $inputId }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if ($type !== 'password' && $type !== 'file') value="{{ old($name, $value) }}" @endif
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        @if ($required) required @endif
    >

    @error($name)
        <div class="invalid-feedback {{ $togglePassword ? 'd-block' : '' }}">{{ $message }}</div>
    @elseif ($hasValue && !$hasError)
        <div class="valid-feedback">Looks good.</div>
    @enderror

    @if ($togglePassword)
        <button class="password-toggle" type="button" data-password-toggle="{{ $inputId }}"
            aria-label="Show password" aria-pressed="false">
            <i class="bi bi-eye"></i>
        </button>
        </div>
    @endif
</div>
