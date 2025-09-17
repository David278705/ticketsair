/** @type {import('tailwindcss').Config} */
module.exports = {
    // darkMode: "media",
    content: [
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue",
        "./resources/js/**/*.js",
    ],
    theme: {
        extend: {
            container: { center: true, padding: "1rem" },
            fontFamily: {
                sans: [
                    "ui-sans-serif",
                    "system-ui",
                    "Inter",
                    "Segoe UI",
                    "Roboto",
                ],
            },
        },
    },
    plugins: [],
};
