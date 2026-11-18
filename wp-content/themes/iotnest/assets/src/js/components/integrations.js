import { createVideoModal } from './video-modal.js';

const granted = purpose => window.iotnestConsent?.has?.(purpose) === true;
/** IDs são validados também no browser; flags e disponibilidade já vêm filtradas do PHP. */
export function initializeIntegrations(root) {
  const config=window.iotnestIntegrations||{}, modal=root.querySelector('[data-video-provider]')?createVideoModal():null;
  let activated=false;
  const activate=()=>{
    if(activated || !granted('analytics')) return;
    const gtm=/^GTM-[A-Z0-9]+$/.test(config.gtm||'')?config.gtm:null;
    const ga4=!gtm && /^G-[A-Z0-9]+$/.test(config.ga4||'')?config.ga4:null;
    if(!gtm && !ga4) return;
    activated=true;window.dataLayer=window.dataLayer||[];
    const script=document.createElement('script');script.async=true;script.dataset.iotnestProvider=gtm?'gtm':'ga4';
    if(gtm) {window.dataLayer.push({'gtm.start':Date.now(),event:'gtm.js'});script.src=`https://www.googletagmanager.com/gtm.js?id=${gtm}`;}
    else {window.gtag=window.gtag||function(){window.dataLayer.push(arguments);};window.gtag('js',new Date());window.gtag('config',ga4);script.src=`https://www.googletagmanager.com/gtag/js?id=${ga4}`;}
    document.head.append(script);
  };
  root.querySelectorAll('[data-video-provider]').forEach(button=>{
    button.addEventListener('click',event=>{
      let media;
      if(button.dataset.videoProvider==='youtube') {
        if(!config.youtube?.available || !/^[\w-]{11}$/.test(button.dataset.videoId||'')) {event.preventDefault();return;}
        if(config.youtube.consent && !granted('youtube')) {event.preventDefault();window.iotnestConsent?.request?.('youtube');return;}
        if(!modal) return;
        media=document.createElement('iframe');const start=Math.max(0,Number.parseInt(button.dataset.videoStart,10)||0);
        media.src=`https://www.youtube-nocookie.com/embed/${button.dataset.videoId}?autoplay=1&rel=0${start?`&start=${start}`:''}`;
        media.title=button.dataset.videoTitle||'Vídeo';media.allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen';media.allowFullscreen=true;
      } else if(button.dataset.videoProvider==='local') {
        if(!modal) return;
        const url=new URL(button.href,document.baseURI);if(url.origin!==location.origin) return;
        media=document.createElement('video');media.src=url.href;media.controls=true;media.autoplay=true;media.playsInline=true;
      }
      if(!media) return;event.preventDefault();modal.open(button,media);
    });
  });
  window.addEventListener('iotnest:consent',()=>{
    if(config.youtube?.consent && !granted('youtube')) modal?.close();
    if(activated && !granted('analytics')) {
      if(config.ga4) window[`ga-disable-${config.ga4}`]=true;
      document.querySelectorAll('[data-iotnest-provider]').forEach(script=>script.remove());
      // Tags já executadas pelo GTM não podem ser descarregadas de forma confiável.
      // A CMP persiste a revogação antes do evento; recarregar elimina seus timers e estado.
      location.reload();return;
    }
    activate();
  });
  activate();
}
