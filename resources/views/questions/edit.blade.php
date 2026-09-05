<x-app>
    <x-slot:title>Edit Question</x-slot:title>
    <form action="{{ route('questions.update', $question->id) }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        <div class="col-lg-12">
            <div class="row">
                <div class="col-md-6 col-lg-6">
                    <div class="card">
                        <h5 class="card-header">Create Question</h5>
                        <div class="card-body">
                            <x-forms.textarea value="{{ $question->content }}" label="Content" name="content">
                            </x-forms.textarea>
                            <x-forms.textarea value="{{ $question->high_yield }}" label="High Yield" name="high_yield">
                            </x-forms.textarea>
                            <x-forms.textarea
                                value="{{ $question->main_explanation }}"
                                label="Main Expalantion"
                                name="main_explanation"
                            >
                            </x-forms.textarea>
                            <x-forms.textarea value="{{ $question->topic }}" label="Topic" name="topic">
                            </x-forms.textarea>
                            {{-- ? Difficulty --}}
                            <div class="mt-4 mb-4">
                                <label for="exampleFormControlInput1" class="form-label">Difficulty </label>
                                <select class="form-select" name="difficulty">
                                    {{-- There Are Things That More Important Than Other --}}
                                    <option value="easy" @selected(old('difficulty', $question->difficulty) === 'easy')>
                                        Easy
                                    </option>
                                    <option
                                        value="meduim"
                                        @selected(old('difficulty', $question->difficulty) === 'meduim')
                                    >
                                        Meduim
                                    </option>
                                    <option value="hard" @selected(old('difficulty', $question->difficulty) === 'hard')>
                                        Hard
                                    </option>
                                    <option value="nerd" @selected(old('difficulty', $question->difficulty) === 'nerd')>
                                        Nerd
                                    </option>
                                </select>
                                @error('difficulty')
                                    <p class="text-danger py-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Length --}}
                            <div class="mt-4 mb-4">
                                <label for="exampleFormControlInput1" class="form-label">Length</label>
                                <select class="form-select" name="length">
                                    <option value="short" @selected(old('length', $question->length) == 'short')>
                                        Short
                                    </option>
                                    <option value="meduim" @selected(old('length', $question->length) == 'meduim')>
                                        Meduim
                                    </option>
                                    <option value="long" @selected(old('length', $question->length) == 'long')>
                                        Long
                                    </option>
                                </select>
                                @error('legth')
                                    <p class="text-danger py-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mt-4 mb-4">
                                <label for="exampleFormControlInput1" class="form-label">Elo Correct</label>
                                <select class="form-select" name="elo_correct">
                                    <option value="4" @selected(old('elo_correct', $question->elo_correct) == '4')>
                                        4
                                    </option>
                                    <option value="8" @selected(old('elo_correct', $question->elo_correct) == '8')>
                                        8
                                    </option>
                                    <option value="12" @selected(old('elo_correct', $question->elo_correct) == '12')>
                                        12
                                    </option>
                                </select>
                                @error('elo_correct')
                                    <p class="text-danger py-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mt-4 mb-4">
                                <label for="exampleFormControlInput1" class="form-label">Elo InCorrect</label>
                                <select class="form-select" name="elo_incorrect">
                                    <option value="5" @selected(old('elo_incorrect', $question->elo_incorrect) == '5')>
                                        5
                                    </option>
                                    <option
                                        value="10"
                                        @selected(old('elo_incorrect', $question->elo_incorrect) == '10')
                                    >
                                        10
                                    </option>
                                    <option
                                        value="15"
                                        @selected(old('elo_incorrect', $question->elo_incorrect) == '15')
                                    >
                                        15
                                    </option>
                                </select>
                                @error('elo_icorrect')
                                    <p class="text-danger py-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="col-lg-12 my-3">
                                <label for="select2Primary" class="form-label">Speciality</label>
                                <div class="select2-primary">
                                    <select
                                        id="specialities"
                                        name="speciality[]"
                                        class="select2 form-select speciality"
                                        multiple
                                    >
                                        @foreach ($oldSpecialities as $s)
                                            <option value="{{ $s->id }}" selected>
                                                {{ Str::limit($s->name, 20) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('speciality')
                                    <p class="text-danger py-2">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mt-4 mb-4">
                                <label for="exampleFormControlInput1" class="form-label">Reference </label>
                                <select id="references" class="form-select select2" name="reference">
                                    @if ($oldReference)
                                        <option value="{{ $oldReference?->id }}">{{ $oldReference?->name }}</option>
                                    @else
                                        <option value=""></option>
                                    @endif
                                </select>
                                @error('reference')
                                    <p class="text-danger py-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="col-lg-12 my-3">
                                <label for="select2Primary" class="form-label">Branches For Medicine</label>
                                <div class="select2-primary">
                                    <select id="branches" name="branches[]" class="select2 form-select branch" multiple>
                                        @foreach ($oldBranches as $branch)
                                            <option value="{{ $branch->id }}" selected>
                                                {{ Str::limit($branch->name, 20) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('branches')
                                    <p class="text-danger py-2">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="col-lg-12 my-3">
                                <label for="select2Primary" class="form-label">Skills For Question</label>
                                <div class="select2-primary">
                                    <select id="skills" name="skills[]" class="select2 form-select skills" multiple>
                                        @foreach ($oldSkills as $skill)
                                            <option value="{{ $skill->id }}" selected>
                                                {{ Str::limit($skill->name, 20) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('skills')
                                    <p class="text-danger py-2">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <img
                                    id="preview"
                                    class="rounded-5"
                                    width="100%"
                                    height="300px"
                                    src="{{ asset($question->image) }}"
                                    alt="No Image Found"
                                />
                            </div>
                            <div class="mt-4 mb-4">
                                <label for="exampleFormControlInput1" class="form-label">Question Image </label>
                                <div>
                                    <input class="form-control" type="file" name="image" />
                                    @error('image')
                                        <p class="text-danger py-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div class="text-end">
                                <button class="btn btn-primary">Update Question</button>
                            </div>
                        </div>
                    </div>
                </div>
    </form>

    @push('scripts')
        <script>
            $(window).on('load', function () {
                if ($('#branches').hasClass('select2-hidden-accessible')) {
                    $('#branches').select2('destroy');
                }
                $('#branches').select2({
                    placeholder: 'Search for Branches ', // Your placeholder text
                    ajax: {
                        url: "{{ route('getBranches') }}",
                        type: 'GET',
                        delay: 250,
                        data: function (params) {
                            return {
                                search: params.term,
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: data.map((branch) => ({
                                    id: branch.id,
                                    text: branch.name,
                                })),
                            };
                        },
                    },
                });
                if ($('#specialities').hasClass('select2-hidden-accessible')) {
                    $('#specialities').select2('destroy');
                }

                $('#specialities').select2({
                    placeholder: 'Search for Specialities ',
                    ajax: {
                        url: "{{ route('getSpeciality') }}",
                        type: 'GET',
                        delay: 250,
                        data: function (params) {
                            return {
                                search: params.term,
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: data.map((s) => ({
                                    id: s.id,
                                    text: s.name,
                                })),
                            };
                        },
                    },
                });
                $('#references').select2({
                    placeholder: 'Search for Reference',
                    allowClear: true,
                    ajax: {
                        url: "{{ route('getReferences') }}",
                        type: 'GET',
                        delay: 250,
                        data: function (params) {
                            return {
                                search: params.term,
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: data.map((ref) => ({
                                    id: ref.id,
                                    text: ref.name,
                                })),
                            };
                        },
                    },
                });
                if ($('#skills').hasClass('select2-hidden-accessible')) {
                    $('#skills').select2('destroy');
                }
                $('#skills').select2({
                    placeholder: 'Search for Skills ',
                    ajax: {
                        url: "{{ route('getSkills') }}",
                        type: 'GET',
                        delay: 250,
                        data: function (params) {
                            return {
                                search: params.term,
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: data.map((skill) => ({
                                    id: skill.id,
                                    text: skill.name,
                                })),
                            };
                        },
                    },
                });
            });

            const input = document.querySelector('input[name="image"]');
            const preview = document.getElementById('preview');

            input.addEventListener('change', () => {
                const file = input.files[0];

                if (file) {
                    preview.src = URL.createObjectURL(file);
                }
            });
        </script>
    @endpush
</x-app>
