(() => {
  const stage = document.querySelector('.flatline-stage');
  let flatline = document.querySelector('.flatline');

  if (!stage || !flatline) return;

  // Expose a small restart helper for pages that need to replay the animation.
  window.restartFlatline = () => {
    const freshFlatline = flatline.cloneNode(true);
    flatline.replaceWith(freshFlatline);
    flatline = freshFlatline;
    return freshFlatline;
  };

  window.FlatlineLoader = {
    show: () => {
      stage.removeAttribute('hidden');
      window.restartFlatline();
    },
    hide: () => stage.setAttribute('hidden', ''),
  };
})();
