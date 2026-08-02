<x-app>
  <x-slot:title>
    Create Branch
  </x-slot:title>
  <form action="{{ route("branch.update", $branch->id) }}" method="post" enctype="multipart/form-data">
    @csrf
    @method("PATCH")
    <div class="col-lg-12  ">
      <div class="row">

        <div class="col-md-6  col-lg-6 ">
          <div class="card">
            <h5 class="card-header">Create Branch</h5>
            <div class="card-body">
              <x-forms.textarea value="{{ $branch->name }}" label="Name" name="name"> </x-forms.textarea>
              <div class="text-end">
                <button class="btn btn-primary">
                  Update Branch
                </button>
              </div>
            </div>
          </div>
        </div>
  </form>
</x-app>
