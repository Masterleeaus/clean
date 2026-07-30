// Plugins
import { registerPlugins } from '@/plugins';
// Components
import App from './App.vue';
// Composables
import { createApp } from 'vue';
import router from "@/router";
import store from "@/store";
import {getLocalValue} from "@/lib/local";
import  "./assets/css/app.css";

const app = createApp(App)
  .use(router)
  .use(store);

app.config.globalProperties.$tr = getLocalValue;

registerPlugins(app);

app.mount('#app');
