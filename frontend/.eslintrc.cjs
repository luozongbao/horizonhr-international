/* eslint-env node */
module.exports = {
  root: true,
  parser: 'vue-eslint-parser',
  parserOptions: {
    parser: '@typescript-eslint/parser',
    ecmaVersion: 2022,
    sourceType: 'module',
    extraFileExtensions: ['.vue'],
  },
  env: {
    browser: true,
    es2022: true,
    node: true,
  },
  extends: [
    'eslint:recommended',
    'plugin:@typescript-eslint/recommended',
    'plugin:vue/vue3-recommended',
  ],
  plugins: ['@typescript-eslint', 'vue'],
  rules: {
    // Enforce <script setup> for all SFCs
    'vue/component-api-style': ['error', ['script-setup']],

    // Allow unused vars prefixed with _ (common convention)
    '@typescript-eslint/no-unused-vars': ['warn', { argsIgnorePattern: '^_', varsIgnorePattern: '^_' }],

    // Allow `any` in some cases (loosen for prototype phase)
    '@typescript-eslint/no-explicit-any': 'warn',

    // Require multi-word component names
    'vue/multi-word-component-names': 'off',

    // Allow single-line HTML elements
    'vue/html-self-closing': ['error', { html: { void: 'always', normal: 'never', component: 'always' } }],

    // Max attributes per line
    'vue/max-attributes-per-line': ['error', { singleline: { max: 4 }, multiline: { max: 1 } }],
  },
  ignorePatterns: [
    'dist/',
    'node_modules/',
    'src/auto-imports.d.ts',
    'src/components.d.ts',
    'postcss.config.js',
  ],
}
