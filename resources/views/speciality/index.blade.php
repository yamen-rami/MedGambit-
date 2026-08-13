<x-app>
    <x-slot:title>Specalities Admin</x-slot:title>
    <!-- Content -->
    <!-- DataTable with Buttons -->
    <div class="card">
        <div style="overflow-x: hidden" class="card-datatable table-responsive pt-0">
            <div class="d-grid">
                <div class="row align-items-center py-4">
                    <div class="col-lg-4 text-center">
                        <div class="dropdown">
                            <button type="button" class="btn dropdown-toggle hide-arrow p-0" data-bs-toggle="dropdown">
                                <div class="">
                                    <img src="{{ asset("assets/images/filter.svg") }}" alt="" />
                                </div>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item">
                                    <div class="text-center">
                                        <form action="{{ route("speciality.index") }}">
                                            <input type="hidden" name="sort" value="{{ $sort ?? "desc" }}" />
                                            <input type="hidden" name="search" value="{{ request('search') }}" />
                                            <button class="btn text-start" type="submit">
                                                <img
                                                    src="{{ asset($sort ?? "desc" === "desc" ? "assets/images/arrow_down.svg" : "assets/images/arrow_top.svg") }}"
                                                    alt="Arrows "
                                                />
                                            </button>
                                        </form>
                                        <hr />
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <form class="form" action="{{ route("speciality.index") }}">
                            <div class="d-flex gap-3">
                                <input
                                    type="text"
                                    name="search"
                                    class="form-control ps-5"
                                    placeholder="Search Questions"
                                />
                                <button class="btn btn-danger">
                                    <a class="text-white" href="{{ route("speciality.index") }}">Clear</a>
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="col-lg-4 text-center">
                        <a href="{{ route("speciality.create") }}">
                            <button class="btn btn-primary">Create A New Speciality</button>
                        </a>
                    </div>
                </div>
            </div>
            <table class="datatables-basic table">
                <thead>
                    <tr class="ps-3 pe-4">
                        <th>id</th>
                        <th>name</th>

                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($specialities as $speciality)
                        <tr>
                            <th>{{ $speciality->id }}</th>
                            <th>{{ $speciality->name }}</th>
                            <td>
                                <div class="dropdown">
                                    <button
                                        type="button"
                                        class="btn dropdown-toggle hide-arrow p-0"
                                        data-bs-toggle="dropdown"
                                    >
                                        <i class="icon-base ti tabler-dots-vertical"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        {{-- Edit tag --}}
                                        {{-- <a class="dropdown-item" href="{{ route('speciality.show', $speciality) }}">
                      <img src="{{ asset("assets/images/eye.svg") }}" alt="Show Questions">
                      Show
                    </a> --}}
                                        <a class="dropdown-item" href="{{ route('speciality.edit', $speciality) }}"
                                            ><i class="icon-base ti tabler-pencil me-1"></i> Edit</a>
                                        {{-- Delete tag --}}
                                        <form action="{{ route("speciality.destroy", $speciality->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item">
                                                <i class="icon-base ti tabler-trash me-1"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="align-item-center py-4 ps-4 pe-4">
                {{ $specialities->appends(request()->query())->links() }}
            </div>
        </div>
        @if ($specialities->count() === 0)
            <h1 class="fs-5 text-center">there is nothing there</h1>
        @endif

        <div class="content-backdrop fade"></div>
    </div>
</x-app>
