import './bootstrap';
import '../css/app.css';
import { createApp, h } from 'vue';
import { createI18n } from 'vue-i18n';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import MyLayout from "@/Shared/MyLayout.vue";
import LangEn from '../../lang/en.json';
import LangDe from '../../lang/de.json';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

createInertiaApp({
    progress: {
        color: '#4B5563',
    },
    title: (title) => `${appName} | ${title}`,
    resolve: (name) => {
        const page = resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue'));
        page.then((module) => {
            module.default.layout = module.default.layout || MyLayout;
        });
        return page;
    },
    setup({ el, App, props, plugin }) {

        const i18n = createI18n({
            locale: props.initialPage.props.locale,
            fallbackLocale: 'de',
            messages: { de: LangDe, en: LangEn },
        })

        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .use(i18n)
            .mount(el);
    },
});
