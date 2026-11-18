/** Adapta somente a análise editorial. Não imprime tags nem altera o HTML público. */
export function initializeYoast(root) {
  if(root.hasAttribute('data-settings')) return;
  let registered=false,timer;
  const content=()=>{
    const fragments=[];
    for(const box of root.querySelectorAll('[data-meta]')) {
      if(box.dataset.meta.startsWith('_iotnest_home_')) {
        const enabled=box.querySelector(':scope > [data-field] > fieldset > [data-key="enabled"] [data-input]');
        const title=box.querySelector(':scope > [data-field] > fieldset > [data-key="title"] [data-input],:scope > [data-field] > fieldset > [data-key="label"] [data-input]');
        if(!enabled?.checked || !title?.value.trim()) continue;
      }
      for(const field of box.querySelectorAll('[data-kind="text"], [data-kind="rich"], [data-kind="heading"]')) {
        if(['key','id','icon'].includes(field.dataset.key)) continue;
        const value=field.querySelector('[data-input]')?.value.trim();if(value) fragments.push(value);
      }
    }
    return fragments.join('\n');
  };
  const register=()=>{
    const yoast=window.YoastSEO;if(registered || !yoast?.analysis?.worker || !yoast.app) return;
    registered=true;yoast.app.registerPlugin('iotnest-fields',{status:'ready'});
    yoast.app.registerModification('content',data=>`${data}\n${content()}`,'iotnest-fields',10);
    const update=()=>{clearTimeout(timer);timer=setTimeout(()=>yoast.app.pluginReloaded('iotnest-fields'),250);};
    ['input','change','click'].forEach(type=>root.addEventListener(type,update));
  };
  register();window.jQuery?.(window).on('YoastSEO:ready.iotnest',register);
}
