<x-app>
    <x-slot:title>Create Option</x-slot:title>
    <div class="card px-5 py-5">
        <form action="{{ route("option.store", $id) }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="col-lg-6">
                <x-textarea name="name" label="Name"> </x-textarea>
                <x-textarea name="content" label="content"> </x-textarea>
                <x-textarea name="explanation" label="Explanation"> </x-textarea>
                <div class="form-check my-4">
                    <input type="hidden" name="correct_answer" value="0" />
                    <input name="correct_answer" value="1" class="form-check-input" type="checkbox" id="defaultCheck" />
                    <label class="form-check-label" for="defaultCheck1"> Correct Answer </label>
                    @error('correct_answer')
                        <p class="text-danger py-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <img width="50%" height="400px" id="optionPreview" class="rounded-5" src="" alt="Add Some Image " />
                    @error('image')
                        <p class="text-danger">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mt-4 mb-4">
                    <label for="exampleFormControlInput1" class="form-label">Option Image </label>
                    <div>
                        <input class="form-control" id="optionInput" type="file" name="image" />
                        @error('image')
                            <p class="text-danger py-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="text-start">
                    <button class="btn btn-primary my-2 text-white">Create Option</button>
                </div>
            </div>
        </form>
    </div>
    @push('scripts')
        <script src="{{ asset('assets/js/edit.js') }}"></script>

    @endpush
</x-app>
