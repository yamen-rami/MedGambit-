<x-app>
  <x-slot:title>
    Create Questions
  </x-slot:title>
  <form action="{{ route("questions.store") }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="col-lg-12  ">
      <div class="row">
        <div class="col-md-6  col-lg-6 ">
          <div class="card">
            <h5 class="card-header">Create Question</h5>
            <div class="card-body">
              <x-textarea label="Content" name="content"> </x-textarea>
              <x-textarea label="High Yield" name="high_yield"> </x-textarea>
              <x-textarea label="Main Expalantion" name="main_explanation"> </x-textarea>
              <x-textarea label="Topic" name="topic"> </x-textarea>
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
              {{-- ? Difficulty --}}
              <div class="mb-4 mt-4">
                <label for="exampleFormControlInput1" class="form-label">Difficulty </label>
                <select class="form-select" name="difficulty">
                  <option value="">Select Difficulty</option>
                  <option value="easy" @selected(old("difficulty") === "easy")>Easy</option>
                  <option value="medium" @selected(old("difficulty") === "medium")>Medium</option>
                  <option value="hard" @selected(old("difficulty") === "hard")>Hard</option>
                  <option value="nerd" @selected(old("difficulty") === "nerd")>Nerd</option>
                </select>
                @error("difficulty")
                  <p class="text-danger py-1">{{ $message }}</p>
                @enderror
              </div>
              <div class="mb-4 mt-4">
                <label for="exampleFormControlInput1" class="form-label">Refernce </label>
                <select class="form-select" name="reference">
                  <option value="">Select Reference</option>
                  <option value="UW" @selected(old("reference") === "UW")>UW</option>
                  <option value="MRCP" @selected(old("reference") === "MRCP")>MRCP</option>
                  <option value="MCC Qe" @selected(old("reference") === "MCC Qe")>MCC Qe</option>
                </select>
                @error("reference")
                  <p class="text-danger py-1">{{ $message }}</p>
                @enderror
              </div>
              {{-- Length --}}
              <div class="mb-4 mt-4">
                <label for="exampleFormControlInput1" class="form-label">Length</label>
                <select class="form-select" name="length">
                  <option value="">Select Length</option>
                  <option value="short" @selected(old("length") == "short")>Short</option>
                  <option value="medium" @selected(old("length") == "medium")>Medium</option>
                  <option value="long" @selected(old("length") == "long")>Long</option>
                </select>
                @error("length")
                  <p class="text-danger py-1">{{ $message }}</p>
                @enderror
              </div>
              <div class="mb-4 mt-4">
                <label for="exampleFormControlInput1" class="form-label">Elo Correct</label>
                <select class="form-select" name="elo_correct">
                  <option value="">Select Elo Correct</option>
                  <option value="4" @selected(old("elo_correct") === "4")>4</option>
                  <option value="8" @selected(old("elo_correct") === "8")>8</option>
                  <option value="12" @selected(old("elo_correct") === "12")>12</option>
                </select>
                @error("elo_correct")
                  <p class="text-danger py-1">{{ $message }}</p>
                @enderror
              </div>
              <div class="mb-4 mt-4">
                <label for="exampleFormControlInput1" class="form-label">Elo InCorrect</label>
                <select class="form-select" name="elo_incorrect">
                  <option value="">Select Elo InCorrect</option>
                  <option value="5" @selected(old("elo_incorrect") === "5")>5</option>
                  <option value="10" @selected(old("elo_incorrect") === "10")>10</option>
                  <option value="15" @selected(old("elo_incorrect") === "15")>15</option>
                </select>
                @error("elo_incorrect")
                  <p class="text-danger py-1">{{ $message }}</p>
                @enderror
              </div>
              <div id="" class="mt-4">
                <img class="rounded-5" id="image-preview" width="100%" height="300px" src="" alt="">
              </div>
              <div class="mb-4 mt-4">
                <label for="exampleFormControlInput1" class="form-label">Question Image </label>
                <div>
                  <input class="form-control" id="questionImage" type="file" name="image">
                  @error("image")
                    <p class="text-danger py-1">{{ $message }}</p>
                  @enderror
                </div>
              </div>
              <div class="text-end">
                <button class="btn btn-primary">
                  Create Question
                </button>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-6 ">
          <div class="card">
            <h5 class="card-header">Create Question Options</h5>
            <div class="card-body">
              <p id="para" role="alert"></p>
              <div class="mb-4 mt-4">
                <label for="exampleFormControlInput1" class="form-label">Options Number</label>
                <input type="number" name="options_number" max="5" class="form-control"
                  value="{{ old("options_number") }}" id="options_number"
                  placeholder="Enter Option Number That You Want to Create" />
              </div>
              @error("options_number")
                <p class="text-danger">{{ $message }}</p>
              @enderror
              <p style="font-size:12px ; margin-top: -10px;" class="fw-bold  "> <strong> * <span class="text-danger">Be
                    Careful </span> The Number You Put Is how many options you want to generate* </strong></p>
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
</x-app>
<script>
  $(".speciality").select2();
  $(".branch").select2();
  $(".skills").select2();
</script>