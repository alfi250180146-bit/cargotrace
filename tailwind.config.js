import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

// Biru navy resmi. Nama "indigo" dialihkan ke palet yang sama,
// jadi semua view lama otomatis ikut berubah warna.
const brand = {
    50: '#f1f5fa',
    100: '#dde7f3',
    200: '#bccfe7',
    300: '#8fb0d6',
    400: '#5d8bc0',
    500: '#3a6ba5',
    600: '#2b5486',
    700: '#234470',
    800: '#1d3858',
    900: '#172c46',
    950: '#0f1d30',
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
                md: '0.25rem',
                lg: '0.25rem',
                xl: '0.375rem',
            },
            fontFamily: {
                sans: ['"IBM Plex Sans"', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};