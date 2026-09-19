<?php

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\{Computed, On};
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\{DB, Storage};

new class extends Component {
    use WithPagination;
    use WithFileUploads;
    public string $search = '';
    public $selectedUser;
    public string $direction = 'desc';
    public $image;
    public ?string $name = null;
    public ?string $email = null;
    public ?string $password = null;
    public ?string $createGender = null;
    public ?string $createRole = null;
    public ?string $createYear = null;
    public ?string $createGraduated = null;

    public string $sort = 'created_at';

    public string $year = '';

    public string $graduated = '';

    public string $country = '';

    public string $gender = '';

    public string $role = '';

    public int $count = 20;

    public function clearFilters(): void
    {
        $this->reset(['search', 'year', 'graduated', 'country', 'gender', 'role']);

        $this->sort = 'created_at';
        $this->direction = 'desc';

        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedYear(): void
    {
        $this->resetPage();
    }

    public function updatedGraduated(): void
    {
        $this->resetPage();
    }

    public function updatedCountry(): void
    {
        $this->resetPage();
    }

    public function updatedGender(): void
    {
        $this->resetPage();
    }

    public function updatedRole(): void
    {
        $this->resetPage();
    }

    public function updatedCount(): void
    {
        $this->resetPage();
    }

    public function setSort(string $sort): void
    {
        $allowed = ['created_at', 'updated_at', 'name', 'email', 'year', 'rank', 'graduated'];

        if (in_array($sort, $allowed)) {
            $this->sort = $sort;
            $this->resetPage();
        }
    }

    public function toggleDirection(): void
    {
        $this->direction = $this->direction === 'asc' ? 'desc' : 'asc';

        $this->resetPage();
    }

    #[Computed]
    public function users()
    {
        $allowedSorts = ['created_at', 'updated_at', 'name', 'email', 'year', 'rank', 'graduated'];

        $sort = in_array($this->sort, $allowedSorts) ? $this->sort : 'created_at';

        $direction = in_array($this->direction, ['asc', 'desc']) ? $this->direction : 'desc';

        return User::query()
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'LIKE', "%{$this->search}%")->orWhere('email', 'LIKE', "%{$this->search}%");
                });
            })

            ->when($this->year !== '', fn($query) => $query->where('year', $this->year))

            ->when($this->graduated !== '', fn($query) => $query->where('graduated', $this->graduated === 'true'))

            ->when($this->country !== '', fn($query) => $query->where('country', $this->country))

            ->when($this->gender !== '', fn($query) => $query->where('gender', $this->gender))

            ->when($this->role !== '', fn($query) => $query->where('role', $this->role))

            ->orderBy($sort, $direction)
            ->simplePaginate($this->count);
    }

    #[Computed]
    public function countries()
    {
        return User::query()->whereNotNull('country')->where('country', '!=', '')->distinct()->orderBy('country')->pluck('country');
    }

    #[Computed]
    public function hasFilters(): bool
    {
        return $this->search !== '' || $this->year !== '' || $this->graduated !== '' || $this->country !== '' || $this->gender !== '' || $this->role !== '';
    }

    #[Computed]
    public function sortLabel(): string
    {
        return match ($this->sort) {
            'name' => 'Name',
            'email' => 'Email',
            'year' => 'Year',
            'rank' => 'Rank',
            'updated_at' => 'Updated',
            default => 'Created',
        };
    }
    public function create()
    {
        $this->authorize('create', auth()->user());

        $validated = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'max:20'],
            'image' => ['nullable', 'image', 'max:2048'],
            'createYear' => ['nullable', 'required_if:createGraduated,true', 'integer'],
            'createRole' => ['required', Rule::in(['user', 'admin', 'super_admin'])],
            'createGender' => ['required', Rule::in(['male', 'female'])],
            'createGraduated' => ['required'],
        ]);
        $validated['createGraduated'] = $validated['createGraduated'] == 'false' ? false : true;
        $imagePath = null;
        if ($this->image) {
            $imagePath = $this->image->store('users', 'public');
        }

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'image' => $imagePath,
            'role' => $validated['createRole'],
            'gender' => $validated['createGender'],
            'graduated' => filter_var($validated['createGraduated'], FILTER_VALIDATE_BOOLEAN),
            'year' => $validated['createYear'] ?? null,
        ]);

        flash()->success('User created successfully.');

        $this->reset(['name', 'email', 'password', 'image', 'createYear', 'createRole', 'createGender', 'createGraduated']);
        $this->resetPage();

        $this->dispatch('close-modal');
    }
    public function update()
    {
        // $this->authorize('update', auth()->user());
        $validated = $this->validate([
            'name' => ['nullable', 'string', 'min:2', 'max:100'],
            'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($this->selectedUser->id)],
            'password' => ['nullable', 'min:8', 'max:20'],
            'image' => ['nullable', 'image', 'max:2048'],
            'createYear' => ['nullable', 'required_if:createGraduated,false', 'integer'],
            'createRole' => ['nullable', Rule::in(['user', 'admin', 'super_admin'])],
            'createGender' => ['nullable', Rule::in(['male', 'female'])],
            'createGraduated' => ['nullable', "prohibited_if:createYear,null"],
        ]);
        $validated['year'] = $validated['createYear'];
        $validated['gender'] = $validated['createGender'];
        $validated['role'] = $validated['createRole'];
        $validated['graduated'] = $validated['createGraduated'];
        unset($validated['createYear'], $validated['createGender'], $validated['createRole'], $validated['createGraduated']);
        if (!$this->selectedUser) {
            return;
        }
        if ($this->password) {
            $validated['password'] = Hash::make($this->password);
        } else {
            unset($validated['password']);
        }
        $validated['graduated'] = $validated['graduated'] == 'false' ? false : true;
        if ($this->image) {
            $oldImage = $this->selectedUser->image;
            $validated['image'] = $this->image->store('users', 'public');

            if ($oldImage) {
                Storage::disk('public')->delete($oldImage);
            }
        } else {
            unset($validated['image']);
        }
        $this->selectedUser->update($validated);
        flash()->success('User has updated');
        $this->resetPage();
    }
    public function delete(User $user)
    {
        $user->delete();
        flash()->success('User Has Deleted Succefully');
        $this->resetPage();
    }
    #[On('userSelected')]
    public function userSelected($id)
    {
        $this->selectedUser = User::findOrFail($id);
        $this->name = $this->selectedUser->name;
        $this->email = $this->selectedUser->email;
            $this->name = $this->selectedUser->name;
    $this->email = $this->selectedUser->email;
    $this->createRole = $this->selectedUser->role;
    $this->createGender = $this->selectedUser->gender;
    $this->createYear = $this->selectedUser->year;
    $this->createGraduated = $this->selectedUser->graduated ? 'true' : 'false';
    }
};
?>

<div>
    <div class="card shadow-sm border-0">

        {{-- =========================
            HEADER
        ========================== --}}
        <div class="card-header border-bottom py-4">

            <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">

                {{-- Title --}}
                <div>
                    <h5 class="mb-1">Users</h5>

                    <p class="mb-0 text-muted">
                        Manage registered users and their accounts
                    </p>
                </div>

                {{-- Actions --}}
                <div class="d-flex flex-column flex-sm-row gap-2">

                    {{-- Search --}}
                    <div class="position-relative">

                        <i class="icon-base ti tabler-search position-absolute"
                            style="
                                left: 14px;
                                top: 50%;
                                transform: translateY(-50%);
                                z-index: 2;
                                color: var(--bs-secondary-color);
                            "></i>

                        <input type="text" wire:model.live.debounce.400ms="search" class="form-control ps-5 pe-5"
                            style="min-width: 280px" placeholder="Search name or email...">

                        @if ($search)
                            <button type="button" wire:click="$set('search', '')" class="btn p-0 position-absolute"
                                style="
                                    right: 12px;
                                    top: 50%;
                                    transform: translateY(-50%);
                                ">
                                <i class="icon-base ti tabler-x"></i>
                            </button>
                        @endif

                    </div>

                    {{-- Filter --}}
                    {{-- Filter --}}
                    <div class="dropdown" x-data="{ open: false }">
                        <button type="button" class="btn btn-outline-secondary dropdown-toggle"
                            x-on:click="open = !open" x-bind:aria-expanded="open">
                            <i class="icon-base ti tabler-adjustments-horizontal me-1"></i>
                            Filters

                            @if ($this->hasFilters)
                                <span class="badge rounded-pill bg-primary ms-1">
                                    !
                                </span>
                            @endif
                        </button>

                        <div class="dropdown-menu dropdown-menu-end p-0" x-bind:class="{ 'show': open }"
                            style="width: 330px; max-height: 80vh; overflow-y: auto;">

                            {{-- Filter Header --}}
                            <div class="px-4 py-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-center">

                                    <div>
                                        <h6 class="mb-1">Filters</h6>

                                        <small class="text-muted">
                                            Narrow down your users
                                        </small>
                                    </div>

                                    @if ($this->hasFilters)
                                        <button type="button" wire:click="clearFilters"
                                            class="btn btn-sm btn-link text-danger p-0">
                                            Clear all
                                        </button>
                                    @endif

                                </div>
                            </div>

                            <div class="p-4">

                                {{-- Sort --}}
                                <div class="mb-4">
                                    <label class="form-label fw-semibold mb-2">
                                        Sort by
                                    </label>

                                    <div class="d-flex gap-2">

                                        <select wire:model.live="sort" class="form-select">
                                            <option value="created_at">
                                                Created At
                                            </option>

                                            <option value="updated_at">
                                                Updated At
                                            </option>

                                            <option value="name">
                                                Name
                                            </option>

                                            <option value="email">
                                                Email
                                            </option>

                                            <option value="year">
                                                Year
                                            </option>

                                            <option value="rank">
                                                Rank
                                            </option>

                                            <option value="graduated">
                                                Graduation
                                            </option>
                                        </select>

                                        <button type="button" wire:click="toggleDirection"
                                            class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
                                            style="width: 44px;"
                                            title="{{ $direction === 'asc' ? 'Ascending' : 'Descending' }}">
                                            @if ($direction === 'asc')
                                                <i class="icon-base ti tabler-sort-ascending"></i>
                                            @else
                                                <i class="icon-base ti tabler-sort-descending"></i>
                                            @endif
                                        </button>

                                    </div>

                                    <small class="text-muted mt-1 d-block">
                                        {{ $this->sortLabel }}
                                        ·
                                        {{ $direction === 'asc' ? 'Ascending' : 'Descending' }}
                                    </small>
                                </div>

                                {{-- Year --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        Medical year
                                    </label>

                                    <select wire:model.live="year" class="form-select">
                                        <option value="">
                                            All years
                                        </option>

                                        @for ($i = 1; $i <= 6; $i++)
                                            <option value="{{ $i }}">
                                                Year {{ $i }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>

                                {{-- Gender --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        Gender
                                    </label>

                                    <select wire:model.live="gender" class="form-select">
                                        <option value="">
                                            All genders
                                        </option>

                                        <option value="male">
                                            Male
                                        </option>

                                        <option value="female">
                                            Female
                                        </option>
                                    </select>
                                </div>

                                {{-- Graduation --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        Graduation status
                                    </label>

                                    <select wire:model.live="graduated" class="form-select">
                                        <option value="">
                                            All users
                                        </option>

                                        <option value="true">
                                            Graduated
                                        </option>

                                        <option value="false">
                                            Not graduated
                                        </option>
                                    </select>
                                </div>

                                {{-- Role --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        Role
                                    </label>

                                    <select wire:model.live="role" class="form-select">
                                        <option value="">
                                            All roles
                                        </option>

                                        <option value="user">
                                            User
                                        </option>

                                        <option value="admin">
                                            Admin
                                        </option>

                                        <option value="super_admin">
                                            Super Admin
                                        </option>
                                    </select>
                                </div>

                                {{-- Country --}}
                                <div class="mb-1">
                                    <label class="form-label fw-semibold">
                                        Country
                                    </label>

                                    <select wire:model.live="country" class="form-select">
                                        <option value="">
                                            All countries
                                        </option>

                                        @foreach ($this->countries as $countryOption)
                                            <option value="{{ $countryOption }}">
                                                {{ $countryOption }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>

                            {{-- Results --}}
                            <div class="border-top px-4 py-3 bg-body-tertiary">
                                <div class="d-flex align-items-center justify-content-between">

                                    <small class="text-muted">
                                        Results per page
                                    </small>

                                    <select wire:model.live="count" class="form-select form-select-sm"
                                        style="width: 80px;">
                                        <option value="10">10</option>
                                        <option value="20">20</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>

                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- Create --}}
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#staticBackdrop">
                        <i class="icon-base ti tabler-user-plus me-1"></i>
                        Create User
                    </button>

                    <!-- Modal -->
                    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false"
                        tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Create User</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <x-forms.input name="name" label="Name" wire:model="name"></x-forms.input>
                                    <x-forms.input type="email" name="email" label="Email"
                                        wire:model="email"></x-forms.input>
                                    <div class="my-3">
                                        <label for="gender">Role</label>
                                        <select name="role" wire:model='createRole' class="form-select"
                                            id="gender">
                                            <option value="">Select Role</option>
                                            <option value="user">User</option>
                                            <option value="admin">Admin</option>
                                            <option value="super_admin">Super Admin</option>
                                        </select>
                                        @error('createRole')
                                            <p class="text-danger">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="my-3">
                                        <label for="gender">Graduated</label>
                                        <select name="gender" wire:model='createGraduated' class="form-select"
                                            id="gender">
                                            <option value=""> Graduated</option>
                                            <option value="true">Yes</option>
                                            <option value="false">No</option>
                                        </select>
                                        @error('createGraduated')
                                            <p class="text-danger">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="my-3">
                                        <label for="gender">Year</label>
                                        <select name="year" wire:model='createYear' class="form-select"
                                            id="gender">
                                            <option value=""> Year</option>
                                            <option value="1">Year 1</option>
                                            <option value="2">Year 2</option>
                                            <option value="3">Year 3</option>
                                            <option value="4">Year 4</option>
                                            <option value="5">Year 5</option>
                                            <option value="6">Year 6</option>
                                        </select>
                                        @error('createYear')
                                            <p class="text-danger">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="my-1">
                                        <label for="gender">Gender</label>
                                        <select name="gender" wire:model='createGender' class="form-select"
                                            id="gender">
                                            <option value="">Select Gender</option>
                                            <option value="male">Male</option>
                                            <option value="female">female</option>
                                        </select>
                                        @error('createGender')
                                            <p class="text-danger">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="my-2">
                                        <label for="image">Image</label>
                                        <input type="file" wire:model='image' name="image">
                                    </div>
                                    @error('image')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                    <x-forms.input type="password" name="password" label="Password"
                                        wire:model="password"></x-forms.input>


                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-primary" wire:click='create'\
                                        data-bs-dismiss="modal">Create
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            {{-- Active filters --}}
            @if ($this->hasFilters)

                <div class="d-flex flex-wrap align-items-center gap-2 mt-3 pt-3 border-top">

                    <small class="text-muted me-1">
                        Active:
                    </small>

                    @if ($search)
                        <span class="badge bg-label-primary">
                            Search: {{ $search }}
                        </span>
                    @endif

                    @if ($year)
                        <span class="badge bg-label-primary">
                            Year {{ $year }}
                        </span>
                    @endif

                    @if ($country)
                        <span class="badge bg-label-primary">
                            {{ $country }}
                        </span>
                    @endif

                    @if ($gender)
                        <span class="badge bg-label-primary">
                            {{ ucfirst($gender) }}
                        </span>
                    @endif

                    @if ($role)
                        <span class="badge bg-label-primary">
                            {{ ucfirst(str_replace('_', ' ', $role)) }}
                        </span>
                    @endif

                    @if ($graduated !== '')
                        <span class="badge bg-label-primary">
                            {{ $graduated === 'true' ? 'Graduated' : 'Not graduated' }}
                        </span>
                    @endif

                    <button type="button" wire:click="clearFilters"
                        class="btn btn-sm btn-link text-danger p-0 ms-1">
                        Clear
                    </button>

                </div>

            @endif

        </div>


        {{-- =========================
            TABLE
        ========================== --}}
        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead>

                    <tr>

                        <th class="ps-4">
                            ID
                        </th>

                        <th>
                            User
                        </th>

                        <th>
                            Email
                        </th>

                        <th>
                            Country
                        </th>

                        <th>
                            Year
                        </th>

                        <th class="text-center">
                            Rank
                        </th>


                        <th>
                            Role
                        </th>

                        <th>
                            Gender
                        </th>

                        <th>
                            Graduation
                        </th>

                        <th class="text-end pe-4">
                            Actions
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($this->users as $user)
                        <tr wire:key="user-{{ $user->id }}">

                            {{-- ID --}}
                            <td class="ps-4 text-muted">
                                #{{ $user->id }}
                            </td>

                            {{-- User --}}
                            <td>

                                <div class="d-flex align-items-center gap-3">

                                    <div class="avatar avatar-sm">

                                        @if ($user->image)
                                            <img src="{{ $user->image_url }}"
                                                alt="{{ $user->name }}" class="rounded-circle">
                                        @else
                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </span>
                                        @endif

                                    </div>

                                    <div>

                                        <div class="fw-semibold">
                                            {{ $user->name }}
                                        </div>

                                        <small class="text-muted">
                                            Joined {{ $user->created_at?->format('M d, Y') }}
                                        </small>

                                    </div>

                                </div>

                            </td>

                            {{-- Email --}}
                            <td>
                                <span class="text-body">
                                    {{ $user->email }}
                                </span>
                            </td>

                            {{-- Country --}}
                            <td>

                                <div class="d-flex align-items-center gap-2">

                                    <i class="icon-base ti tabler-map-pin text-muted"></i>

                                    <span>
                                        {{ $user->country ?: '—' }}
                                    </span>

                                </div>

                            </td>

                            {{-- Year --}}
                            <td>

                                @if ($user->year)
                                    <span class="badge bg-label-primary">
                                        Year {{ $user->year }}
                                    </span>
                                @else
                                    <span class="text-muted">
                                        —
                                    </span>
                                @endif

                            </td>

                            {{-- Rank --}}
                            <td class="text-center">

                                <span class="fw-semibold">
                                    {{ number_format($user->rank) }}
                                </span>

                            </td>

                            {{-- Game Rank --}}


                            {{-- Role --}}
                            <td>

                                @switch($user->role)
                                    @case('super_admin')
                                        <span class="badge bg-label-danger">
                                            <i class="icon-base ti tabler-shield me-1"></i>
                                            Super Admin
                                        </span>
                                    @break

                                    @case('admin')
                                        <span class="badge bg-label-warning">
                                            <i class="icon-base ti tabler-shield-check me-1"></i>
                                            Admin
                                        </span>
                                    @break

                                    @default
                                        <span class="badge bg-label-secondary">
                                            User
                                        </span>
                                @endswitch

                            </td>

                            {{-- Gender --}}
                            <td>

                                @if ($user->gender === 'male')
                                    <span class="text-body">
                                        Male
                                    </span>
                                @elseif($user->gender === 'female')
                                    <span class="text-body">
                                        Female
                                    </span>
                                @else
                                    <span class="text-muted">
                                        —
                                    </span>
                                @endif

                            </td>

                            {{-- Graduation --}}
                            <td>

                                @if ($user->graduated)
                                    <span class="text-success fw-medium">
                                        <i class="icon-base ti tabler-circle-check me-1"></i>
                                        Graduated
                                    </span>
                                @else
                                    <span class="text-muted">
                                        <i class="icon-base ti tabler-circle-x me-1"></i>
                                        Not graduated
                                    </span>
                                @endif

                            </td>

                            {{-- Actions --}}
                            <td class="text-end pe-4">

                                <div class="dropdown">

                                    <button type="button" class="btn btn-sm btn-icon dropdown-toggle hide-arrow"
                                        data-bs-toggle="dropdown">
                                        <i class="icon-base ti tabler-dots-vertical"></i>
                                    </button>

                                    <div class="dropdown-menu dropdown-menu-end">

                                        <button type="button" class="dropdown-item" data-bs-toggle="modal"
                                            data-bs-target="#staticBackdropEdit"
                                            wire:click='$dispatch("userSelected" , {id: {{ $user->id }}})'>

                                            <i class="icon-base ti tabler-pencil me-2"></i>
                                            Edit
                                        </button>

                                        <button type="button" class="dropdown-item">
                                            <i class="icon-base ti tabler-eye me-2"></i>
                                            View
                                        </button>

                                        <div class="dropdown-divider"></div>

                                        <button wire:confirm wire:click='delete({{ $user->id }})' type="button"
                                            class="dropdown-item text-danger">
                                            <i class="icon-base ti tabler-trash me-2"></i>
                                            Delete
                                        </button>

                                    </div>

                                </div>

                            </td>

                        </tr>

                        @empty

                            <tr>

                                <td colspan="11" class="text-center py-5">

                                    <div class="d-flex flex-column align-items-center">

                                        <div class="avatar avatar-lg mb-3" style="width: 64px; height: 64px;">
                                            <span class="avatar-initial rounded-circle bg-label-secondary">
                                                <i class="icon-base ti tabler-users-off fs-3"></i>
                                            </span>
                                        </div>

                                        <h6 class="mb-1">
                                            No users found
                                        </h6>

                                        <p class="text-muted mb-3">
                                            Try changing your search or filters.
                                        </p>

                                        @if ($this->hasFilters)
                                            <button type="button" wire:click="clearFilters"
                                                class="btn btn-sm btn-outline-primary">
                                                Clear filters
                                            </button>
                                        @endif

                                    </div>

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- =========================
            FOOTER
        ========================== --}}
            <div class="card-footer border-top py-3">

                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">

                    <div class="text-muted small">

                        Showing
                        <span class="fw-semibold">
                            {{ $this->users->firstItem() ?? 0 }}
                        </span>

                        to

                        <span class="fw-semibold">
                            {{ $this->users->lastItem() ?? 0 }}
                        </span>

                        users

                    </div>

                    <div>
                        {{ $this->users->links() }}
                    </div>

                </div>
                <div class="modal fade" id="staticBackdropEdit" data-bs-backdrop="static" data-bs-keyboard="false"
                    tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="staticBackdropLabel">{{ $selectedUser?->name }}</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <x-forms.input name="name" value="{{ $selectedUser?->name }}" label="Name"
                                    wire:model="name"></x-forms.input>
                                <x-forms.input type="email" value={{ $selectedUser?->email }} name="email"
                                    label="Email" wire:model="email"></x-forms.input>
                                <div class="my-3">
                                    <label for="gender">Role</label>
                                    <select name="role" wire:model='createRole' class="form-select" id="gender">
                                        <option value="">Select Role</option>
                                        <option value="user" @selected($selectedUser?->role === 'user')>User</option>
                                        <option value="admin" @selected($selectedUser?->role === 'admin')>Admin</option>
                                        <option value="super_admin" @selected($selectedUser?->role === 'super_admin')>Super Admin</option>
                                    </select>
                                    @error('createRole')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="my-3">
                                    <label for="gender">Graduated</label>
                                    <select name="gender" wire:model='createGraduated' class="form-select"
                                        id="gender">
                                        <option value=""> Graduated</option>
                                        <option value="true" @selected($selectedUser?->graduated === 'true')>Yes</option>
                                        <option value="false" @selected($selectedUser?->graduated === 'false')>No</option>
                                    </select>
                                    @error('createGraduated')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="my-3">
                                    <label for="gender">Year</label>
                                    <select name="year" wire:model='createYear' class="form-select" id="gender">
                                        <option value=""> Year</option>
                                        <option value="1" @selected($selectedUser?->year === '1')>Year 1</option>
                                        <option value="2" @selected($selectedUser?->year === '2')>Year 2</option>
                                        <option value="3" @selected($selectedUser?->year === '3')>Year 3</option>
                                        <option value="4" @selected($selectedUser?->year === '4')>Year 4</option>
                                        <option value="5" @selected($selectedUser?->year === '5')>Year 5</option>
                                        <option value="6" @selected($selectedUser?->year === '6')>Year 6</option>
                                    </select>
                                    @error('createYear')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="my-1">
                                    <label for="gender">Gender</label>
                                    <select name="gender" wire:model='createGender' class="form-select" id="gender">
                                        <option value="">Select Gender</option>
                                        <option value="male" @selected($selectedUser?->gender === 'male')>Male</option>
                                        <option value="female" @selected($selectedUser?->gender === 'female')>female</option>
                                    </select>
                                    @error('createGender')
                                        <p class="text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <img width="200px" height="200px" src="{{ $selectedUser?->image_url }}"
                                        alt="Here ">
                                </div>
                                <div class="my-2">
                                    <label for="image">Image</label>
                                    <input type="file" wire:model='image' name="image">
                                </div>
                                @error('image')
                                    <p class="text-danger">{{ $message }}</p>
                                @enderror
                                <x-forms.input type="password" name="password" label="Password"
                                    wire:model="password"></x-forms.input>

                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" wire:click='update'
                                    data-bs-dismiss="modal">Update
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
