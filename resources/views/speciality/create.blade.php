<x-app>
    <x-slot:title>Create Specialities</x-slot:title>
    <form action="{{ route("speciality.store") }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="col-lg-12">
            <div class="row">
                <div class="col-md-6 col-lg-6">
                    <div class="card">
                        <h5 class="card-header">Create Speciality</h5>
                        <div class="card-body">
                            <x-forms.input label="Name" name="name"></x-forms.input>
                            <div class="text-end">
                                <button class="btn btn-primary">Create Speciality</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-app>
