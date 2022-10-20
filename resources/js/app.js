import { router } from "./router/router";
import Btn from "./components/Btn.vue";
import VueHead from "vue-head";
import VueResource from "vue-resource";
import { store } from "./store/index";
import { Auth } from "./mixins/auth";
import AudioRecorder from "vue-audio-recorder";

/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
require("./bootstrap");
window.Vue = require("vue");

/**
 *  Vue Plugins
 */
Vue.use(VueHead);
Vue.use(VueResource);
Vue.http.options.root = "https://sh1r3f.davidlouis.co/asswat/api";
Vue.use(AudioRecorder);

/**
 *  Vue Components
 */
Vue.component("Btn", Btn);

/**
 * Mixins
 */
Vue.mixin(Auth);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
const app = new Vue({
    el: "#app",
    store,
    router
});
