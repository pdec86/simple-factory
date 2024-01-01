import 'material-design-icons-iconfont/dist/material-design-icons.css';
import '@mdi/font/css/materialdesignicons.css';

import { createApp } from 'vue';
import App from './productsList.vue'

// Vuex
import { store } from '../../../store/index.js';

// Vuetify
import 'vuetify/styles';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';

const vuetify = createVuetify({
    components,
    directives,
    icons: {
        defaultSet: 'mdi',
      },
});

const app = createApp(App, {});
app.use(vuetify).use(store).mount('#simplef-app');
