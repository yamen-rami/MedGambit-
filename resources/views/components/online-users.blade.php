@props(['initialCount' => 0, 'id' => 'online-users-count'])

<span id="{{ $id }}">{{ number_format($initialCount) }}</span>

@push('scripts')
    <script>
        (() => {
            const start = () => {
                const countElement = document.getElementById(@js($id));

                if (!countElement || countElement.dataset.subscribed === 'true' || !window.Echo) {
                    return;
                }

                countElement.dataset.subscribed = 'true';

                window.Echo.join('online.users')
                    .here((users) => {
                        countElement.textContent = users.length;
                    })
                    .joining(() => {
                        countElement.textContent = Number(countElement.textContent.replace(/,/g, '')) + 1;
                    })
                    .leaving(() => {
                        countElement.textContent = Math.max(
                            0,
                            Number(countElement.textContent.replace(/,/g, '')) - 1
                        );
                    });
            };

            if (window.Echo) {
                start();
            } else {
                window.addEventListener('echo:ready', start, { once: true });
            }
        })();
    </script>
@endpush
