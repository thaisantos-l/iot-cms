import { tabKeyboard, selectTab } from '../lib/autoplay.js';

export function initializeSteps(root) {
  const panels = [...root.querySelectorAll('[data-step-item]')], tabs = [...root.querySelectorAll('.how__tabs .pill')];
  if (!panels.length) return;
  let current = 0;
  const show = index => {
    current = Math.max(0, Math.min(panels.length - 1, index));
    root.dataset.step = String(current + 1); selectTab(tabs, current);
    panels.forEach((panel, i) => {
      panel.hidden = i !== current; panel.inert = i !== current;
      const previous = panel.querySelector('[data-how-prev]'), next = panel.querySelector('[data-how-next]');
      if (previous) previous.disabled = current === 0;
      if (next) next.disabled = current === panels.length - 1;
    });
  };
  tabKeyboard(tabs, show);
  tabs.forEach((tab, index) => tab.addEventListener('click', () => show(index)));
  panels.forEach(panel => {
    panel.querySelector('[data-how-prev]')?.addEventListener('click', () => { show(current - 1); panels[current].querySelector('[data-how-prev]:not(:disabled),[data-how-next]:not(:disabled)')?.focus({preventScroll:true}); });
    panel.querySelector('[data-how-next]')?.addEventListener('click', () => { show(current + 1); panels[current].querySelector('[data-how-next]:not(:disabled),[data-how-prev]:not(:disabled)')?.focus({preventScroll:true}); });
  });
  show(0);
}
