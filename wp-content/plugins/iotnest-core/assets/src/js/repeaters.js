const directFields = element => [...element.children].filter(child => child.matches('[data-field]'));

export function readField(field) {
  const kind = field.dataset.kind;
  if (kind === 'object') {
    return Object.fromEntries(directFields(field.querySelector(':scope > fieldset')).map(child => [child.dataset.key, readField(child)]));
  }
  if (kind === 'array') {
    const items = field.querySelector(':scope > fieldset > [data-items]');
    return [...items.children].map(item => readField(item.querySelector(':scope > [data-field]')));
  }
  const input = field.querySelector('[data-input]');
  if (kind === 'boolean') return input.checked;
  if (['integer', 'number', 'image', 'video', 'page'].includes(kind)) return input.value === '' ? null : Number(input.value);
  return input.value;
}

export function syncPayload(root) {
  const comparison = root.querySelector('[data-meta="_iotnest_home_comparison"]');
  if (comparison) {
    const columns = readField(comparison.querySelector('[data-key="columns"]'));
    for (const field of comparison.querySelectorAll('[data-kind="idref"]')) {
      const input = field.querySelector('[data-input]');
      const current = input.value;
      const choices = [new Option('Selecionar coluna', '')];
      for (const column of columns) choices.push(new Option(column.label || 'Coluna sem nome', column.id));
      if (current && !columns.some(column => column.id === current)) choices.push(new Option('Coluna removida', current));
      input.replaceChildren(...choices);
      input.value = current;
    }
  }
  const value = root.hasAttribute('data-settings')
    ? readField(root.querySelector(':scope > [data-field]'))
    : Object.fromEntries([...root.querySelectorAll('[data-meta]')].map(box => [box.dataset.meta, readField(box.querySelector(':scope > [data-field]'))]));
  root.querySelector('[data-payload]').value = JSON.stringify(value);
}

function newId() {
  return window.crypto?.randomUUID?.() || `item-${Date.now()}-${Math.random().toString(36).slice(2)}`;
}

export function initializeRepeaters(root) {
  root.addEventListener('click', event => {
    const button = event.target.closest('button');
    if (!button) return;
    const repeater = button.closest('[data-repeater]');
    if (!repeater) return;
    const items = repeater.querySelector(':scope > [data-items]');
    const item = button.closest('[data-item]');
    const status = root.querySelector('[data-status]');
    if (button.hasAttribute('data-add')) {
      if (items.children.length >= Number(repeater.dataset.limit)) {
        status.textContent = `Limite de ${repeater.dataset.limit} itens atingido.`;
        return;
      }
      const fragment = repeater.querySelector(':scope > [data-template]').content.cloneNode(true);
      // Templates clonados precisam de labels/IDs próprios, inclusive nos templates aninhados.
      const unique = tree => {
        for (const input of tree.querySelectorAll('[data-input]')) {
          const previous = input.id;
          input.id = newId();
          for (const label of tree.querySelectorAll('label')) if (label.htmlFor === previous) label.htmlFor = input.id;
        }
        for (const template of tree.querySelectorAll('template')) unique(template.content);
      };
      unique(fragment);
      for (const id of fragment.querySelectorAll('[data-kind="id"] > input')) id.value = newId();
      items.append(fragment);
      items.lastElementChild.querySelector('input:not([readonly]):not([type="hidden"]),textarea,select,button')?.focus();
      status.textContent = 'Item adicionado.';
    } else if (button.hasAttribute('data-remove') && item) {
      const next = item.nextElementSibling || item.previousElementSibling;
      item.remove();
      (next?.querySelector('button') || repeater.querySelector(':scope > [data-add]')).focus();
      status.textContent = 'Item removido. Salve para confirmar.';
    } else if (button.hasAttribute('data-up') && item?.previousElementSibling) {
      items.insertBefore(item, item.previousElementSibling);
      button.focus();
      status.textContent = 'Item movido para cima.';
    } else if (button.hasAttribute('data-down') && item?.nextElementSibling) {
      items.insertBefore(item.nextElementSibling, item);
      button.focus();
      status.textContent = 'Item movido para baixo.';
    }
    syncPayload(root);
  });
}
