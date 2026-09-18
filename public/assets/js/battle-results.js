(() => {

  document.querySelectorAll('[data-link]').forEach((button) => button.addEventListener('click', () => { window.location.href = button.dataset.link; }));

  const toast = (message) => {
    let notice = document.getElementById('battleNotice');
    if (!notice) { notice = document.createElement('div'); notice.id = 'battleNotice'; notice.className = 'battle-notice'; document.body.append(notice); }
    notice.textContent = message; notice.classList.add('is-visible'); clearTimeout(notice.hideTimer);
    notice.hideTimer = setTimeout(() => notice.classList.remove('is-visible'), 2400);
  };
  document.getElementById('exportPgn')?.addEventListener('click', () => {
    const pgn = `[Event "MedGambit Ranked Duel"]\n[Round "${document.querySelector('.eyebrow')?.textContent.match(/MATCH #(\d+)/)?.[1] ?? ''}"]\n[Result "1-0"]`;
    const link = document.createElement('a'); link.href = URL.createObjectURL(new Blob([pgn], { type: 'text/plain' }));
    link.download = 'medgambit-match-8102.pgn'; link.click(); URL.revokeObjectURL(link.href); toast('PGN exported');
  });
  document.getElementById('shareMatch')?.addEventListener('click', async () => {
    try {
      if (navigator.share) await navigator.share({ title: 'MedGambit battle results', text: 'Dr. Yamen won match #8102 — 16/20', url: window.location.href });
      else if (navigator.clipboard) await navigator.clipboard.writeText(window.location.href);
      else throw new Error('Clipboard unavailable');
      toast(navigator.share ? 'Match shared' : 'Match link copied');
    } catch (error) { if (error.name !== 'AbortError') toast('Unable to share this match'); }
  });
  const scrollToTelemetry = () => document.getElementById('decisionMatrix')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  document.getElementById('cleanMatrix')?.addEventListener('click', scrollToTelemetry);
  document.getElementById('fullTelemetry')?.addEventListener('click', () => { scrollToTelemetry(); toast('Showing full match telemetry'); });
  document.getElementById('reviewMistakes')?.addEventListener('click', () => {
    document.querySelector('#reviewFilters [data-filter="missed"]')?.click();
    document.getElementById('review-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });

  const filters = document.querySelectorAll('#reviewFilters [data-filter]');
  const reviews = document.querySelectorAll('.review-card');
  filters.forEach((filter) => filter.addEventListener('click', () => {
    filters.forEach((item) => { item.classList.remove('active', 'btn-primary'); item.classList.add('btn-outline-secondary'); });
    filter.classList.add('active', 'btn-primary'); filter.classList.remove('btn-outline-secondary');
    reviews.forEach((review) => { review.hidden = filter.dataset.filter !== 'all' && review.dataset.status !== filter.dataset.filter; });
  }));
  document.querySelectorAll('.matrix-cell').forEach((cell) => {
    cell.setAttribute('tabindex', '0');
    cell.addEventListener('focus', () => cell.classList.add('is-focused'));
    cell.addEventListener('blur', () => cell.classList.remove('is-focused'));
  });
})();
