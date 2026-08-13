@props(['label' => 'Content', 'type' => 'text', 'name' => 'content', 'max' => null, 'value' => null, 'min' => null])
<div class="mt-4 mb-4">
    <label for="exampleFormControlInput1" class="form-label">{{ $label }}</label>
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        max="{{ $max }}"
        min="{{ $min }}"
        {{ $attributes->merge(['class' => 'form-control']) }}
        value="{{ $value ?? old($name) }}"
        id="exampleFormControlInput{{ $name }}"
        placeholder="Enter {{ $label }}"
    />
</div>
@error($name)
    <p class="text-danger">{{ $message }}</p>
@enderror
