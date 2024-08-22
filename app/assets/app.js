import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/inertia-vue3'
import {createPinia} from "pinia";
import './app.css';
import 'font-awesome/css/font-awesome.min.css'
import './fonts/Poppins-Regular.ttf'
// Vuetify
import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import {aliases as customAliases, custom as customIcons} from './customIcons'

import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'

/**
 * Imports the given page component from the page record.
 */
function resolvePageComponent(name, pages) {
    for (const path in pages) {
        if (path.endsWith(`${name.replace('.', '/')}.vue`)) {
            return typeof pages[path] === 'function'
                ? pages[path]()
                : pages[path]
        }
    }

    throw new Error(`Page not found: ${name}`)
}

const vuetify = createVuetify({
    components,
    directives,
    icons: {
        defaultSet: 'las',
        aliases: {
            ...customAliases
        },
        sets: {
            las: customIcons
        }
    }
})

const pinia = createPinia()

// Creates the Inertia app, nothing fancy.
createInertiaApp({
    resolve: (name) => resolvePageComponent(name, import.meta.glob('./pages/**/*.vue')),
    setup({ el, app, props, plugin }) {
        createApp({ render: () => h(app, props) })
            .use(plugin)
            .use(pinia)
            .use(vuetify)
            .component('VueDatePicker', VueDatePicker)
            .mount(el)
    },
})

