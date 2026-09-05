<x-app>
    <x-slot:title>Create Reference</x-slot:title>
    <form action="{{ route("skills.store") }}" method="post">
        @csrf
        <div class="col-lg-12">
            <div class="row">
                <div class="col-md-6 col-lg-6">
                    <div class="card">
                        <h5 class="card-header">Create Reference</h5>
                        <div class="card-body">
                            <x-forms.input label="Name" name="name"></x-forms.input>
                            <div class="text-end">
                                <button class="btn btn-primary">Create Reference</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-app>
