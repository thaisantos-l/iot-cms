export function initializeMedia(root) {
  root.addEventListener('click', event => {
    const button = event.target.closest('[data-media],[data-clear]');
    if (!button) return;
    const field = button.closest('[data-field]');
    const input = field.querySelector('[data-input]');
    const label = field.querySelector('[data-media-label]');
    const update = (value, text) => {
      input.value = value;
      label.textContent = text;
      input.dispatchEvent(new Event('change', { bubbles: true }));
      button.focus();
    };
    if (button.hasAttribute('data-clear')) {
      update('', 'Nenhum arquivo selecionado.');
      return;
    }
    if (!window.wp?.media) return;
    const frame = window.wp.media({ title: 'Selecionar mídia iotnest', multiple: false, library: { type: button.dataset.media } });
    frame.on('select', () => {
      const selected = frame.state().get('selection').first().toJSON();
      update(String(selected.id), selected.title || selected.filename);
    });
    frame.open();
  });
}
