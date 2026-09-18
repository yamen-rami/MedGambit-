/* Usage: <link rel="stylesheet" href="ecg-loader.css"><script src="ecg-loader.js"></script>
   Then place <ecg-loader></ecg-loader> anywhere in the page body. */
(function () {
  const template = `
    <div class="ecg-loader__frame" role="status" aria-label="Loading">
      <svg viewBox="0 0 900 320" preserveAspectRatio="xMidYMid meet" aria-hidden="true">
        <defs>
          <filter id="ecg-pen-glow" x="-100%" y="-100%" width="300%" height="300%">
            <feGaussianBlur result="blur1" stdDeviation="4.5" />
            <feGaussianBlur result="blur2" stdDeviation="1.8" />
            <feMerge><feMergeNode in="blur1" /><feMergeNode in="blur2" /><feMergeNode in="SourceGraphic" /></feMerge>
          </filter>
          <filter id="ecg-trace-glow" x="-20%" y="-20%" width="140%" height="140%">
            <feGaussianBlur result="subtleGlow" stdDeviation="2" />
            <feMerge><feMergeNode in="subtleGlow" /><feMergeNode in="SourceGraphic" /></feMerge>
          </filter>
          <radialGradient id="ecg-pen-bloom" cx="50%" cy="50%" r="50%">
            <stop offset="0%" stop-color="var(--ecg-trace-start)" />
            <stop offset="35%" stop-color="var(--ecg-highlight)" stop-opacity=".8" />
            <stop offset="70%" stop-color="var(--ecg-secondary)" stop-opacity=".25" />
            <stop offset="100%" stop-color="var(--ecg-secondary)" stop-opacity="0" />
          </radialGradient>
          <linearGradient id="ecg-trace-gradient" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="var(--ecg-trace-start)" /><stop offset="33%" stop-color="var(--ecg-trace-start)" />
            <stop offset="40%" stop-color="var(--ecg-highlight)" /><stop offset="46%" stop-color="var(--ecg-secondary)" />
            <stop offset="100%" stop-color="var(--ecg-secondary)" />
          </linearGradient>
        </defs>
        <g class="pulse-drawing-stage">
          <path class="ecg-drawn-path" d="M 60 160 L 320 160 C 334 160, 340 152, 348 152 C 356 152, 360 160, 370 160 L 388 160 L 398 170 L 420 54 L 438 196 L 450 160 L 472 160 C 480 160, 488 146, 498 146 C 508 146, 514 160, 524 160 L 840 160" fill="none" filter="url(#ecg-trace-glow)" stroke="url(#ecg-trace-gradient)" stroke-width="2.5" />
          <g class="pen-head" pointer-events="none" filter="url(#ecg-pen-glow)">
            <circle cx="0" cy="0" r="16" fill="url(#ecg-pen-bloom)" />
            <circle class="pen-tip-glow" cx="0" cy="0" r="4.5" />
            <circle cx="0" cy="0" r="2" fill="var(--ecg-trace-start)" />
          </g>
        </g>
      </svg>
    </div>`;

  function mount() {
    document.querySelectorAll('ecg-loader').forEach((loader) => {
      if (!loader.dataset.mounted) {
        loader.innerHTML = template;
        loader.dataset.mounted = 'true';
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mount, { once: true });
  } else {
    mount();
  }

  window.EcgLoader = {
    show: () => document.querySelectorAll('ecg-loader').forEach((loader) => {
      loader.removeAttribute('hidden');

      // Replace the animated stage so every show starts a fresh CSS animation.
      const stage = loader.querySelector('.pulse-drawing-stage');
      if (stage) stage.replaceWith(stage.cloneNode(true));
    }),
    hide: () => document.querySelectorAll('ecg-loader').forEach((loader) => loader.setAttribute('hidden', ''))
  };
})();
