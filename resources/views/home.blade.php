<x-user-layout>
    <input type="text" id="search" />
    <script>
        let searchInput = document.getElementById('search');
        searchInput.addEventListener('keypress', async function getInfo(event) {
            console.log(search.value);
            let response = await fetch(`get/branches/${event.target.value}`);
            let results = await response.json();
            console.log(results);
        });
    </script>
</x-user-layout>
