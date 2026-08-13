<x-app>
    <x-slot:title>Create Quiz</x-slot:title>

    <livewire:create-quiz :skillsList="$skills" :branchesList="$branches" :specialityList="$speciality" />
</x-app>
<script>
    $(document).ready(function () {
        $('.questions').select2();
    });
</script>
