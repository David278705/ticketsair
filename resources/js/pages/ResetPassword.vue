<template>
    <div
        class="min-h-screen bg-slate-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8"
    >
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <div
                    class="flex items-center justify-center gap-2 font-semibold text-2xl mb-2"
                >
                    <span
                        class="inline-flex size-10 items-center justify-center rounded-xl bg-gradient-to-tr from-blue-600 to-cyan-400 text-white shadow-md"
                        >✈️</span
                    >
                    <span>TicketsAir</span>
                </div>
                <h2 class="text-3xl font-extrabold text-gray-900">
                    Restablecer Contraseña
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Ingresa tu nueva contraseña para {{ email }}
                </p>
            </div>

            <!-- Información del usuario si está disponible -->
            <div
                v-if="userInfo"
                class="bg-blue-50 border border-blue-200 rounded-lg p-4"
            >
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <div
                            class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center"
                        >
                            <svg
                                class="w-5 h-5 text-blue-600"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                    clip-rule="evenodd"
                                ></path>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-blue-900">
                            {{ userInfo.name }}
                        </p>
                        <p class="text-xs text-blue-700">
                            {{ getRoleLabel(userInfo.role) }}
                        </p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submitReset" class="mt-8 space-y-6">
                <div class="space-y-4">
                    <!-- Nueva contraseña -->
                    <div>
                        <label
                            for="password"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Nueva contraseña
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            minlength="8"
                            class="mt-1 relative block w-full px-3 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{
                                'border-red-500': errors.password,
                            }"
                            placeholder="Mínimo 8 caracteres"
                        />
                        <p
                            v-if="errors.password"
                            class="text-red-500 text-xs mt-1"
                        >
                            {{ errors.password[0] }}
                        </p>
                    </div>

                    <!-- Confirmar contraseña -->
                    <div>
                        <label
                            for="password_confirmation"
                            class="block text-sm font-medium text-gray-700"
                        >
                            Confirmar nueva contraseña
                        </label>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            required
                            minlength="8"
                            class="mt-1 relative block w-full px-3 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{
                                'border-red-500': passwordMismatch,
                            }"
                            placeholder="Repite la contraseña"
                        />
                        <p
                            v-if="passwordMismatch"
                            class="text-red-500 text-xs mt-1"
                        >
                            Las contraseñas no coinciden
                        </p>
                    </div>
                </div>

                <!-- Mensajes de error generales -->
                <div
                    v-if="generalError"
                    class="bg-red-50 border border-red-200 rounded-lg p-4"
                >
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg
                                class="h-5 w-5 text-red-400"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd"
                                ></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">
                                {{ generalError }}
                            </p>
                        </div>
                    </div>
                </div>

                <div>
                    <button
                        type="submit"
                        :disabled="
                            loading ||
                            passwordMismatch ||
                            !form.password ||
                            !form.password_confirmation
                        "
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-tr from-blue-600 to-cyan-400 hover:from-blue-700 hover:to-cyan-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl transition-all duration-200"
                    >
                        <span
                            v-if="loading"
                            class="absolute left-0 inset-y-0 flex items-center pl-3"
                        >
                            <svg
                                class="animate-spin h-5 w-5 text-white"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                        </span>
                        {{
                            loading
                                ? "Restableciendo..."
                                : "Restablecer Contraseña"
                        }}
                    </button>
                </div>

                <div class="text-center">
                    <RouterLink
                        to="/"
                        class="text-sm text-blue-600 hover:text-blue-500 font-medium"
                    >
                        ← Volver al inicio
                    </RouterLink>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useAuth } from "../stores/auth";
import { useUi } from "../stores/ui";

const props = defineProps({
    token: String,
    email: String,
});

const router = useRouter();
const auth = useAuth();
const ui = useUi();

const loading = ref(false);
const errors = ref({});
const generalError = ref("");
const userInfo = ref(null);

const form = reactive({
    password: "",
    password_confirmation: "",
});

const passwordMismatch = computed(() => {
    return (
        form.password &&
        form.password_confirmation &&
        form.password !== form.password_confirmation
    );
});

const getRoleLabel = (role) => {
    const labels = {
        root: "Administrador Root",
        admin: "Administrador",
        client: "Cliente",
    };
    return labels[role] || "Usuario";
};

const submitReset = async () => {
    if (!props.token || !props.email || passwordMismatch.value) return;

    loading.value = true;
    errors.value = {};
    generalError.value = "";

    try {
        const response = await auth.resetPassword({
            token: props.token,
            email: props.email,
            password: form.password,
            password_confirmation: form.password_confirmation,
        });

        // Mostrar notificación de éxito
        const notification = document.createElement("div");
        notification.className =
            "fixed top-4 right-4 bg-green-500 text-white p-4 rounded-lg shadow-lg z-[100] max-w-sm";
        notification.innerHTML = `
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <p class="font-medium">¡Contraseña restablecida!</p>
                    <p class="text-sm opacity-90">Ya puedes iniciar sesión</p>
                </div>
            </div>
        `;
        document.body.appendChild(notification);

        // Remover notificación después de 5 segundos
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 5000);

        // Redirigir al home y abrir modal de login
        setTimeout(() => {
            // router.push("/");
            // ui.openAuth("login");
            window.location.href = "/";
        }, 2000);
    } catch (error) {
        if (error.response?.status === 422) {
            if (error.response.data.errors) {
                errors.value = error.response.data.errors;
            } else {
                generalError.value =
                    error.response.data.message || "Error de validación";
            }
        } else {
            generalError.value =
                error.response?.data?.message ||
                "Error al restablecer contraseña";
        }
    } finally {
        loading.value = false;
    }
};

// Verificar si el token es válido al cargar la página
onMounted(async () => {
    if (!props.token || !props.email) {
        generalError.value = "Enlace de recuperación inválido";
        return;
    }

    // Opcional: Hacer una verificación previa del token
    // Esto es opcional, solo para mejorar UX
    try {
        const user = await auth.checkResetToken(props.token, props.email);
        if (user) {
            userInfo.value = user;
        }
    } catch (error) {
        // Si falla la verificación, mostrar error pero permitir intentar el reset
        console.warn("No se pudo verificar el token previamente");
    }
});
</script>
