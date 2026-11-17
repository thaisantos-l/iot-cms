export function initializeNavigation(header) {
  const toggle = header.querySelector('.nav-toggle');
  const menu = header.querySelector('#menu');
  if (!toggle || !menu) return;
  const mobile = window.matchMedia('(max-width: 1200px)');
  const dropdowns = [...menu.querySelectorAll('.nav__dropdown-btn')];
  const setDropdown = (button, open) => {
    button.setAttribute('aria-expanded', String(open));
    const panel = document.getElementById(button.getAttribute('aria-controls'));
    if (panel) panel.inert = !open;
  };
  const setMenu = (open, restore = false) => {
    menu.classList.toggle('is-open', open);
    toggle.setAttribute('aria-expanded', String(open));
    toggle.setAttribute('aria-label', open ? 'Fechar menu' : 'Abrir menu');
    menu.inert = mobile.matches && !open;
    if (!open) dropdowns.forEach(button => setDropdown(button, false));
    if (restore) toggle.focus();
  };
  toggle.addEventListener('click', () => setMenu(toggle.getAttribute('aria-expanded') !== 'true'));
  for (const button of dropdowns) {
    const group = button.parentElement;
    setDropdown(button, false);
    button.addEventListener('click', () => setDropdown(button, button.getAttribute('aria-expanded') !== 'true'));
    group.addEventListener('pointerenter', event => {
      if (!mobile.matches && event.pointerType !== 'touch') setDropdown(button, true);
    });
    group.addEventListener('pointerleave', () => {
      if (!mobile.matches && !group.contains(document.activeElement)) setDropdown(button, false);
    });
    group.addEventListener('focusout', event => {
      if (!group.contains(event.relatedTarget)) setDropdown(button, false);
    });
    button.addEventListener('keydown', event => {
      if (!['ArrowDown', 'ArrowUp'].includes(event.key)) return;
      event.preventDefault();
      setDropdown(button, true);
      const links = group.querySelectorAll('.nav__panel a');
      (event.key === 'ArrowDown' ? links[0] : links[links.length - 1])?.focus();
    });
  }
  menu.addEventListener('click', event => {
    if (event.target.closest('a')) setMenu(false);
  });
  document.addEventListener('keydown', event => {
    if (event.key !== 'Escape') return;
    const opened = dropdowns.find(button => button.getAttribute('aria-expanded') === 'true');
    if (opened) {
      setDropdown(opened, false);
      opened.focus();
    } else if (toggle.getAttribute('aria-expanded') === 'true') setMenu(false, mobile.matches);
  });
  document.addEventListener('pointerdown', event => {
    if (!header.contains(event.target)) setMenu(false);
  });
  mobile.addEventListener('change', () => setMenu(false));
  setMenu(false);
}
