/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: '#0066CC',
        success: '#00CC66',
        warning: '#FF9900',
        danger: '#FF3333',
      },
    },
  },
  plugins: [],
}
