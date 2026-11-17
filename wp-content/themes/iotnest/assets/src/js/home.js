import { initializeComponents, onReady } from './lib/components.js';

// Não copiar textos ou efeitos provisórios da home para esta fundação.
onReady(() => initializeComponents(document, []));
