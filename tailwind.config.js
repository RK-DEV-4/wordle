 /** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/view/**/*.blade.php",
    "./resources/js/**/*.svelte"
  ],
  safelist: [
    'grid-cols-1',
    'grid-cols-2',
    'grid-cols-3',
    'grid-cols-4',
    'grid-cols-5',
    'grid-cols-6',
    'grid-cols-7',
    'grid-cols-8',
    'grid-rows-1',
    'grid-rows-2',
    'grid-rows-3',
    'grid-rows-4',
    'grid-rows-5',
    'grid-rows-6',
    'grid-rows-7',
    'grid-rows-8',
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}