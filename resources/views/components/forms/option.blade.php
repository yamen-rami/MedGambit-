@props(['number' => 1])
<x-forms.input
    name="options[{{ $number }}][name]"
    label="Option {{ $number }} Name"
    value="{{ ['A', 'B', 'C', 'D', 'E'][$number - 1] ?? '' }}"
    readonly
></x-forms.input>

@error("options.$number.name")
    <p class="text-danger">{{ $message }}</p>
@enderror
<x-forms.textarea
    :value="old('options.'.$number.'.explanation')"
    label="Option {{ $number }} Explantion"
    name="options[{{ $number }}][explanation]"
>
</x-forms.textarea>
@error("options.$number.explanation")
    <p class="text-danger">{{ $message }}</p>
@enderror
<div class="form-check mt-4">
    <input type="hidden" name="options[{{ $number }}][correct_answer]" value="0" />
    <input
        name="options[{{ $number }}][correct_answer]"
        value="1"
        class="form-check-input"
        type="checkbox"
        id="defaultCheck{{ $number }}"
        @checked(old('options.'.$number.'.correct_answer'))
    />
    <label class="form-check-label" for="defaultCheck1"> Correct Answer </label>
    @error('correct_answer')
        <p class="text-danger py-1">{{ $message }}</p>
    @enderror
</div>
<x-forms.textarea
    :value="old('options.'.$number.'.content')"
    name="options[{{ $number }}][content]"
    label="Content"
    type="text"
></x-forms.textarea>
@error("options.$number.content")
    <p class="text-danger">{{ $message }}</p>
@enderror
<x-forms.textarea label="Topic" :value="old('options.'.$number.'.topic')" name="options[{{ $number }}][topic]">
</x-forms.textarea>
@error("options.$number.topic")
    <p class="text-danger">{{ $message }}</p>
@enderror
<div class="mt-4 mb-4">
    <label for="exampleFormControlInput1" class="form-label">Option {{ $number }} Image </label>
    <div>
        <input class="form-control" type="file" id="option{{ $number }}Image" name="options[{{ $number }}][image]" />
        @error("options.$number.image")
            <p class="text-danger py-1">{{ $message }}</p>
        @enderror
    </div>
</div>
<div class="d-flex justify-content-between">
    <div class="text-start">
        <button
            onclick="showPrevious(event, {{ $number }})"
            class="btn btn-info"
            {{ $number === "1" ? "disabled" : "" }}
            id="before-{{ $number }}"
        >
            back
        </button>
    </div>
    <div class="text-end">
        <button
            onclick="showNext(event , {{ $number }})"
            {{ $number == "5" ? "disabled" : "" }}
            class="{{ $number == "5" ? "btn btn-danger" : "btn btn-primary" }}"
            id="next-{{ $number }}"
        >
            Next
        </button>
    </div>
</div>
