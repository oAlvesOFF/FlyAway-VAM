import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Geist', 'Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                crimson: {
                    50: '#fff1f2',
                    100: '#ffe4e6',
                    200: '#fecdd3',
                    300: '#fda4af',
                    400: '#fb7185',
                    500: '#e11d48',
                    600: '#be123c',
                    700: '#9f1239',
                    800: '#881337',
                    900: '#4c0519',
                    950: '#2d000b',
                },
            },
        },
    },

    plugins: [forms],
};
