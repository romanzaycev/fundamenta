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
import {createVuestic} from "vuestic-ui";
import "vuestic-ui/css";
import {markRaw} from "vue";

const vuestic = createVuestic();
const router = VueRouter.createRouter({
    history: VueRouter.createWebHistory(
        // @ts-ignore
        window.__fndaapp.env.ROUTER_BASE_PATH,
    ),
    routes,
});
const pinia = createPinia();

pinia.use(({ store }) => {
    store.$router = markRaw(router);
})

const app = createApp(App);

app.use(vuestic);
app.use(pinia);
app.use(router);
app.use(bemPlugin);
app.use(clickOutsidePlugin);

app.mount('#fundamenta-admin-app');
