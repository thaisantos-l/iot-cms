import js from '@eslint/js';
import globals from 'globals';

export default [
  { ignores: ['wp-admin/**', 'wp-includes/**', 'node_modules/**', 'vendor/**', 'artifacts/**', 'test-results/**', 'playwright-report/**', '**/assets/dist/**', 'wp-content/themes/iotnest/assets/src/js/vendor/lenis.js', 'wp-content/themes/twenty*/**', 'wp-content/plugins/akismet/**'] },
  js.configs.recommended,
  {
    files: ['scripts/**/*.mjs', 'tests/**/*.mjs', '*.mjs'],
    languageOptions: { globals: globals.node },
  },
  {
    files: ['wp-content/**/assets/src/js/**/*.js', 'tests/e2e/**/*.mjs'],
    languageOptions: { globals: globals.browser },
  },
];
