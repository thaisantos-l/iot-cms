import { autoplay, tabKeyboard, selectTab } from '../lib/autoplay.js';
import { reducedMotion } from '../lib/scroll.js';

export function initializeAI(root) {
  const images = [...root.querySelectorAll('.ai-tabs__stage img')], tabs = [...root.querySelectorAll('.ai-tabs__item')];
  if (!images.length || images.length !== tabs.length) return;
  let current = 0;
  const show = index => {
    current = index; selectTab(tabs, index);
    images.forEach((image, k) => { image.classList.toggle('is-on', k === index); image.setAttribute('aria-hidden', String(k !== index)); });
  };
  const timer = autoplay(root, () => show((current + 1) % images.length), 4000, images.length);
  if (images.length > 1) {
    tabKeyboard(tabs, show);
    tabs.forEach((tab, index) => { tab.addEventListener('click', () => { show(index); timer.reset(); }); tab.addEventListener('focus', () => show(index)); });
  }
  show(0);
}

export function initializeMES(root) {
  const tabs = [...root.querySelectorAll('.mess__tab')], panels = [...root.querySelectorAll('[data-mess-panel]')];
  if (!panels.length) return;
  let current = 0, front = 0, pending;
  const layers = [...root.querySelectorAll('.mess__bg')];
  const copy = root.querySelector('.mess__copy');
  if (!copy) return;
  // Grid já reserva o maior conteúdo. Em engines sem calc-size, ajusta apenas
  // o arredondamento de menos de 1px que a referência aplica com Math.ceil.
  if (!CSS.supports('height', 'calc-size(auto, round(up, size, 1px))')) {
    const roundHeight = () => {
      if (!copy.isConnected || !copy.offsetWidth) return;
      const height = Math.max(...panels.map(panel => {
        const children = [...panel.children];
        return children.reduce((sum, child) => sum + child.getBoundingClientRect().height, 0)
          + parseFloat(getComputedStyle(panel).rowGap) * Math.max(0, children.length - 1);
      }));
      copy.style.minHeight = `${Math.ceil(height)}px`;
    };
    roundHeight();
    if ('ResizeObserver' in window) {
      const observer = new ResizeObserver(roundHeight);
      panels.forEach(panel => [...panel.children].forEach(child => observer.observe(child)));
    } else window.addEventListener('resize', roundHeight);
    document.fonts?.ready.then(roundHeight);
  }
  const show = (index, instant = false) => {
    if (!panels[index]) return;
    current = index; selectTab(tabs, index);
    const url = panels[index].dataset.image;
    if (layers.length === 2) {
      const next = layers[1 - front];
      next.style.backgroundImage = url ? `url(${JSON.stringify(new URL(url, document.baseURI).href)})` : 'none';
      next.classList.add('is-active'); layers[front].classList.remove('is-active'); front = 1 - front;
    }
    const switchCopy = () => {
      panels.forEach((panel, i) => { panel.classList.toggle('is-active', i === index); panel.inert = i !== index; panel.setAttribute('aria-hidden', String(i !== index)); });
      copy.classList.remove('is-swapping');
    };
    clearTimeout(pending);
    if (instant || reducedMotion()) switchCopy();
    else { copy.classList.add('is-swapping'); pending = setTimeout(switchCopy, 260); }
  };
  // A referência mantém MES independente do scroll; preservado, com pausa de foco/aba.
  const timer = autoplay(root, () => show((current + 1) % panels.length), 2000, panels.length, false);
  tabKeyboard(tabs, show);
  tabs.forEach((tab, index) => tab.addEventListener('click', () => { show(index); timer.reset(); }));
  let preloaded = false;
  const preload = () => { if (preloaded) return; preloaded = true; panels.forEach(panel => { if (panel.dataset.image) { const image = new Image(); image.src = panel.dataset.image; } }); };
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(([entry]) => { if (entry.isIntersecting) { preload(); observer.disconnect(); } }, {rootMargin:'900px 0px'}); observer.observe(root);
  } else preload();
  show(0, true);
  window.addEventListener('pagehide', () => clearTimeout(pending));
}
