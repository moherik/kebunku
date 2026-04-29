import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    darkMode: 'class',

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                forest: {
                    50: '#F0FFF4',
                    100: '#C6F6D5',
                    200: '#9AE6B4',
                    300: '#68D391',
                    400: '#48BB78',
                    500: '#2D6A4F',
                    600: '#1B4332',
                    700: '#14532D',
                    800: '#0B3D21',
                    900: '#062016',
                    950: '#031A0E',
                },
                soil: {
                    50: '#FEFCF3',
                    100: '#FDF6E3',
                    200: '#FAECC7',
                    300: '#F5DEB3',
                    400: '#D4A76A',
                    500: '#8B6F47',
                    600: '#6B4E2F',
                    700: '#4A3520',
                    800: '#2D1F12',
                    900: '#1A1008',
                },
                harvest: {
                    50: '#FFFEF0',
                    100: '#FFF9DB',
                    200: '#FFF3B0',
                    300: '#FFE066',
                    400: '#F4C430',
                    500: '#E9C46A',
                    600: '#D4A017',
                    700: '#B8860B',
                    800: '#8B6914',
                    900: '#5C4A0E',
                },
            },
            animation: {
                'fade-in': 'fadeIn 0.5s ease-out',
                'slide-up': 'slideUp 0.5s ease-out',
                'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideUp: {
                    '0%': { opacity: '0', transform: 'translateY(20px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
            },
        },
    },

    plugins: [forms],
};

