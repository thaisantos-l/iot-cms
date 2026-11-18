import { initializeComponents, onReady } from './lib/components.js';
import { initializeNavigation } from './components/navigation.js';

import { initializeReveal } from './components/motion.js';

import { initializeScroll } from './lib/scroll.js';

onReady(() => {
  initializeScroll();
  initializeComponents(document,[{selector:"body",initialize:initializeReveal}]);
  initializeComponents(document, [
  { selector: '.site-header', initialize: initializeNavigation },
  ]);
});
