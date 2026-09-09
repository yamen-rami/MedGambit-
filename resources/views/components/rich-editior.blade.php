@props([
    'label' => 'Content',
    'name' => 'content',
    'value' => '',
])

@php
    $editorId = 'quill-' . $name . '-' . uniqid();
@endphp

<div class="my-3">
    <label class="my-2" for="{{ $editorId }}">
        {{ $label }}
    </label>

    <div id="{{ $editorId }}"></div>

    <input
        type="hidden"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
    >
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[id^="quill-"]').forEach(function (editorElement) {

                // Don't initialize the same editor twice
                if (editorElement.classList.contains('ql-container')) {
                    return;
                }

                const inputElement = editorElement
                    .parentElement
                    .querySelector('input[type="hidden"]');

                if (!inputElement) {
                    return;
                }

                const quill = new Quill(editorElement, {
                    theme: 'snow',

                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline', 'strike'],

                            [
                                { color: [] },
                                { background: [] }
                            ],

                            [
                                { header: [1, 2, 3, false] }
                            ],

                            [
                                { list: 'ordered' },
                                { list: 'bullet' }
                            ],

                            ['link'],

                            ['clean']
                        ]
                    }
                });

                const oldContent = inputElement.value;

                if (oldContent) {
                    quill.clipboard.dangerouslyPasteHTML(oldContent);
                }

                inputElement.value = quill.root.innerHTML;

                quill.on('text-change', function () {
                    inputElement.value = quill.root.innerHTML;
                });
            });
        });
    </script>
@endonce