import "reflect-metadata";
import "@/di";
import {createApp} from "vue/dist/vue.esm-bundler.js";
import * as VueRouter from "vue-router";
import {createPinia} from "pinia";
import App from "@/App.vue";
import bemPlugin from "@/plugins/bem-plugin";
import clickOutsidePlugin from "@/plugins/click-outside-plugin";
import "./main.scss";
import routes from "@/routes";

// FIXME Read config from `window.__fndaapp.*`

const router = VueRouter.createRouter({
    history: VueRouter.createWebHashHistory(),
    routes,
});
const pinia = createPinia();
const app = createApp(App);

app.use(pinia);
app.use(router);
app.use(bemPlugin);
app.use(clickOutsidePlugin);

app.mount('#fundamenta-admin-app');
