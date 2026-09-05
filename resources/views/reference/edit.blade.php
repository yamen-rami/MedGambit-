<x-app>
    <x-slot:title>Update Reference</x-slot:title>
    <form action="{{ route('skills.update', $reference->id) }}" method="post"i>
        @csrf
        @method('PATCH')
        <div class="col-lg-12">
            <div class="row">
                <div class="col-md-6 col-lg-6">
                    <div class="card">
                        <h5 class="card-header">Update Reference</h5>
                        <div class="card-body">
                            <x-forms.textarea value="{{ old('name', $reference->name ?? '') }}" label="Name"
                                name="name">
                                @dd(old('name'))
                                @error("name")
                                <p class="text-danger">{{ $message }}</p>
                                @enderror

                            </x-forms.textarea>
                            <div class="text-end">
                                <button class="btn btn-primary">Update Reference</button>
                            </div>
                        </div>
                    </div>
        </div>
    </form>
</x-app>
