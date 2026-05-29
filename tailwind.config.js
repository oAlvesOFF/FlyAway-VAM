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
                sans: ['Inter', 'Nunito', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Primary blue palette (matches #518ce5 from demo.sass-projects.dev)
                primary: {
                    50:  '#eff5fe',
                    100: '#ddeafd',
                    200: '#c3d8fb',
                    300: '#99bdf8',
                    400: '#6898f2',
                    500: '#518ce5',
                    600: '#3a6fd8',
                    700: '#2f5abf',
                    800: '#2a4b9c',
                    900: '#28417b',
                    950: '#1c2c54',
                },
                // Keep crimson for any backward compat references
                crimson: {
                    50:  '#eff5fe',
                    100: '#ddeafd',
                    200: '#c3d8fb',
                    300: '#99bdf8',
                    400: '#6898f2',
                    500: '#518ce5',
                    600: '#3a6fd8',
                    700: '#2f5abf',
                    800: '#2a4b9c',
                    900: '#28417b',
                    950: '#1c2c54',
                },
            },
            backgroundColor: {
                'page': '#f4f5fa',
                'sidebar': '#ffffff',
                'header': '#ffffff',
            },
            boxShadow: {
                'sidebar': '2px 0 10px rgba(0,0,0,0.05)',
                'card': '0 1px 4px rgba(0,0,0,0.07), 0 0 0 1px rgba(0,0,0,0.04)',
                'card-hover': '0 4px 16px rgba(81,140,229,0.12)',
            },
        },
    },

    plugins: [forms],
};
