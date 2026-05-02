/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './index.html',
    './src/**/*.{vue,js,ts,jsx,tsx}',
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#003366',
          light: '#E6F0FF',
          dark: '#002244',
        },
        accent: '#FF6B35',
        'brand-text': '#1A1A2E',
        'brand-secondary': '#6C757D',
        'brand-border': '#DEE2E6',
      },
      fontFamily: {
        sans: ['Arial', 'Microsoft YaHei', 'PingFang SC', 'sans-serif'],
      },
    },
  },
  plugins: [],
  // Prevent Tailwind from conflicting with Element Plus prefixed classes
  corePlugins: {
    preflight: false,
  },
}


