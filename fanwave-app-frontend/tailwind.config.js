/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./components/**/*.{js,vue,ts}",
    "./layouts/**/*.{vue,js,ts}",
    "./pages/**/*.{vue,js,ts}",
    "./plugins/**/*.{js,ts}",
    "./composables/**/*.{js,ts}",
    "./utils/**/*.{js,ts}",
    "./app.vue",
    "./error.vue"
  ],
  safelist: [
    'bg-blue-500',
    'bg-red-500', 
    'text-white',
    'text-4xl',
    'text-xl',
    'font-bold',
    'mb-4',
    'mb-8',
    'p-8',
    'px-8',
    'py-3',
    'rounded',
    'border-2',
    'border-white',
    'flex',
    'gap-4',
    'justify-center',
    'text-center'
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#f0f4ff',
          100: '#e0e7ff',
          200: '#c7d2fe',
          300: '#a5b4fc',
          400: '#818cf8',
          500: '#667eea',
          600: '#5a67d8',
          700: '#4c51bf',
          800: '#434190',
          900: '#3c366b',
        },
        success: {
          50: '#f0fff4',
          100: '#c6f6d5',
          200: '#9ae6b4',
          300: '#68d391',
          400: '#48bb78',
          500: '#38a169',
          600: '#2f855a',
          700: '#276749',
          800: '#22543d',
          900: '#1a202c',
        },
        danger: {
          50: '#fed7d7',
          100: '#feb2b2',
          200: '#fc8181',
          300: '#f56565',
          400: '#e53e3e',
          500: '#c53030',
          600: '#9b2c2c',
          700: '#742a2a',
          800: '#63171b',
          900: '#1a202c',
        }
      },
      animation: {
        'fade-in': 'fadeIn 0.5s ease-in-out',
        'spin-slow': 'spin 1s linear infinite',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0', transform: 'translateY(20px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        }
      }
    },
  },
  plugins: [],
}
