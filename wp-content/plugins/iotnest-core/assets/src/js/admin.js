import { initializeRepeaters, readField, syncPayload } from './repeaters.js';
import { initializeMedia } from './media.js';

for (const root of document.querySelectorAll('[data-iotnest-admin]')) {
  initializeRepeaters(root);
  initializeMedia(root);
  const sync = () => syncPayload(root);
  root.addEventListener('input', sync);
  root.addEventListener('change', sync);
  root.closest('form')?.addEventListener('submit', event => {
    sync();
    const payload = root.querySelector('[data-payload]');
    if (new TextEncoder().encode(payload.value).length > 1048576) {
      event.preventDefault();
      root.querySelector('[data-status]').textContent = 'O formulário excede 1 MiB. Reduza o conteúdo antes de salvar; os dados anteriores serão preservados.';
    }
  });
  sync();
  // Campos próprios também geram revisão quando título/conteúdo nativos não mudaram.
  if (window.jQuery && !root.hasAttribute('data-settings')) {
    let acknowledged = root.querySelector('[data-payload]').value;
    let sent = '';
    window.jQuery(document).on('heartbeat-send.iotnest', (_event, data) => {
      sync();
      const payload = root.querySelector('[data-payload]').value;
      if (payload === acknowledged || new TextEncoder().encode(payload).length > 1048576) return;
      sent = payload;
      data.iotnest_fields = {
        post_id: root.closest('form').querySelector('[name="post_ID"]').value,
        _iotnest_payload: payload,
        _iotnest_complete: '1',
        _iotnest_nonce: root.closest('form').querySelector('[name="_iotnest_nonce"]').value,
      };
    }).on('heartbeat-tick.iotnest', (_event, data) => {
      if (!data.iotnest_fields?.success) return;
      acknowledged = sent;
      root.querySelector('[data-status]').textContent = 'Revisão automática dos campos salva. Atualize a página para publicar.';
    });
  }
}

export { readField };
