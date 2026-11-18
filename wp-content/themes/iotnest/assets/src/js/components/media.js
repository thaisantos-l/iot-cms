import { reducedMotion } from '../lib/scroll.js';
const play = video => video.play().catch(() => {});
const observe = (root,callback,threshold) => {if('IntersectionObserver' in window) new IntersectionObserver(([entry])=>callback(entry.isIntersecting),{threshold}).observe(root);else callback(true);};
export function initializeHeroVideo(root) {
  const a=root.querySelector('video.hero__bg'); if(!a || reducedMotion() || !window.iotnestConfig?.autoplay || a.dataset.autoplay==='false') return;
  const b=a.cloneNode(true); [a,b].forEach(video=>{video.loop=false;video.muted=true;});b.preload='metadata';b.style.opacity='0';a.style.zIndex='-2';b.style.zIndex='-3';a.after(b);
  let current=a,next=b,fading=false,visible=true,paused=false,raf=0,pending;
  const swap=()=>{current.pause();current.style.transition='none';current.style.opacity='0';current.style.zIndex='-3';current.currentTime=0;[current,next]=[next,current];fading=false;};
  const fade=()=>{if(fading || paused || !visible || document.hidden) return;fading=true;next.preload='auto';next.currentTime=0;next.style.zIndex='-2';current.style.zIndex='-3';play(next);void next.offsetWidth;next.style.transition='opacity 1.5s ease-in-out';next.style.opacity='1';pending=setTimeout(swap,1560);};
  const tick=()=>{raf=0;if(!visible || paused || document.hidden) return;const duration=current.duration;if(!fading && Number.isFinite(duration) && duration>0){if(duration-current.currentTime<=5.5 && next.preload!=='auto'){next.preload='auto';next.load();}if(duration-current.currentTime<=1.5) fade();}raf=requestAnimationFrame(tick);};
  const sync=()=>{if(!visible || paused || document.hidden){a.pause();b.pause();cancelAnimationFrame(raf);raf=0;}else{play(current);if(fading) play(next);if(!raf) raf=requestAnimationFrame(tick);}};
  const control=document.createElement('button');control.type='button';control.className='screen-reader-text motion-toggle';control.textContent='Pausar vídeo de fundo';control.setAttribute('aria-pressed','false');root.append(control);
  control.addEventListener('click',()=>{paused=!paused;control.textContent=paused?'Retomar vídeo de fundo':'Pausar vídeo de fundo';control.setAttribute('aria-pressed',String(paused));sync();});
  [a,b].forEach(video=>{video.addEventListener('ended',fade);video.addEventListener('error',()=>{paused=true;sync();});});a.addEventListener('canplay',sync,{once:true});document.addEventListener('visibilitychange',sync);observe(root,value=>{visible=value;sync();},0.05);
  window.addEventListener('pagehide',()=>{clearTimeout(pending);cancelAnimationFrame(raf);a.pause();b.pause();});window.addEventListener('pageshow',sync);
}
export function initializeDadaVideo(root) {
  const video=root.querySelector('video'),toggle=root.querySelector('[data-dada-toggle]'),bar=root.querySelector('[data-dada-progress]');if(!video || !toggle || !bar) return;
  video.controls=false;toggle.disabled=false;
  let paused=reducedMotion() || !window.iotnestConfig?.autoplay || video.dataset.autoplay==='false',visible=false;
  const sync=()=>{toggle.classList.toggle('is-paused',video.paused);toggle.setAttribute('aria-label',video.paused?'Reproduzir vídeo':'Pausar vídeo');};
  const visibility=()=>{if(visible && !document.hidden && !paused) play(video);else video.pause();};
  toggle.addEventListener('click',()=>{paused=!video.paused;if(paused) video.pause();else play(video);});['play','pause','error'].forEach(type=>video.addEventListener(type,sync));
  bar.role='slider';bar.tabIndex=0;
  const seek=value=>{if(Number.isFinite(video.duration) && video.duration>0) video.currentTime=Math.max(0,Math.min(video.duration,value));};
  video.addEventListener('timeupdate',()=>{const pct=Number.isFinite(video.duration)&&video.duration>0?video.currentTime/video.duration*100:0;bar.style.setProperty('--prog',pct.toFixed(2));bar.setAttribute('aria-valuenow',String(Math.round(pct)));});
  bar.addEventListener('click',event=>{const r=bar.getBoundingClientRect();seek((event.clientX-r.left)/r.width*video.duration);});
  bar.addEventListener('keydown',event=>{const targets={ArrowLeft:video.currentTime-5,ArrowRight:video.currentTime+5,Home:0,End:video.duration};if(event.key in targets){event.preventDefault();seek(targets[event.key]);}});
  observe(root,value=>{visible=value;visibility();},0.15);document.addEventListener('visibilitychange',visibility);window.addEventListener('pagehide',()=>video.pause());sync();
}
