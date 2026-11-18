import { reducedMotion, scrollState } from '../lib/scroll.js';

const enabled = () => window.iotnestConfig?.motion && !reducedMotion();
const clamp = (value, min, max) => Math.max(min, Math.min(max, value));
function onScroll(update) {
  let queued = false;
  const run = () => { if (!queued) { queued = true; requestAnimationFrame(() => { queued = false; update(); }); } };
  window.addEventListener('scroll', run, {passive:true}); window.addEventListener('resize',run); update();
}
export function initializeReveal(root) {
  if (!enabled() || !('IntersectionObserver' in window)) return;
  document.documentElement.classList.add('js-motion');
  const selectors = '.hero__content > *, .clients, .section-head, .split > *, .ai-tabs, .mess, .report-lists, .calc__intro, .calc__grid > .panel, .faq, .faq__cta, .listing, .article__cover, .cta-band, .compare, .legend, .compare__foot, .cases-head, .cases, .how__tabs, .how__panel > *, .videos > .video, .quote, .contact__side > *, .contact__grid > .panel, .footer__inner > *, .footer__bottom';
  const observer = new IntersectionObserver(entries => entries.forEach(entry => { if (entry.isIntersecting) { entry.target.classList.add('is-in'); observer.unobserve(entry.target); } }), {threshold:0.12,rootMargin:'0px 0px -6% 0px'});
  root.querySelectorAll(selectors).forEach(element => {
    if (element.closest('.reveal')) return;
    const siblings = [...element.parentElement.children].filter(node => node.matches(selectors));
    element.style.setProperty('--d',`${Math.min(siblings.indexOf(element),4)*90}ms`);
    element.classList.add('reveal'); observer.observe(element);
  });
}
export function initializeEnergy(root) {
  if (!enabled()) return;
  onScroll(() => { const rect = root.getBoundingClientRect(), vh = innerHeight; root.style.setProperty('--p',clamp(((rect.top+rect.height/2)-vh/2)/(vh/2+rect.height/2),-1,1).toFixed(3)); });
}
export function initializeHeroZoom(root) {
  if (enabled()) onScroll(() => root.style.setProperty('--zoom',(1+clamp(scrollY/root.offsetHeight,0,1)*0.14).toFixed(4)));
}
export function initializeDadaScroll(root) {
  if (!enabled()) return;
  const veil = root.querySelector('.dada-full__veil'), desktop = matchMedia('(min-width:900px) and (hover:hover)');
  let lastY = scrollY, snapped = false, holdAt = 0, holdTimer;
  const release = () => { clearTimeout(holdTimer); holdTimer = null; scrollState().lenis?.start(); };
  onScroll(() => {
    const r = root.getBoundingClientRect(), vh = innerHeight, down = scrollY > lastY; lastY = scrollY;
    if (veil) veil.style.opacity = String(clamp((r.top-vh*0.15)/(vh*0.6),0,1));
    const {lenis,navUntil} = scrollState();
    if (!window.iotnestConfig?.dada_scroll || !lenis || !desktop.matches) return;
    if (r.bottom<=0 || r.top>=vh) { snapped=false; return; }
    if (snapped || performance.now()<navUntil || holdTimer) return;
    if (!(down && r.top<vh*0.6 && r.top>vh*0.12) && !(!down && r.bottom>vh*0.4 && r.bottom<vh*0.88)) return;
    snapped=true;
    lenis.scrollTo(root,{offset:-Math.max(0,(vh-r.height)/2),duration:1.3,lock:true,easing:t=>t<0.5?4*t*t*t:1-Math.pow(-2*t+2,3)/2,onComplete:()=>{lenis.stop();holdAt=performance.now();holdTimer=setTimeout(release,1500);}});
  });
  ['wheel','touchmove','keydown'].forEach(type=>window.addEventListener(type,()=>{if(holdTimer && performance.now()-holdAt>500) release();},{passive:true}));
  window.addEventListener('pagehide',release);
}
export function initializeCounter(root) {
  if (!enabled() || !window.iotnestConfig?.counters || !('IntersectionObserver' in window)) return;
  const end=Number(root.dataset.count), value=root.querySelector('[data-count-value]');
  if (!Number.isFinite(end) || !value) return;
  root.classList.add('is-counting'); value.textContent='0';
  const observer=new IntersectionObserver(([entry])=>{
    if(!entry.isIntersecting) return; observer.disconnect();
    const start=performance.now();
    const tick=now=>{const t=Math.min(1,(now-start)/1600);value.textContent=String(Math.round(end*(1-Math.pow(1-t,3))));if(t<1 && !document.hidden) requestAnimationFrame(tick);else {value.textContent=String(end);root.classList.remove('is-counting');}};requestAnimationFrame(tick);
  },{threshold:0.6});observer.observe(root);
}
export function initializeMarquee(root) {
  const track=root.querySelector('.marquee__track'); if(!track) return;
  if(!enabled() || !window.iotnestConfig?.autoplay || root.dataset.animate!=='true') {track.style.animation='none';return;}
  let paused=false;
  const button=document.createElement('button');button.type='button';button.className='screen-reader-text motion-toggle';button.textContent='Pausar movimento';button.setAttribute('aria-pressed','false');root.append(button);
  const sync=()=>{track.style.animationPlayState=paused || document.hidden || root.matches(':hover,:focus-within')?'paused':'running';};
  button.addEventListener('click',()=>{paused=!paused;button.setAttribute('aria-pressed',String(paused));button.textContent=paused?'Retomar movimento':'Pausar movimento';sync();});
  ['mouseenter','mouseleave','focusin','focusout'].forEach(type=>root.addEventListener(type,()=>queueMicrotask(sync)));document.addEventListener('visibilitychange',sync);
}
