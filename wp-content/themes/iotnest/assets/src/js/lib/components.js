/** Inicialização idempotente; componentes ausentes não exigem placeholders. */
const initialized = new WeakMap();

export function initializeComponents(root, components) {
  for (const { selector, initialize } of components) {
    for (const element of root.querySelectorAll(selector)) {
      let handlers = initialized.get(element);
      if (!handlers) {
        handlers = new Set();
        initialized.set(element, handlers);
      }
      if (handlers.has(initialize)) continue;
      initialize(element);
      handlers.add(initialize);
    }
  }
}

export function onReady(callback, documentRoot = document) {
  if (documentRoot.readyState === 'loading') {
    documentRoot.addEventListener('DOMContentLoaded', callback, { once: true });
  } else {
    callback();
  }
}
