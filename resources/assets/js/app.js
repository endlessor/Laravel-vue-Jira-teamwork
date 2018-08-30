
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
import Vue from 'vue';
import Notifications from 'vue-notification';
import router from './router.js';
import App from './components/AdminPanel.vue';

require('./bootstrap');
Vue.use(Notifications);
const app = new Vue({
    el: '#app',
    render: h => h(App),
    router,
    Notifications
});
