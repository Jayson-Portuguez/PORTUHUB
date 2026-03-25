import { createRouter, createWebHistory } from 'vue-router';
import HomeView from '../views/HomeView.vue';
import ProductsView from '../views/ProductsView.vue';
import AdminLayout from '../views/AdminLayout.vue';
import AdminProductsView from '../views/AdminProductsView.vue';
import AdminActivityView from '../views/AdminActivityView.vue';
import AdminLandingView from '../views/AdminLandingView.vue';

const routes = [
    { path: '/', name: 'home', component: HomeView },
    { path: '/products', name: 'products', component: ProductsView },
    {
        path: '/admin',
        component: AdminLayout,
        children: [
            { path: '', redirect: { name: 'admin-products' } },
            {
                path: 'products',
                name: 'admin-products',
                meta: { title: 'Products' },
                component: AdminProductsView,
            },
            {
                path: 'activity',
                name: 'admin-activity',
                meta: { title: 'Activity history' },
                component: AdminActivityView,
            },
            {
                path: 'landing',
                name: 'admin-landing',
                meta: { title: 'Landing page' },
                component: AdminLandingView,
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
