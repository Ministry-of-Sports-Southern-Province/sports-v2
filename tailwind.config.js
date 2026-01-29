/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./public/**/*.php",
    "./includes/**/*.php",
    "./login.php",
    "./assets/js/**/*.js",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Poppins', 'Noto Sans Sinhala', 'Iskoola Pota', 'sans-serif'],
      },
    },
  },
  plugins: [],
};
