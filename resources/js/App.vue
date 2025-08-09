<template>
    <div class="min-h-full">
        <NavBar />
        <RouterView />
        <SiteFooter class="mt-16" />
        <!-- Modal global -->
        <AuthModal v-model:open="ui.authOpen" :mode="ui.authMode" />
    </div>
</template>

<script setup>
import { onMounted } from "vue";
import { useAuth } from "./stores/auth";
import { useUi } from "./stores/ui";
import NavBar from "./components/landing/NavBar.vue";
import SiteFooter from "./components/landing/SiteFooter.vue";
import AuthModal from "./components/auth/AuthModal.vue";

const auth = useAuth();
const ui = useUi();

onMounted(async () => {
    auth.bootstrap();
    await auth.me(); // restaurar sesión si había token
});
</script>
