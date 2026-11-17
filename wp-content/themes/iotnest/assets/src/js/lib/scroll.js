import '../vendor/lenis.js';

const key = Symbol.for('iotnest.scroll');
const state = window[key] ||= { initialized: false, lenis: null, navUntil: 0 };
export const reducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;
export const scrollState = () => state;

export function scrollToElement(element, offset = -80) {
  state.navUntil = performance.now() + 2200;
  if (state.lenis) state.lenis.scrollTo(element, { offset, duration: 1.6 });
  else element.scrollIntoView({ behavior: reducedMotion() ? 'auto' : 'smooth', block: 'start' });
}

export function initializeScroll() {
  if (state.initialized) return;
  state.initialized = true;
  if (!reducedMotion() && window.iotnestConfig?.lenis && window.Lenis) {
    state.lenis = new window.Lenis({
      duration: 1.5,
      easing: t => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
      wheelMultiplier: 0.85,
      touchMultiplier: 1.4,
    });
    const raf = time => { state.lenis?.raf(time); requestAnimationFrame(raf); };
    requestAnimationFrame(raf);
  }
  document.addEventListener('click', event => {
    const anchor = event.target.closest('a[href]');
    if (!anchor || event.defaultPrevented || event.button || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || anchor.target === '_blank') return;
    const url = new URL(anchor.href, document.baseURI);
    if (url.origin !== location.origin || url.pathname !== location.pathname || url.search !== location.search || !url.hash || url.hash === '#') return;
    let id;
    try { id = decodeURIComponent(url.hash.slice(1)); } catch { return; }
    const target = document.getElementById(id);
    if (!target) return;
    event.preventDefault();
    scrollToElement(target);
    history.pushState(null, '', url.hash);
    if (anchor.classList.contains('skip-link')) target.focus({ preventScroll: true });
  });
}
