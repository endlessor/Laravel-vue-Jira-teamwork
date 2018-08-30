import Vue from 'vue';
import axios from 'axios';
import VueRouter from 'vue-router';
import Addteamworkaccounts from './components/Addteamworkaccounts.vue';
import SetLinkedProjects from './components/Setlinkedprojects.vue';

Vue.use(VueRouter);
let router = new VueRouter({
  routes: [
    { path: '/', component: Addteamworkaccounts, name: 'addteamwork' },
    { path: '/Setlinkedprojects', component: SetLinkedProjects, name: 'setlinkedproject' },
  ]
});
export default router;
