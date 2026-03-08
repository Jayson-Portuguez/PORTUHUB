import { createRouter, createWebHistory } from 'vue-router';

const routes = [
    { path: '/', name: 'home', component: () => import('../views/HomeView.vue') },
    { path: '/products', name: 'products', component: () => import('../views/ProductsView.vue') },
    { path: '/admin', name: 'admin', component: () => import('../views/AdminView.vue') },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
