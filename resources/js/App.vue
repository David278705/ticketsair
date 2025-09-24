<template>
    <div class="min-h-full overflow-x-hidden">
        <!-- NavBar se oculta en páginas fullscreen -->
        <NavBar v-if="!isFullscreenPage" />
        <RouterView />
        <SiteFooter v-if="!isFullscreenPage" class="mt-16" />
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

// Detectar si estamos en páginas que no necesitan header/footer
const isFullscreenPage = computed(() => {
    return route.path === '/google' || route.path === '/admin/complete-registration'
});

onMounted(async () => {
    auth.bootstrap();
    await auth.me(); // restaurar sesión si había token
});
</script>