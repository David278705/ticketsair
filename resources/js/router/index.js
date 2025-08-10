import { createRouter, createWebHistory } from "vue-router";
import HomePage from "../pages/HomePage.vue";
import MyTrips from "../pages/MyTrips.vue";
import { useAuth } from "../stores/auth";
import AdminFlights from "../pages/admin/AdminFlights.vue";

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
            path: "/admin/flights",
            component: AdminFlights,
            meta: { auth: true, role: ["admin", "root"] },
        },
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
