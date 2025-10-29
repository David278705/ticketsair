<template>
    <header
        class="sticky background-blur top-0 z-50 border-b border-slate-200/60 transition-[background-color,backdrop-filter] duration-300"
        :class="[
            scrolled ? 'shadow-md' : '',
            !open
                ? 'backdrop-blur supports-[backdrop-filter]:bg-white/70 bg-white/90'
                : 'bg-white/95',
        ]"
    >
        <div class="container flex h-16 items-center justify-between">
            <RouterLink to="/" class="flex items-center gap-2 font-semibold">
                <img 
                    :src="logoUrl" 
                    alt="TicketsAir Logo" 
                    class="h-8 w-auto"
                >
                <span>TicketsAir</span>
            </RouterLink>

            <nav class="hidden md:flex items-center gap-8 text-sm">
                <a href="/#buscar" class="hover:text-blue-600 transition-colors"
                    >Vuelos</a
                >
                <RouterLink
                    v-if="auth.user?.role?.name === 'client'"
                    to="/mis-viajes"
                    class="hover:text-blue-600"
                    >Mis viajes</RouterLink
                >
                <RouterLink
                    v-if="auth.user"
                    to="/forum"
                    class="hover:text-blue-600"
                    >Foro</RouterLink
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
                        class="hover:text-blue-600 transition-colors flex items-center gap-1"
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
                            class="absolute top-full left-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-slate-200 py-1 z-50"
                        >
                            <!-- Solo para administradores, no para root -->
                            <RouterLink
                                v-if="auth.user?.role?.name === 'admin'"
                                to="/admin/flights"
                                class="block px-4 py-2 text-sm hover:bg-slate-100 transition-colors"
                                @click="adminMenuOpen = false"
                            >
                                Gestión de vuelos
                            </RouterLink>
                            <!-- Solo para root -->
                            <RouterLink
                                v-if="auth.user?.role?.name === 'root'"
                                to="/admin/users"
                                class="block px-4 py-2 text-sm hover:bg-slate-100 transition-colors"
                                @click="adminMenuOpen = false"
                            >
                                Gestión de usuarios
                            </RouterLink>
                            <!-- Solo para administradores, no para root -->
                            <RouterLink
                                v-if="auth.user?.role?.name === 'admin'"
                                to="/admin/forum"
                                class="block px-4 py-2 text-sm hover:bg-slate-100 transition-colors"
                                @click="adminMenuOpen = false"
                            >
                                Gestión de Foro
                            </RouterLink>
                        </div>
                    </Transition>
                </div>
            </nav>

            <!-- Zona derecha -->
            <div class="hidden md:flex items-center gap-2">
                <!-- Selector de Moneda -->
                <div 
                    class="relative"
                    @mouseenter="currencyMenuOpen = true"
                    @mouseleave="currencyMenuOpen = false"
                >
                    <button
                        class="flex items-center gap-2 px-3 h-10 rounded-xl border border-slate-300/70 hover:bg-slate-100 transition-colors"
                        title="Cambiar moneda"
                    >
                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm font-medium">{{ currentCurrency }}</span>
                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Dropdown de monedas -->
                    <Transition
                        enter-active-class="transition ease-out duration-100"
                        enter-from-class="transform opacity-0 scale-95"
                        enter-to-class="transform opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-75"
                        leave-from-class="transform opacity-100 scale-100"
                        leave-to-class="transform opacity-0 scale-95"
                    >
                        <div
                            v-if="currencyMenuOpen"
                            class="absolute right-0 top-full mt-2 w-48 bg-white rounded-lg shadow-lg border border-slate-200 py-1 z-50"
                        >
                            <button
                                v-for="currency in availableCurrencies"
                                :key="currency.code"
                                @click="changeCurrency(currency.code)"
                                class="w-full flex items-center justify-between px-4 py-2 text-sm hover:bg-slate-100 transition-colors"
                                :class="{ 'bg-blue-50 text-blue-600': currentCurrency === currency.code }"
                            >
                                <span class="flex items-center gap-2">
                                    <span class="font-medium">{{ currency.code }}</span>
                                    <span class="text-xs text-slate-500">{{ currency.symbol }}</span>
                                </span>
                                <svg 
                                    v-if="currentCurrency === currency.code"
                                    class="w-4 h-4 text-blue-600" 
                                    fill="currentColor" 
                                    viewBox="0 0 20 20"
                                >
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </Transition>
                </div>

                <template v-if="!auth.user">
                    <button
                        class="px-4 h-10 rounded-xl border border-slate-300/70 hover:bg-slate-100 transition-colors"
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
                    <!-- Menú de usuario -->
                    <div
                        class="relative"
                        @mouseenter="userMenuOpen = true"
                        @mouseleave="userMenuOpen = false"
                    >
                        <button
                            class="flex items-center gap-2 px-4 h-10 rounded-xl border border-slate-300/70 hover:bg-slate-100 transition-colors"
                        >
                            <div class="w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center">
                                <span class="text-xs text-indigo-600 font-medium">
                                    {{ (auth.user.first_name || auth.user.name || 'U').charAt(0).toUpperCase() }}
                                </span>
                            </div>
                            <span class="text-sm">
                                {{ auth.user.first_name || auth.user.name || 'Usuario' }}
                            </span>
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Dropdown del usuario -->
                        <Transition
                            enter-active-class="transition duration-200 ease-out"
                            enter-from-class="transform scale-95 opacity-0"
                            enter-to-class="transform scale-100 opacity-100"
                            leave-active-class="transition duration-150 ease-in"
                            leave-from-class="transform scale-100 opacity-100"
                            leave-to-class="transform scale-95 opacity-0"
                        >
                            <div
                                v-if="userMenuOpen"
                                class="absolute right-0 top-full mt-2 w-56 rounded-lg bg-white shadow-xl border border-slate-200/60 py-2 z-50"
                            >
                                <div class="px-4 py-2 border-b border-slate-100">
                                    <p class="text-sm font-medium text-slate-900">{{ auth.user.name }}</p>
                                    <p class="text-xs text-slate-500">{{ auth.user.email }}</p>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium mt-1"
                                          :class="getRoleBadgeClass(auth.user.role?.name)">
                                        {{ getRoleDisplayName(auth.user.role?.name) }}
                                    </span>
                                </div>
                                
                                <RouterLink
                                    to="/profile"
                                    class="flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors"
                                    @click="userMenuOpen = false"
                                >
                                    <svg class="w-4 h-4 mr-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Mi Perfil
                                </RouterLink>
                                
                                <button
                                    @click="logout"
                                    class="w-full flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors text-left"
                                >
                                    <svg class="w-4 h-4 mr-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Cerrar Sesión
                                </button>
                            </div>
                        </Transition>
                    </div>
                </template>
                <!-- <button
                    @click="toggleDark"
                    class="ml-1 size-10 grid place-items-center rounded-xl border border-slate-300/70 hover:bg-slate-100 transition-colors"
                    :title="isDark ? 'Modo claro' : 'Modo oscuro'"
                >
                    <span v-if="isDark">🌙</span>
                    <span v-else>☀️</span>
                </button> -->
            </div>

            <!-- Botón hamburguesa móvil -->
            <button
                class="md:hidden relative size-10 grid place-items-center rounded-xl border border-slate-300/70"
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
                class="md:hidden z-50 relative border-t border-slate-200 bg-white/95"
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
                        v-if="auth.user"
                        to="/forum"
                        @click="close()"
                        >Foro</RouterLink
                    >

                    <!-- Opciones de admin en móvil -->
                    <template
                        v-if="
                            auth.user?.role?.name === 'admin' ||
                            auth.user?.role?.name === 'root'
                        "
                    >
                        <div class="border-t border-slate-200 pt-2 mt-2">
                            <p class="text-xs text-slate-500 font-medium mb-2">
                                ADMINISTRACIÓN
                            </p>
                            <!-- Solo para administradores, no para root -->
                            <RouterLink
                                v-if="auth.user?.role?.name === 'admin'"
                                to="/admin/flights"
                                @click="close()"
                                class="block py-1"
                            >
                                Gestión de vuelos
                            </RouterLink>
                            <!-- Solo para root -->
                            <RouterLink
                                v-if="auth.user?.role?.name === 'root'"
                                to="/admin/users"
                                @click="close()"
                                class="block py-1"
                            >
                                Gestión de usuarios
                            </RouterLink>
                            <!-- Solo para administradores, no para root -->
                            <RouterLink
                                v-if="auth.user?.role?.name === 'admin'"
                                to="/admin/forum"
                                @click="close()"
                                class="block py-1"
                            >
                                Gestión de Foro
                            </RouterLink>
                        </div>
                    </template>

                    <!-- Perfil de usuario en móvil -->
                    <div v-if="auth.user" class="pt-2 border-t border-slate-200">
                        <RouterLink
                            to="/profile"
                            @click="close()"
                            class="flex items-center py-2 text-slate-700 hover:text-blue-600 transition-colors"
                        >
                            <svg class="w-4 h-4 mr-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Mi Perfil
                        </RouterLink>
                    </div>

                    <!-- Selector de moneda en móvil -->
                    <div class="pt-2 border-t border-slate-200">
                        <p class="text-xs font-medium text-slate-500 mb-2">Moneda</p>
                        <div class="flex gap-2">
                            <button
                                v-for="currency in availableCurrencies"
                                :key="currency.code"
                                @click="changeCurrency(currency.code)"
                                class="flex-1 px-3 py-2 rounded-lg border text-sm font-medium transition-colors"
                                :class="currentCurrency === currency.code 
                                    ? 'bg-blue-50 border-blue-500 text-blue-600' 
                                    : 'border-slate-300 text-slate-700 hover:bg-slate-50'"
                            >
                                {{ currency.code }}
                            </button>
                        </div>
                    </div>

                    <div class="pt-2 flex gap-2">
                        <template v-if="!auth.user">
                            <button
                                class="flex-1 h-10 rounded-xl border border-slate-300/70 hover:bg-slate-100 transition-colors"
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
import { useCurrency } from "../../composables/useCurrency";

const auth = useAuth();
const ui = useUi();
const { currentCurrency, setCurrency, availableCurrencies } = useCurrency();

const logoUrl = '/logo.png';
const open = ref(false);
const adminMenuOpen = ref(false);
const userMenuOpen = ref(false);
const currencyMenuOpen = ref(false);
const isDark = ref(false);
const scrolled = ref(false);

function changeCurrency(code) {
    setCurrency(code);
    currencyMenuOpen.value = false;
}

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
    userMenuOpen.value = false;
    window.location.reload();
}

// Utilidades para roles
const getRoleBadgeClass = (role) => {
    const classes = {
        root: "bg-red-100 text-red-800",
        admin: "bg-blue-100 text-blue-800",
        client: "bg-green-100 text-green-800",
    };
    return classes[role] || "bg-gray-100 text-gray-800";
};

const getRoleDisplayName = (role) => {
    const names = {
        root: "Administrador Root",
        admin: "Administrador",
        client: "Cliente",
    };
    return names[role] || "Usuario";
};

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
