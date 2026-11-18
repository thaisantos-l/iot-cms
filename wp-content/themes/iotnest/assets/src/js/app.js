import { initializeContact } from './components/contact-form.js';
import { initializeIntegrations } from './components/integrations.js';
import { initializeComponents, onReady } from './lib/components.js';
import { initializeNavigation } from './components/navigation.js';

import { initializeReveal } from './components/motion.js';

import { initializeScroll } from './lib/scroll.js';

onReady(() => {
  initializeScroll();
  initializeComponents(document,[{selector:'[data-contact-form]',initialize:initializeContact},{selector:'body',initialize:initializeIntegrations}]);
  initializeComponents(document,[{selector:"body",initialize:initializeReveal}]);
  initializeComponents(document, [
  { selector: '.site-header', initialize: initializeNavigation },
  ]);
});
