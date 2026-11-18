/** A mesma validação do servidor, com token renovado antes de cada operação nova. */
export function initializeContact(form) {
  const button=form.querySelector('[type="submit"]'),status=form.querySelector('[data-contact-status]');
  if(!button || !status || !window.fetch) return;
  let busy=false, token=null, messages;
  try {messages=JSON.parse(form.querySelector('[data-contact-messages]').textContent);} catch {return;}
  const announce=code=>{status.textContent=messages[code]||messages.failed;status.dataset.code=code;};
  form.addEventListener('submit',async event=>{
    event.preventDefault();if(busy) return;busy=true;button.disabled=true;form.setAttribute('aria-busy','true');
    form.querySelectorAll('[aria-invalid]').forEach(input=>input.removeAttribute('aria-invalid'));announce('sending');
    const controller=new AbortController(),timeout=setTimeout(()=>controller.abort(),20000);
    try {
      if(!token) {
        const response=await fetch(form.dataset.tokenUrl,{cache:'no-store',credentials:'same-origin',signal:controller.signal});
        if(!response.ok) {announce('unavailable');return;}
        token=await response.json();
      }
      const data=new URLSearchParams(new FormData(form));data.set('request_id',token.request_id);data.set('nonce',token.nonce);
      const response=await fetch(form.dataset.endpoint,{method:'POST',body:data,cache:'no-store',credentials:'same-origin',signal:controller.signal});
      const result=await response.json();announce(result.code);
      for(const name of result.fields||[]) if(form.elements[name]) form.elements[name].setAttribute('aria-invalid','true');
      if(result.fields?.length) form.elements[result.fields[0]]?.focus();else status.focus({preventScroll:true});
      // Incerto mantém o mesmo token para consultar o resultado sem repetir a entrega.
      if(result.code!=='uncertain') token=null;
      if(result.code==='accepted') {form.reset();button.disabled=true;form.dataset.sent='true';}
    } catch {announce('uncertain');status.focus({preventScroll:true});}
    finally {clearTimeout(timeout);busy=false;if(form.dataset.sent!=='true') button.disabled=false;form.removeAttribute('aria-busy');}
  });
}
