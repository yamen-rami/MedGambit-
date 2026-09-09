const editorElement = document.querySelector('#content-editor');

if (editorElement) {
    const quill = new Quill(editorElement, {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                [{ color: [] }, { background: [] }],
                [{ header: [1, 2, 3, false] }],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['link'],
                ['clean']
            ]
        }
    });

    const input = document.querySelector('#content');

    quill.on('text-change', () => {
        input.value = quill.root.innerHTML;
    });
}