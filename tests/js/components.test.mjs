import assert from 'node:assert/strict';
import test from 'node:test';
import { initializeComponents, onReady } from '../../wp-content/themes/iotnest/assets/src/js/lib/components.js';

test('zero, um e vários elementos; inicializar novamente não duplica handlers', () => {
  let calls = 0;
  const initialize = () => calls++;
  const components = [{ selector: '[data-test]', initialize }];
  initializeComponents({ querySelectorAll: () => [] }, components);
  assert.equal(calls, 0);
  const one = {};
  const root = { querySelectorAll: () => [one] };
  initializeComponents(root, components);
  initializeComponents(root, components);
  assert.equal(calls, 1);
  initializeComponents({ querySelectorAll: () => [one, {}, {}] }, components);
  assert.equal(calls, 3);
});

test('entradas defer funcionam antes e depois do DOMContentLoaded', () => {
  let calls = 0;
  onReady(() => calls++, { readyState: 'complete' });
  let listener;
  onReady(() => calls++, {
    readyState: 'loading',
    addEventListener: (event, callback, options) => {
      assert.equal(event, 'DOMContentLoaded');
      assert.equal(options.once, true);
      listener = callback;
    },
  });
  assert.equal(calls, 1);
  listener();
  assert.equal(calls, 2);
});
