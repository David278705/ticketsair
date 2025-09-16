<template>
    <header
        class="sticky top-0 z-50 border-b border-slate-200/60 dark:border-slate-800 transition-[background-color,backdrop-filter] duration-300"
        :class="[
            scrolled ? 'shadow-md' : '',
            !open
                ? 'backdrop-blur supports-[backdrop-filter]:bg-white/70 bg-white/90 dark:bg-slate-950/70'
                : 'bg-white/95 dark:bg-slate-950/95',
        ]"
    >
        <div class="container flex h-16 items-center justify-between">
            <RouterLink to="/" class="flex items-center gap-2 font-semibold">
                <span
                    class="inline-flex size-8 items-center justify-center rounded-xl bg-gradient-to-tr from-blue-600 to-cyan-400 text-white shadow-md"
                    >✈️</span
                >
                <span>TicketsAir</span>
            </RouterLink>

            <nav class="hidden md:flex items-center gap-8 text-sm">
                <a
                    href="/#buscar"
                    class="hover:text-blue-600 dark:hover:text-cyan-400 transition-colors"
                    >Vuelos</a
                >
                <RouterLink
                    v-if="auth.user?.role?.name === 'client'"
                    to="/mis-viajes"
                    class="hover:text-blue-600 dark:hover:text-cyan-400"
                    >Mis viajes</RouterLink
                >
                <RouterLink
                    v-if="auth.user?.role?.name === 'client'"
                    to="/mensajes"
                    class="hover:text-blue-600 dark:hover:text-cyan-400"
                    >Mensajes</RouterLink
                >

                <!-- Menú de administración -->
                <div
                    v-if="
                        auth.user?.role?.name === 'admin' ||
                        auth.user?.role?.name === 'root'
                    "
                    class="relative"
                    @mouseenter="adminMenuOpen = true"
                    @mouseleave="adminMenuOpen = false"
                >
                    <button
                        class="hover:text-blue-600 dark:hover:text-cyan-400 transition-colors flex items-center gap-1"
                    >
                        Administración
                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 9l-7 7-7-7"
                            ></path>
                        </svg>
                    </button>

                    <Transition
                        enter-active-class="transition ease-out duration-100"
                        enter-from-class="transform opacity-0 scale-95"
                        enter-to-class="transform opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-75"
                        leave-from-class="transform opacity-100 scale-100"
                        leave-to-class="transform opacity-0 scale-95"
                    >
                        <div
                            v-if="adminMenuOpen"
                            class="absolute top-full left-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 py-1 z-50"
                        >
                            <RouterLink
                                to="/admin/flights"
                                class="block px-4 py-2 text-sm hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                                @click="adminMenuOpen = false"
                            >
                                Gestión de vuelos
                            </RouterLink>
                            <RouterLink
                                v-if="auth.user?.role?.name === 'root'"
                                to="/admin/users"
                                class="block px-4 py-2 text-sm hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                                @click="adminMenuOpen = false"
                            >
                                Gestión de usuarios
                            </RouterLink>
                            <RouterLink
                                to="/admin/messages"
                                class="block px-4 py-2 text-sm hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                                @click="adminMenuOpen = false"
                            >
                                Mensajes
                            </RouterLink>
                        </div>
                    </Transition>
                </div>
            </nav>

            <!-- Zona derecha -->
            <div class="hidden md:flex items-center gap-2">
                <template v-if="!auth.user">
                    <button
                        class="px-4 h-10 rounded-xl border border-slate-300/70 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
                        @click="ui.openAuth('login')"
                    >
                        Iniciar sesión
                    </button>
                    <button
                        class="px-4 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-cyan-400 text-white shadow hover:shadow-lg transition-shadow"
                        @click="ui.openAuth('register')"
                    >
                        Crear cuenta
                    </button>
                </template>
                <template v-else>
                    <span
                        class="text-sm text-slate-600 dark:text-slate-300 mr-1"
                        >Hola,
                        {{ auth.user.first_name || auth.user.role.name }}</span
                    >
                    <button
                        class="px-4 h-10 rounded-xl border border-slate-300/70 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
                        @click="logout"
                    >
                        Cerrar sesión
                    </button>
                </template>
                <button
                    @click="toggleDark"
                    class="ml-1 size-10 grid place-items-center rounded-xl border border-slate-300/70 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
                    :title="isDark ? 'Modo claro' : 'Modo oscuro'"
                >
                    <span v-if="isDark">🌙</span>
                    <span v-else>☀️</span>
                </button>
            </div>

            <!-- Botón hamburguesa móvil -->
            <button
                class="md:hidden relative size-10 grid place-items-center rounded-xl border border-slate-300/70 dark:border-slate-700"
                @click="open = !open"
                aria-label="Menú"
            >
                <span
                    class="absolute h-[2px] w-6 bg-current transition-transform duration-300"
                    :class="open ? 'rotate-45' : '-translate-y-2'"
                ></span>
                <span
                    class="absolute h-[2px] w-6 bg-current transition-opacity duration-300"
                    :class="open ? 'opacity-0' : 'opacity-100'"
                ></span>
                <span
                    class="absolute h-[2px] w-6 bg-current transition-transform duration-300"
                    :class="open ? '-rotate-45' : 'translate-y-2'"
                ></span>
            </button>
        </div>

        <!-- Overlay -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-[2px] md:hidden"
                @click="close()"
            ></div>
        </Transition>

        <!-- Menú móvil -->
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
        >
            <div
                v-if="open"
                class="md:hidden z-50 relative border-t border-slate-200 dark:border-slate-800 bg-white/95 dark:bg-slate-950/95"
            >
                <div class="container py-3 flex flex-col gap-2">
                    <a href="#buscar" @click="close()">Vuelos</a>
                    <RouterLink
                        v-if="auth.user?.role?.name === 'client'"
                        to="/mis-viajes"
                        @click="close()"
                        >Mis viajes</RouterLink
                    >
                    <RouterLink
                        v-if="auth.user?.role?.name === 'client'"
                        to="/mensajes"
                        @click="close()"
                        >Mensajes</RouterLink
                    >

                    <!-- Opciones de admin en móvil -->
                    <template
                        v-if="
                            auth.user?.role?.name === 'admin' ||
                            auth.user?.role?.name === 'root'
                        "
                    >
                        <div
                            class="border-t border-slate-200 dark:border-slate-700 pt-2 mt-2"
                        >
                            <p
                                class="text-xs text-slate-500 dark:text-slate-400 font-medium mb-2"
                            >
                                ADMINISTRACIÓN
                            </p>
                            <RouterLink
                                to="/admin/flights"
                                @click="close()"
                                class="block py-1"
                            >
                                Gestión de vuelos
                            </RouterLink>
                            <RouterLink
                                v-if="auth.user?.role?.name === 'root'"
                                to="/admin/users"
                                @click="close()"
                                class="block py-1"
                            >
                                Gestión de usuarios
                            </RouterLink>
                            <RouterLink
                                to="/admin/messages"
                                @click="close()"
                                class="block py-1"
                            >
                                Mensajes
                            </RouterLink>
                        </div>
                    </template>

                    <div class="pt-2 flex gap-2">
                        <template v-if="!auth.user">
                            <button
                                class="flex-1 h-10 rounded-xl border border-slate-300/70 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors"
                                @click="openLogin"
                            >
                                Iniciar sesión
                            </button>
                            <button
                                class="flex-1 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-cyan-400 text-white hover:shadow-lg transition-shadow"
                                @click="openRegister"
                            >
                                Crear cuenta
                            </button>
                        </template>
                        <template v-else>
                            <button
                                class="flex-1 h-10 rounded-xl border"
                                @click="logout"
                            >
                                Cerrar sesión
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </Transition>
    </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from "vue";
import { useAuth } from "../../stores/auth";
import { useUi } from "../../stores/ui";

const auth = useAuth();
const ui = useUi();

const open = ref(false);
const adminMenuOpen = ref(false);
const isDark = ref(false);
const scrolled = ref(false);

function openLogin() {
    ui.openAuth("login");
    open.value = false;
}
function openRegister() {
    ui.openAuth("register");
    open.value = false;
}
function close() {
    open.value = false;
}

function smoothTheme() {
    const el = document.documentElement;
    el.classList.add("theme-transition");
    setTimeout(() => el.classList.remove("theme-transition"), 400);
}
function toggleDark() {
    isDark.value = !isDark.value;
    localStorage.setItem("dark", isDark.value ? "1" : "0");
    smoothTheme();
    document.documentElement.classList.toggle("dark", isDark.value);
}
async function logout() {
    auth.logout();
    close();
}

watch(open, (v) => {
    document.body.classList.toggle("overflow-hidden", v);
});

let onScroll;
onMounted(() => {
    isDark.value = localStorage.getItem("dark") === "1";
    document.documentElement.classList.toggle("dark", isDark.value);
    onScroll = () => {
        scrolled.value = window.scrollY > 8;
    };
    window.addEventListener("scroll", onScroll, { passive: true });
    onScroll();
});
onUnmounted(() => {
    window.removeEventListener("scroll", onScroll);
});
</script>
