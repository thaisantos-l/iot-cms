import { autoplay, tabKeyboard, selectTab } from '../lib/autoplay.js';
import { reducedMotion } from '../lib/scroll.js';

export function initializeHardware(root) {
  const slides = [...root.querySelectorAll('.hw-carousel__slide')], tabs = [...root.querySelectorAll('.hw-carousel__dots button')];
  if (!slides.length) return;
  let current = 0;
  const show = index => {
    current = index; selectTab(tabs, index);
    slides.forEach((slide, i) => {
      const position = (i - index + slides.length) % slides.length;
      slide.dataset.pos = String(position === 0 ? 0 : position === 1 ? 1 : position === slides.length - 1 ? 2 : 3);
    });
  };
  if (slides.length > 1) {
    const timer = autoplay(root, () => show((current + 1) % slides.length), 2000, slides.length);
    const go = index => { show(index); timer.reset(); };
    tabKeyboard(tabs, go);
    tabs.forEach((tab, index) => tab.addEventListener('click', () => go(index)));
    slides.forEach((slide, index) => slide.addEventListener('click', () => go(index)));
  }
  show(0);
}

export function initializeCarousel(root) {
  const track = root.querySelector('[data-cases]'), cards = [...root.querySelectorAll('.case')];
  const arrows = root.querySelector('[data-cases-arrows]'), dots = root.querySelector('[data-cases-dots]');
  const previous = root.querySelector('[data-cases-prev]'), next = root.querySelector('[data-cases-next]');
  if (!track || cards.length < 2 || !arrows || !dots || !previous || !next) return;
  const markers = cards.map(() => document.createElement('i')); dots.replaceChildren(...markers);
  const step = () => cards[0].getBoundingClientRect().width + parseFloat(getComputedStyle(track).columnGap || 0);
  const update = () => {
    const overflow = track.scrollWidth - track.clientWidth > 4;
    arrows.hidden = dots.hidden = !overflow;
    const width = step(), index = width > 0 ? Math.round(track.scrollLeft / width) : 0;
    markers.forEach((dot, i) => dot.classList.toggle('is-active', i === index));
    previous.disabled = track.scrollLeft <= 2; next.disabled = track.scrollLeft >= track.scrollWidth - track.clientWidth - 2;
  };
  previous.addEventListener('click', () => track.scrollBy({left:-step(),behavior:reducedMotion()?'instant':'smooth'}));
  next.addEventListener('click', () => track.scrollBy({left:step(),behavior:reducedMotion()?'instant':'smooth'}));
  track.addEventListener('scroll', update, {passive:true});
  if ('ResizeObserver' in window) new ResizeObserver(update).observe(track); else window.addEventListener('resize',update);
  update();
}
