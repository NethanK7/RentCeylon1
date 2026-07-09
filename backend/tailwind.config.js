import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import aspectRatio from '@tailwindcss/aspect-ratio';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.tsx',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
                display: ['"Plus Jakarta Sans"', 'Inter', ...defaultTheme.fontFamily.sans],
                serif: ['"Playfair Display"', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                // RentCeylon brand — lotus-mark navy, taken straight from the logo.
                brand: {
                    50: '#eef4fb', 100: '#dbe8f6', 200: '#afcbef', 300: '#7ea9e0',
                    400: '#4d84c9', 500: '#2f5b9b', 600: '#1f4278', 700: '#173562',
                    800: '#123063', 900: '#0d2247', 950: '#081733',
                },
                // Gold ring accent from the logo — primary call-to-action colour.
                gold: {
                    50: '#fdf8ec', 100: '#faedc9', 200: '#f4da93', 300: '#efc468',
                    400: '#dca62c', 500: '#c6900f', 600: '#a5760c', 700: '#7d5a0b',
                    800: '#5e440c', 900: '#4a370d',
                },
                ceylon: {
                    50: '#f0fdfa', 100: '#ccfbf1', 200: '#99f6e4', 300: '#5eead4',
                    400: '#2dd4bf', 500: '#14b8a6', 600: '#0d9488', 700: '#0f766e',
                    800: '#115e59', 900: '#134e4a',
                },
            },
            boxShadow: {
                card: '0 1px 2px rgba(0,0,0,0.08), 0 4px 12px rgba(0,0,0,0.05)',
                hover: '0 6px 20px rgba(0,0,0,0.12)',
                nav: '0 1px 0 rgba(0,0,0,0.06)',
            },
            borderRadius: {
                xl: '0.875rem',
                '2xl': '1.25rem',
            },
        },
    },

    plugins: [forms, aspectRatio],
};
