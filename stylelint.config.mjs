export default {
  extends: ['stylelint-config-standard-scss'],
  rules: {
    // BEM é a convenção existente no front; preservar classes evita regressões.
    'selector-class-pattern': '^[a-z][a-z0-9]*(?:[-_][a-z0-9]+|--[a-z0-9-]+|__[a-z0-9-]+)*$',
  },
};
