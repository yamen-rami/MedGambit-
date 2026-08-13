@props(['name' => 'topic', 'label' => 'Topic', 'value' => null])
<div class="my-2">
    <label for="exampleFormControlTextarea1" class="form-label">{{ $label }}</label>
    <textarea
        class="form-control"
        placeholder="Enter {{ $label }}"
        name="{{ $name }}"
        id="exampleFormControlTextarea1"
        rows="3"
    >{{ $value }}</textarea>
</div>
