import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

// Palet pastel mint-sage. Nama "indigo" dialihkan ke palet yang sama,
// jadi semua view lama otomatis ikut berubah warna.
const brand = {
    50: '#f1f8f6',
    100: '#ddeee9',
    200: '#bfe0d7',
    300: '#9accbf',
    400: '#6fb0a0',
    500: '#4f9484',
    600: '#3d776b',
    700: '#335f56',
    800: '#2c4d46',
    900: '#26403b',
    950: '#14231f',
};

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                brand,
                indigo: brand,
            },
            borderRadius: {
                md: '0.375rem',
                lg: '0.5rem',
                xl: '0.5rem',
            },
            fontFamily: {
                sans: ['"IBM Plex Sans"', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};