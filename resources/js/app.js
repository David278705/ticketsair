import { createApp } from "vue";
import App from "./App.vue";
import reveal from "./directives/reveal";
import { createPinia } from "pinia";

import router from "./router";

createApp(App).use(createPinia()).use(router).mount("#app");

const app = createApp(App);
app.use(createPinia());
app.use(router);
app.directive("reveal", reveal);
app.mount("#app");
