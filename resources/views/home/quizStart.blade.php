<x-user-layout>
  <x-slot:title>
    Start A Quiz
  </x-slot:title>
  {{-- <livewire:start-quiz /> --}}
  <div class="card px-4 py-6">
    <h1 class="fs-4 fw-bold">Start A Quiz </h1>
    {{-- --}}
    {{-- @dd($skills, $branches) --}}
    <div>
      <button class="btn btn-danger "><a class="text-white" href="{{ route("start.random.quiz") }}">Start Random
          Questions</a></button>
    </div>
    <livewire:detected-quiz :branches="$branches" :specialities="$specialities" :skills="$skills" />
    

  </div>
</x-user-layout>