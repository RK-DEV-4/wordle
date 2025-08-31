import './bootstrap';
import { createInertiaApp } from "@inertiajs/svelte";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import '../css/app.css';

if (document.getElementById("app")) {
    createInertiaApp({
        title: "Wordle",
        resolve: name =>
            resolvePageComponent(
                `./Pages/${name}.svelte`,
                import.meta.glob('./Pages/**/*.svelte')
            ),
        setup({ el, App, props }) {
            new App({ target: el, props });
        },
    });
}