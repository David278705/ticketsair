import { createRouter, createWebHistory } from "vue-router";
import HomePage from "../pages/HomePage.vue";
import MyTrips from "../pages/MyTrips.vue";
import ClientMessages from "../pages/ClientMessages.vue";
import { useAuth } from "../stores/auth";
import AdminFlights from "../pages/admin/AdminFlights.vue";
import UserManagement from "../pages/admin/UserManagement.vue";
import MessagesAdmin from "../pages/admin/MessagesAdmin.vue";

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: "/", component: HomePage },
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
            path: "/admin/flights",
            component: AdminFlights,
            meta: { auth: true, role: ["admin", "root"] },
        },
        {
            path: "/admin/users",
            component: UserManagement,
            meta: { auth: true, role: "root" },
        },
        {
            path: "/admin/messages",
            component: MessagesAdmin,
            meta: { auth: true, role: ["admin", "root"] },
        },
        // Ruta catch-all para manejar 404s
        { path: "/:pathMatch(.*)*", redirect: "/" },
    ],
});

router.beforeEach(async (to) => {
    const auth = useAuth();
    if (auth.token && !auth.user) await auth.me();
    if (to.meta?.auth && !auth.user) return { path: "/" };
    if (to.meta?.role) {
        const need = Array.isArray(to.meta.role)
            ? to.meta.role
            : [to.meta.role];
        const have = auth.user?.role?.name;
        if (!need.includes(have)) return { path: "/" };
    }
});
export default router;
