(() => {
  const sidebar = document.getElementById('questionSidebar');
  const backdrop = document.getElementById('questionBackdrop');
  const closeSidebar = () => {
    sidebar?.classList.remove('open');
    backdrop?.classList.remove('open');
  };

  document.getElementById('sidebarTrigger')?.addEventListener('click', () => {
    sidebar?.classList.add('open');
    backdrop?.classList.add('open');
  });
  document.getElementById('sidebarClose')?.addEventListener('click', closeSidebar);
  backdrop?.addEventListener('click', closeSidebar);
})();
