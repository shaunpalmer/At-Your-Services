(function () {
  const nav = document.querySelector('[data-ays-tabs]');
  if (!nav) return;
  const tabs = Array.from(nav.querySelectorAll('[data-ays-tab]'));
  const panels = Array.from(document.querySelectorAll('[data-ays-panel]'));
  function activate() {
    const hash = window.location.hash || '#overview';
    tabs.forEach(tab => tab.classList.toggle('is-active', tab.getAttribute('href') === hash));
    panels.forEach(panel => panel.classList.toggle('is-active', '#' + panel.id === hash));
  }
  tabs.forEach(tab => tab.addEventListener('click', function (e) {
    window.location.hash = this.getAttribute('href');
  }));
  window.addEventListener('hashchange', () => activate());
  activate();
})();
