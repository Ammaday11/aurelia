import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

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
                // Aurelia Boutonnière beige palette. Referenced as bg-beige,
                // text-mocha, border-blush, etc. throughout storefront + admin.
                beige: {
                    DEFAULT: '#F5F0E6', // page background (cream beige)
                    surface: '#FBF8F3', // cards / panels
                },
                sand: '#C8A27C', // primary
                mocha: '#8B6F4E', // accent
                espresso: '#3E3228', // body text
                blush: '#E8D5C4', // subtle highlight
            },
            fontFamily: {
                // Body copy.
                sans: ['Lato', ...defaultTheme.fontFamily.sans],
                // Headings & section titles.
                serif: ['"Playfair Display"', '"Cormorant Garamond"', ...defaultTheme.fontFamily.serif],
                // Logo + hero tagline + decorative accents only — never body copy.
                script: ['"Great Vibes"', 'cursive'],
            },
            boxShadow: {
                soft: '0 10px 30px -10px rgba(62, 50, 40, 0.15)',
                card: '0 4px 20px -4px rgba(62, 50, 40, 0.12)',
            },
            borderRadius: {
                xl2: '1.25rem',
            },
            backgroundImage: {
                'floral-divider': "url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 20' width='200' height='20'%3E%3Cpath d='M0 10 H80 M120 10 H200' stroke='%23C8A27C' stroke-width='1'/%3E%3Ccircle cx='100' cy='10' r='3' fill='none' stroke='%238B6F4E' stroke-width='1'/%3E%3Cpath d='M94 10c0-4 4-6 6-6s6 2 6 6c0 4-4 6-6 6s-6-2-6-6z' fill='none' stroke='%238B6F4E' stroke-width='1'/%3E%3C/svg%3E\")",
            },
        },
    },

    plugins: [forms, typography],
};
