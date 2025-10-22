import { createRouter, createWebHistory } from "vue-router";
import HomePage from "../pages/HomePage.vue";
import MyTrips from "../pages/MyTrips.vue";
import ClientMessages from "../pages/ClientMessages.vue";
import Forum from "../pages/Forum.vue";
import { useAuth } from "../stores/auth";
import AdminFlights from "../pages/admin/AdminFlights.vue";
import UserManagement from "../pages/admin/UserManagement.vue";
import MessagesAdmin from "../pages/admin/MessagesAdmin.vue";
import ForumAdmin from "../pages/admin/ForumAdmin.vue";
import SearchResults from '../components/landing/SearchResults.vue'

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: "/", component: HomePage },
        {
            path: "/reset-password",
            component: () => import("../pages/ResetPassword.vue"),
            props: (route) => ({
                token: route.query.token,
                email: route.query.email,
            }),
        },
        {
            path: "/admin/complete-registration",
            component: () => import("../pages/AdminCompleteRegistration.vue"),
            meta: { auth: true, role: "admin" },
        },
        {
            path: "/profile",
            component: () => import("../pages/UserProfile.vue"),
            meta: { auth: true },
        },
        {
            path: "/mis-viajes",
            component: MyTrips,
            meta: { auth: true, role: "client" },
        },
        {
            path: "/mensajes",
            component: ClientMessages,
            meta: { auth: true, role: "client" },
        },
        {
            path: "/forum",
            component: Forum,
            meta: { auth: true },
        },
        {
            path: "/admin/flights",
            component: AdminFlights,
            meta: { auth: true, role: "admin" },
        },
        {
            path: "/admin/users",
            component: UserManagement,
            meta: { auth: true, role: "root" },
        },
        {
            path: "/admin/messages",
            component: MessagesAdmin,
            meta: { auth: true, role: "admin" },
        },
        {
            path: "/admin/forum",
            component: ForumAdmin,
            meta: { auth: true, role: "admin" },
        },
        {
            path: '/google',
            name: 'google',
            component: SearchResults
        },
        // Ruta catch-all para manejar 404s
        { path: "/:pathMatch(.*)*", redirect: "/" },
    ],
});

router.beforeEach(async (to) => {
    const auth = useAuth();
    if (auth.token && !auth.user) await auth.me();
    if (to.meta?.auth && !auth.user) return { path: "/" };
    
    // Verificar si es un admin con registro incompleto que intenta ir a otra página
    if (auth.user && auth.user.role?.name === 'admin' && !auth.user.registration_completed) {
        if (to.path !== '/admin/complete-registration') {
            return { path: '/admin/complete-registration' };
        }
    }
    
    if (to.meta?.role) {
        const need = Array.isArray(to.meta.role)
            ? to.meta.role
            : [to.meta.role];
        const have = auth.user?.role?.name;
        if (!need.includes(have)) return { path: "/" };
    }
});
export default router;
