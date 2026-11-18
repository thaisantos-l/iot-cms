import { scrollState } from '../lib/scroll.js';
/** Modal compartilhado: o adapter fornece apenas um elemento já autorizado. */
export function createVideoModal() {
  const modal=document.createElement('dialog');if(typeof modal.showModal!=='function') return null;
  modal.className='video-modal';modal.setAttribute('aria-labelledby','video-modal-title');
  modal.innerHTML='<div class="video-modal__bar"><p class="video-modal__title" id="video-modal-title"></p><button type="button" class="video-modal__close" aria-label="Fechar vídeo"><svg width="18" height="18" viewBox="0 0 18 18" fill="none" aria-hidden="true"><path d="M4 4l10 10M14 4 4 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button></div><div class="video-modal__frame"></div>';
  const frame=modal.querySelector('.video-modal__frame'),title=modal.querySelector('.video-modal__title'),close=modal.querySelector('button');let trigger;
  document.body.append(modal);
  close.addEventListener('click',()=>modal.close());modal.addEventListener('click',event=>{if(event.target===modal) modal.close();});
  modal.addEventListener('close',()=>{frame.replaceChildren();scrollState().lenis?.start();trigger?.focus({preventScroll:true});});
  return {open(element,media){trigger=element;title.textContent=element.dataset.videoTitle||element.getAttribute('aria-label')||'Vídeo';frame.replaceChildren(media);scrollState().lenis?.stop();modal.showModal();close.focus();},close(){if(modal.open) modal.close();}};
}
