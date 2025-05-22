import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50: '#fef0e6',
                    100: '#fde1cd',
                    200: '#fbc29b',
                    300: '#f9a26a',
                    400: '#f7843a',
                    500: '#f56a1a',
                    600: '#dd550b',
                    700: '#b54d0d',
                    800: '#8f3e0e',
                    900: '#682f0e',
                    950: '#431f0f',
                }
            }
        },
    },

    plugins: [forms],
};
