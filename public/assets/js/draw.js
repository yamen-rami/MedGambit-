(() => {
  const stage = document.querySelector('.draw-stage');
  if (!stage) return;

  const restart = () => {
    // Replacing the animated group resets the CSS animation timeline reliably.
    const animatedGroup = stage.querySelector('.draw-stage-master');
    if (animatedGroup) animatedGroup.replaceWith(animatedGroup.cloneNode(true));
  };

  window.DrawLoader = {
    show: () => { stage.removeAttribute('hidden'); restart(); },
    hide: () => stage.setAttribute('hidden', ''),
  };
})();
