import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        // Flowbite JS components
        './node_modules/flowbite/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans:    ['DM Sans', ...defaultTheme.fontFamily.sans],
                display: ['Syne', ...defaultTheme.fontFamily.sans],
                mono:    ['DM Mono', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                brand: {
                    50:  '#fff7ed',
                    100: '#ffedd5',
                    200: '#fed7aa',
                    300: '#fdba74',
                    400: '#fb923c',
                    500: '#f97316',   // primary accent
                    600: '#ea580c',
                    700: '#c2410c',
                    800: '#9a3412',
                    900: '#7c2d12',
                },
            },
            borderRadius: {
                '2xl': '1rem',
                '3xl': '1.5rem',
            },
            boxShadow: {
                'card': '0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04)',
                'card-hover': '0 8px 30px rgba(0,0,0,.08)',
            },
        },
    },

    plugins: [
        forms,
        require('flowbite/plugin'),
    ],
};