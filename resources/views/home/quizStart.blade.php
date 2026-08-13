<x-user-layout>
    <x-slot:title>Start A Quiz</x-slot:title>
    {{-- <livewire:start-quiz /> --}}
    <div class="card px-4 py-6">
        <h1 class="fs-4 fw-bold">Start A Quiz</h1>
        {{-- --}}
        <div>
            <button class="btn btn-danger">
                <a class="text-white" href="{{ route("start.random.quiz") }}">Start Random Questions</a>
            </button>
        </div>
        <livewire:detected-quiz />
    </div>
</x-user-layout>
