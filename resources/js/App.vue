<template>
    <div class="min-h-full overflow-x-hidden">
        <!-- NavBar se oculta solo en la página de Google -->
        <NavBar v-if="!isGooglePage" />
        <RouterView />
        <SiteFooter v-if="!isGooglePage" class="mt-16" />
        <!-- Modal global -->
        <AuthModal v-model:open="ui.authOpen" :mode="ui.authMode" />
    </div>
</template>

<script setup>
import { computed, onMounted } from "vue";
import { useRoute } from "vue-router";
import { useAuth } from "./stores/auth";
import { useUi } from "./stores/ui";
import NavBar from "./components/landing/NavBar.vue";
import SiteFooter from "./components/landing/SiteFooter.vue";
import AuthModal from "./components/auth/AuthModal.vue";

const route = useRoute();
const auth = useAuth();
const ui = useUi();

// Detectar si estamos en la página de Google
const isGooglePage = computed(() => {
    return route.path === '/google'
});

onMounted(async () => {
    auth.bootstrap();
    await auth.me(); // restaurar sesión si había token
});
</script>