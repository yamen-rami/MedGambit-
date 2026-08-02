<x-app>
  <x-slot:title>
    Create Questions
  </x-slot:title>
  <form action="{{ route("questions.update", $question->id) }}" method="post" enctype="multipart/form-data">
    @csrf
    @method("PATCH")
    <div class="col-lg-12  ">
      <div class="row">

        <div class="col-md-6  col-lg-6 ">
          <div class="card">
            <h5 class="card-header">Create Question</h5>
            <div class="card-body">
              <x-forms.textarea value="{{ $question->content }}" label="Content" name="content"> </x-forms.textarea>
              <x-forms.textarea value="{{ $question->high_yield }}" label="High Yield" name="high_yield">
              </x-forms.textarea>
              <x-forms.textarea value="{{ $question->main_explanation }}" label="Main Expalantion"
                name="main_explanation"> </x-forms.textarea>
              <x-forms.textarea value="{{ $question->topic }}" label="Topic" name="topic"> </x-forms.textarea>
              {{-- ? Difficulty --}}
              <div class="mb-4 mt-4">
                <label for="exampleFormControlInput1" class="form-label">Difficulty </label>
                <select class="form-select" name="difficulty">
                  {{-- There Are Things That More Important Than Other --}}
                  <option value="easy" @selected(old("difficulty", $question->difficulty) === "easy")>Easy</option>
                  <option value="meduim" @selected(old("difficulty", $question->difficulty) === "meduim")>Meduim</option>
                  <option value="hard" @selected(old("difficulty", $question->difficulty) === "hard")>Hard</option>
                  <option value="nerd" @selected(old("difficulty", $question->difficulty) === "nerd")>Nerd</option>
                </select>
                @error("difficulty")
                  <p class="text-danger py-1">{{ $message }}</p>
                @enderror
              </div>
              <div class="mb-4 mt-4">
                <label for="exampleFormControlInput1" class="form-label">Refernce </label>
                <select class="form-select" name="reference">
                  <option value="UW" @selected(old("reference", $question->reference) === "UW")>UW</option>
                  <option value="MRCP" @selected(old("reference", $question->reference) === "MRCP")>MRCP</option>
                  <option value="MCC Qe" @selected(old("reference", $question->reference) === "MCC Qe")>MCC Qe</option>
                </select>
                @error("refernce")
                  <p class="text-danger py-1">{{ $message }}</p>
                @enderror
              </div>

              {{-- Length --}}
              <div class="mb-4 mt-4">
                <label for="exampleFormControlInput1" class="form-label">Length</label>
                <select class="form-select" name="length">
                  <option value="short" @selected(old("length", $question->length) == "short")>Short</option>
                  <option value="meduim" @selected(old("length", $question->length) == "meduim")>Meduim</option>
                  <option value="long" @selected(old("length", $question->length) == "long")>Long</option>
                </select>
                @error("legth")
                  <p class="text-danger py-1">{{ $message }}</p>
                @enderror
              </div>
              <div class="mb-4 mt-4">
                <label for="exampleFormControlInput1" class="form-label">Elo Correct</label>
                <select class="form-select" name="elo_correct">
                  <option value="4" @selected(old("elo_correct", $question->elo_correct) == "4")>4</option>
                  <option value="8" @selected(old("elo_correct", $question->elo_correct) == "8")>8</option>
                  <option value="12" @selected(old("elo_correct", $question->elo_correct) == "12")>12</option>
                </select>
                @error("elo_correct")
                  <p class="text-danger py-1">{{ $message }}</p>
                @enderror
              </div>
              <div class="mb-4 mt-4">
                <label for="exampleFormControlInput1" class="form-label">Elo InCorrect</label>
                <select class="form-select" name="elo_incorrect">
                  <option value="5" @selected(old("elo_incorrect", $question->elo_incorrect) == "5")>5</option>
                  <option value="10" @selected(old("elo_incorrect", $question->elo_incorrect) == "10")>10</option>
                  <option value="15" @selected(old("elo_incorrect", $question->elo_incorrect) == "15")>15</option>
                </select>
                @error("elo_icorrect")
                  <p class="text-danger py-1">{{ $message }}</p>
                @enderror
              </div>

              <div class="col-lg-12 my-3">
                <label for="select2Primary" class="form-label ">Speciality</label>
                <div class="select2-primary">
                  <select id="select2Primary" name="speciality[]" class="select2 form-select speciality " multiple>
                    @foreach ($spicality as $special)
                      <option value="{{ $special->id }}">{{ Str::limit($special->name, 20) }}</option>
                    @endforeach
                  </select>
                </div>
                @error('speciality')
                  <p class="text-danger py-2">{{ $message }}</p>
                @enderror
              </div>
              <div class="col-lg-12 my-3">
                <label for="select2Primary" class="form-label ">Branches For Medicine</label>
                <div class="select2-primary">
                  <select id="select2Danger" name="branches[]" class="select2 form-select branch " multiple>
                    @foreach ($branches as $branch)
                      <option value="{{ $branch->id }}">{{ Str::limit($branch->name, 20) }}</option>
                    @endforeach
                  </select>
                </div>
                @error('branches')
                  <p class="text-danger py-2">{{ $message }}</p>
                @enderror
              </div>
              <div class="col-lg-12 my-3">
                <label for="select2Primary" class="form-label ">Skills For Question</label>
                <div class="select2-primary">
                  <select id="select2Success" name="skills[]" class="select2 form-select skills" multiple>
                    @foreach ($skills as $skill)
                      <option value="{{ $skill->id }}">{{ Str::limit($skill->name, 20) }}</option>
                    @endforeach
                  </select>
                </div>
                @error('skills')
                  <p class="text-danger py-2">{{ $message }}</p>
                @enderror
              </div>
              <div>
                <img id="preview" class="rounded-5" width="100%" height="300px" src="{{ asset($question->image) }}"
                  alt="No Image Found">
              </div>
              <div class="mb-4 mt-4">
                <label for="exampleFormControlInput1" class="form-label">Question Image </label>
                <div>
                  <input class="form-control" type="file" name="image">
                  @error("image")
                    <p class="text-danger py-1">{{ $message }}</p>
                  @enderror
                </div>
              </div>

              <div class="text-end">
                <button class="btn btn-primary">
                  Update Question
                </button>
              </div>
            </div>
          </div>
        </div>
  </form>
</x-app>
<script>
  const input = document.querySelector('input[name="image"]');
  const preview = document.getElementById('preview');

  input.addEventListener('change', () => {
    const file = input.files[0];

    if (file) {
      preview.src = URL.createObjectURL(file);
    }
  });
  $(".speciality").select2();
  $(".branch").select2();
  $(".skills").select2();
</script>