import { reducedMotion } from './scroll.js';

/** Pausa explícita, foco, hover e ciclo da página, sem multiplicar timers. */
export function autoplay(root, tick, interval, count, viewport = true) {
  if (count < 2) return { reset() {}, stop() {} };
  let timer = null, visible = !viewport, hovered = false, focused = false, paused = false;
  const stop = () => { clearInterval(timer); timer = null; };
  const reset = () => {
    stop();
    if (!window.iotnestConfig?.autoplay || reducedMotion() || !visible || hovered || focused || paused || document.hidden) return;
    timer = setInterval(tick, interval);
  };
  const control = document.createElement('button');
  control.type = 'button'; control.className = 'screen-reader-text motion-toggle';
  control.textContent = 'Pausar alternância automática'; control.setAttribute('aria-pressed', 'false');
  control.addEventListener('click', () => {
    paused = !paused;
    control.setAttribute('aria-pressed', String(paused));
    control.textContent = paused ? 'Retomar alternância automática' : 'Pausar alternância automática';
    reset();
  });
  if (window.iotnestConfig?.autoplay && !reducedMotion()) root.append(control);
  root.addEventListener('mouseenter', () => { hovered = true; reset(); });
  root.addEventListener('mouseleave', () => { hovered = false; reset(); });
  root.addEventListener('focusin', () => { focused = true; reset(); });
  root.addEventListener('focusout', event => { focused = root.contains(event.relatedTarget); reset(); });
  document.addEventListener('visibilitychange', reset);
  window.addEventListener('pagehide', stop);
  window.addEventListener('pageshow', reset);
  if (viewport && 'IntersectionObserver' in window) {
    const observer = new IntersectionObserver(([entry]) => { visible = entry.isIntersecting; reset(); });
    observer.observe(root);
  } else { visible = true; reset(); }
  return { reset, stop };
}

/** Roving tabindex e setas sem alterar a ordem editorial. */
export function tabKeyboard(tabs, select) {
  tabs.forEach((tab, index) => {
    tab.disabled = false;
    tab.addEventListener('keydown', event => {
      let next;
      if (event.key === 'ArrowRight' || event.key === 'ArrowDown') next = (index + 1) % tabs.length;
      if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') next = (index - 1 + tabs.length) % tabs.length;
      if (event.key === 'Home') next = 0;
      if (event.key === 'End') next = tabs.length - 1;
      if (next === undefined) return;
      event.preventDefault(); select(next); tabs[next].focus();
    });
  });
}
export function selectTab(tabs, current) {
  tabs.forEach((tab, index) => { tab.setAttribute('aria-selected', String(index === current)); tab.tabIndex = index === current ? 0 : -1; });
}
