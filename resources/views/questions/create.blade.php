<x-app>
    <x-slot:title>Create Questions</x-slot:title>
    <form action="{{ route("questions.store") }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="col-lg-12">
            <div class="row">
                <div class="col-md-6 col-lg-6">
                    <div class="card">
                        <h5 class="card-header">Create Question</h5>
                        <div class="card-body">
                            <x-textarea label="Content" name="content"> </x-textarea>
                            <x-textarea label="High Yield" name="high_yield"> </x-textarea>
                            <x-textarea label="Main Expalantion" name="main_explanation"> </x-textarea>
                            <x-textarea label="Topic" name="topic"> </x-textarea>
                            <div class="col-lg-12 my-3">
                                <label for="select2Primary" class="form-label">Speciality</label>
                                <div class="select2-primary">
                                    <select
                                        name="speciality[]"
                                        id="specialities"
                                        class="select2 form-select speciality"
                                        multiple
                                    >
                                        @foreach ($oldSpecialities ?? [] as $s)
                                            <option value="{{ $s?->id }}" selected>{{ $s?->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('speciality')
                                    <p class="text-danger py-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="col-lg-12 my-3">
                                <label for="select2Primary" class="form-label">Branches For Medicine</label>
                                <div class="select2-primary">
                                    <select name="branches[]" id="branches" class="select2 form-select branch" multiple>
                                        @foreach ($oldBranches ?? [] as $b)
                                            <option value="{{ $b?->id }}" selected>{{ $b?->name }}</option>
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
                                    <select name="skills[]" id="skills" class="select2 form-select skills" multiple>
                                        @foreach ($oldSkills ?? [] as $s)
                                            <option value="{{ $s?->id }}" selected>{{ $s?->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('skills')
                                    <p class="text-danger py-2">{{ $message }}</p>
                                @enderror
                            </div>
                            {{-- ? Difficulty --}}
                            <div class="mt-4 mb-4">
                                <label for="exampleFormControlInput1" class="form-label">Difficulty </label>
                                <select class="form-select" name="difficulty">
                                    <option value="">Select Difficulty</option>
                                    <option value="easy" @selected(old('difficulty') === 'easy')>Easy</option>
                                    <option value="medium" @selected(old('difficulty') === 'medium')>Medium</option>
                                    <option value="hard" @selected(old('difficulty') === 'hard')>Hard</option>
                                    <option value="nerd" @selected(old('difficulty') === 'nerd')>Nerd</option>
                                </select>
                                @error('difficulty')
                                    <p class="text-danger py-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mt-4 mb-4">
                                <label for="exampleFormControlInput1" class="form-label">Refernce </label>
                                <select class="form-select" name="reference">
                                    <option value="">Select Reference</option>
                                    <option value="UW" @selected(old('reference') === 'UW')>UW</option>
                                    <option value="MRCP" @selected(old('reference') === 'MRCP')>MRCP</option>
                                    <option value="MCC Qe" @selected(old('reference') === 'MCC Qe')>MCC Qe</option>
                                </select>
                                @error('reference')
                                    <p class="text-danger py-1">{{ $message }}</p>
                                @enderror
                            </div>
                            {{-- Length --}}
                            <div class="mt-4 mb-4">
                                <label for="exampleFormControlInput1" class="form-label">Length</label>
                                <select class="form-select" name="length">
                                    <option value="">Select Length</option>
                                    <option value="short" @selected(old('length') == 'short')>Short</option>
                                    <option value="medium" @selected(old('length') == 'medium')>Medium</option>
                                    <option value="long" @selected(old('length') == 'long')>Long</option>
                                </select>
                                @error('length')
                                    <p class="text-danger py-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mt-4 mb-4">
                                <label for="exampleFormControlInput1" class="form-label">Elo Correct</label>
                                <select class="form-select" name="elo_correct">
                                    <option value="">Select Elo Correct</option>
                                    <option value="4" @selected(old('elo_correct') === '4')>4</option>
                                    <option value="8" @selected(old('elo_correct') === '8')>8</option>
                                    <option value="12" @selected(old('elo_correct') === '12')>12</option>
                                </select>
                                @error('elo_correct')
                                    <p class="text-danger py-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mt-4 mb-4">
                                <label for="exampleFormControlInput1" class="form-label">Elo InCorrect</label>
                                <select class="form-select" name="elo_incorrect">
                                    <option value="">Select Elo InCorrect</option>
                                    <option value="5" @selected(old('elo_incorrect') === '5')>5</option>
                                    <option value="10" @selected(old('elo_incorrect') === '10')>10</option>
                                    <option value="15" @selected(old('elo_incorrect') === '15')>15</option>
                                </select>
                                @error('elo_incorrect')
                                    <p class="text-danger py-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div id="" class="mt-4">
                                <img class="rounded-5" id="image-preview" width="100%" height="300px" src="" alt="" />
                            </div>
                            <div class="mt-4 mb-4">
                                <label for="exampleFormControlInput1" class="form-label">Question Image </label>
                                <div>
                                    <input class="form-control" id="questionImage" type="file" name="image" />
                                    @error('image')
                                        <p class="text-danger py-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div class="text-end">
                                <button class="btn btn-primary">Create Question</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6">
                    <div class="card">
                        <h5 class="card-header">Create Question Options</h5>
                        <div class="card-body">
                            <p id="para" role="alert"></p>
                            <div class="mt-4 mb-4">
                                <label for="exampleFormControlInput1" class="form-label">Options Number</label>
                                <input
                                    type="number"
                                    name="options_number"
                                    max="5"
                                    class="form-control"
                                    value="{{ old("options_number") }}"
                                    id="options_number"
                                    placeholder="Enter Option Number That You Want to Create"
                                />
                            </div>
                            @error('options_number')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                            <p style="font-size: 12px; margin-top: -10px" class="fw-bold">
                                <strong>
                                    * <span class="text-danger">Be Careful </span> The Number You Put Is how many
                                    options you want to generate*
                                </strong>
                            </p>
                            <div id="step-1">
                                <x-forms.option number="1"></x-forms.option>
                            </div>
                            <div style="display: none" id="step-2">
                                <x-forms.option number="2"></x-forms.option>
                            </div>
                            <div style="display: none" id="step-3">
                                <x-forms.option number="3"></x-forms.option>
                            </div>
                            <div style="display: none" id="step-4">
                                <x-forms.option number="4"></x-forms.option>
                            </div>
                            <div style="display: none" id="step-5">
                                <x-forms.option number="5"></x-forms.option>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    @push('scripts')
        <script src="{{ asset('assets/js/question.js') }}"></script>
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
                            return { search: params.term };
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
                            return { search: params.term };
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
                            return { search: params.term };
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
        </script>
    @endpush
</x-app>
