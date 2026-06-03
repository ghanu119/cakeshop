import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './themes/**/resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                display: ['Outfit', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50: '#fffbeb',
                    100: '#fef3c7',
                    200: '#fde68a',
                    300: '#fcd34d',
                    400: '#fbbf24',
                    500: '#f59e0b',
                    600: '#d97706',
                    700: '#b45309',
                    800: '#92400e',
                    900: '#78350f',
                },
                warm: {
                    50: '#fff8f3',
                    100: '#ffedd5',
                    200: '#fed7aa',
                    300: '#fdba74',
                    400: '#fb923c',
                    500: '#f97316',
                },
            },
            boxShadow: {
                'soft': '0 1px 3px 0 rgb(0 0 0 / 0.05), 0 1px 2px -1px rgb(0 0 0 / 0.05)',
                'card': '0 1px 3px 0 rgb(0 0 0 / 0.04), 0 1px 2px -1px rgb(0 0 0 / 0.04)',
                'card-hover': '0 20px 25px -5px rgb(0 0 0 / 0.08), 0 8px 10px -6px rgb(0 0 0 / 0.05)',
                'glow': '0 0 40px -10px rgb(245 158 11 / 0.35)',
            },
            backgroundImage: {
                'gradient-warm': 'linear-gradient(135deg, #fffbeb 0%, #fff7ed 50%, #ffedd5 100%)',
                'gradient-hero': 'linear-gradient(135deg, #d97706 0%, #ea580c 50%, #c2410c 100%)',
                'mesh': 'radial-gradient(at 40% 20%, rgb(254 243 199) 0px, transparent 50%), radial-gradient(at 80% 0%, rgb(255 237 213) 0px, transparent 50%), radial-gradient(at 0% 50%, rgb(255 251 235) 0px, transparent 50%)',
            },
            transitionDuration: {
                150: '150ms',
                200: '200ms',
                300: '300ms',
            },
            animation: {
                'fade-in': 'fadeIn 0.5s ease-out',
                'slide-up': 'slideUp 0.5s ease-out',
            },
            keyframes: {
                fadeIn: { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
                slideUp: { '0%': { opacity: '0', transform: 'translateY(20px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
            },
        },
    },

    plugins: [forms],
};
