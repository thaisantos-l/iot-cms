import { initializeComponents, onReady } from './lib/components.js';
import { initializeNavigation } from './components/navigation.js';

import { initializeScroll } from './lib/scroll.js';

onReady(() => {
  initializeScroll();
  initializeComponents(document, [
  { selector: '.site-header', initialize: initializeNavigation },
  ]);
});
