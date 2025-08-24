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
                    50: '#f8f7fb',
                    100: '#f1eef6',
                    200: '#e5dded',
                    300: '#d1c3de',
                    400: '#b99fca',
                    500: '#a27db4',
                    600: '#8b609a',
                    700: '#744e80',
                    800: '#532563',
                    900: '#461f53',
                    950: '#2e1238',
                },
                purple: {
                    50: '#f8f7fb',
                    100: '#f1eef6',
                    200: '#e5dded',
                    300: '#d1c3de',
                    400: '#b99fca',
                    500: '#a27db4',
                    600: '#8b609a',
                    700: '#744e80',
                    800: '#532563',
                    900: '#461f53',
                    950: '#2e1238',
                },
            },
        },
    },

    plugins: [forms],
};
