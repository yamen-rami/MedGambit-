<?php

use App\Models\Reference;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $sort = 'created_at';

    public $direction = 'desc';

    public $search = '';

    #[Computed]
    public function references()
    {
        $allowedSort = [
            'id' => 'id',
            'name' => 'name',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        ];

        $sortColumn = $allowedSort[$this->sort] ?? 'created_at';
        $direction = in_array($this->direction, ['asc', 'desc']) ? $this->direction : 'desc';
        $search = $this->search;

        return Reference::query()->when($search, fn ($q) => $q->where('name', 'LIKE', "%{$search}%"))->orderBy($sortColumn, $direction)->simplePaginate(30)->withQueryString();
    }
};
?>

<div>
    <div class="card">
        <div class="card-datatable table-responsive pt-0">
            <div class="row align-items-center gy-3 px-3 py-4">
                <div class="col-lg-2"></div>

                <div class="col-lg-6 col-12">
                    <form class="form" action="{{ route('references.index') }}">
                        <div class="d-flex position-relative gap-2">
                            <input
                                type="text"
                                name="search"
                                wire:model.live="search"
                                class="form-control flex-grow-1 ps-3"
                                style="min-width: 150px"
                                placeholder="Search Questions"
                            />
                            <button
                                type="button"
                                wire:show="search"
                                wire:click="$set('search', '')"
                                style="
                                    position: absolute;
                                    right: 10px;
                                    top: 50%;
                                    transform: translateY(-50%);
                                    background: none;
                                    border: none;
                                "
                            >
                                <i class="icon-base ti tabler-x"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="col-lg-1 text-lg-start text-center">
                    <x-filter align="start">
                        <x-slot:trigger>
                            <span class="btn hide-arrow">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="lucide lucide-sliders-horizontal-icon lucide-sliders-horizontal"
                                >
                                    <path d="M10 5H3" />
                                    <path d="M12 19H3" />
                                    <path d="M14 3v4" />
                                    <path d="M16 17v4" />
                                    <path d="M21 12h-9" />
                                    <path d="M21 19h-5" />
                                    <path d="M21 5h-7" />
                                    <path d="M8 10v4" />
                                    <path d="M8 12H3" />
                                </svg>
                            </span>
                        </x-slot:trigger>

                        <div class="px-4 py-4" style="min-width: 220px">
                            <div class="d-flex justify-content-end mb-2">
                                @if ($this->direction === 'desc')
                                    <svg
                                        wire:click="$set('direction', 'asc')"
                                        wire:key="dir-asc"
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        style="cursor: pointer"
                                    >
                                        <path d="m3 8 4-4 4 4" />
                                        <path d="M7 4v16" />
                                        <path d="M11 12h4" />
                                        <path d="M11 16h7" />
                                        <path d="M11 20h10" />
                                    </svg>
                                @else
                                    <svg
                                        wire:click="$set('direction', 'desc')"
                                        wire:key="dir-desc"
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        style="cursor: pointer"
                                    >
                                        <path d="m3 16 4 4 4-4" />
                                        <path d="M7 20V4" />
                                        <path d="M11 4h4" />
                                        <path d="M11 8h7" />
                                        <path d="M11 12h10" />
                                    </svg>
                                @endif
                            </div>

                            <div class="text-start">
                                <p
                                    wire:click="$set('sort', 'name')"
                                    wire:key="sort-name"
                                    class="mb-1 cursor-pointer py-1 pe-5"
                                    :class="$wire.sort === 'name' ? 'text-success' : 'text-body'"
                                >
                                    Name
                                </p>
                                <p
                                    wire:click="$set('sort', 'created_at')"
                                    wire:key="sort-created"
                                    class="mb-1 cursor-pointer py-1 pe-5"
                                    :class="$wire.sort === 'created_at' ? 'text-success' : 'text-body'"
                                >
                                    Created At
                                </p>
                                <p
                                    wire:click="$set('sort', 'updated_at')"
                                    wire:key="sort-updated"
                                    class="mb-0 cursor-pointer py-1 pe-5"
                                    :class="$wire.sort === 'updated_at' ? 'text-success' : 'text-body'"
                                >
                                    Update At
                                </p>
                            </div>
                        </div>
                    </x-filter>
                </div>

                <div class="col-lg-3 text-lg-end col-6 text-center">
                    <a href="{{ route('references.create') }}">
                        <button class="btn btn-primary">Create A New Reference</button>
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="datatables-basic table">
                    <thead>
                        <tr class="ps-3 pe-4">
                            <th>id</th>
                            <th>name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->references as $reference)
                            <tr wire:key="ref-{{ $reference->id }}">
                                <th>{{ $reference->id }}</th>
                                <th>{{ $reference->name }}</th>
                                <td>
                                    <x-filter align="end">
                                        <x-slot:trigger>
                                            <button type="button" class="btn hide-arrow p-0">
                                                <i class="icon-base ti tabler-dots-vertical"></i>
                                            </button>
                                        </x-slot:trigger>

                                        <a class="dropdown-item" href="{{ route('references.edit', $reference) }}">
                                            <i class="icon-base ti tabler-pencil me-1"></i> Edit
                                        </a>
                                        <form action="{{ route('references.destroy', $reference->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item">
                                                <i class="icon-base ti tabler-trash me-1"></i> Delete
                                            </button>
                                        </form>
                                    </x-filter>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="align-item-center py-4 ps-4 pe-4">{{ $this->references->links() }}</div>
    </div>

    @if ($this->references?->count() === 0)
        <h1 class="fs-5 text-center">there is nothing there</h1>
    @endif
</div>
