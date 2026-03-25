import { createRouter, createWebHistory } from 'vue-router';

const routes = [
    { path: '/', name: 'home', component: () => import('../views/HomeView.vue') },
    { path: '/products', name: 'products', component: () => import('../views/ProductsView.vue') },
    {
        path: '/admin',
        component: () => import('../views/AdminLayout.vue'),
        children: [
            { path: '', redirect: { name: 'admin-products' } },
            {
                path: 'products',
                name: 'admin-products',
                meta: { title: 'Products' },
                component: () => import('../views/AdminProductsView.vue'),
            },
            {
                path: 'activity',
                name: 'admin-activity',
                meta: { title: 'Activity history' },
                component: () => import('../views/AdminActivityView.vue'),
            },
            {
                path: 'landing',
                name: 'admin-landing',
                meta: { title: 'Landing page' },
                component: () => import('../views/AdminLandingView.vue'),
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, _from, savedPosition) {
        if (to.hash) {
            return { el: to.hash, behavior: 'smooth' };
        }
        if (savedPosition) {
            return savedPosition;
        }
        return { top: 0 };
    },
});

export default router;
