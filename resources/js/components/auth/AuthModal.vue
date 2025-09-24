<template>
    <TransitionRoot :show="open" as="template">
        <Dialog as="div" class="relative z-[70]" @close="close">
            <TransitionChild
                as="template"
                enter="ease-out duration-200"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in duration-150"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <TransitionChild
                        as="template"
                        enter="ease-out duration-200"
                        enter-from="opacity-0 translate-y-2 scale-95"
                        enter-to="opacity-100 translate-y-0 scale-100"
                        leave="ease-in duration-150"
                        leave-from="opacity-100 translate-y-0 scale-100"
                        leave-to="opacity-0 translate-y-2 scale-95"
                    >
                        <DialogPanel
                            class="w-full max-w-lg transform overflow-hidden rounded-2xl bg-white p-6 shadow-xl transition-all"
                        >
                            <div class="flex items-center justify-between mb-3">
                                <DialogTitle class="text-lg font-semibold">
                                    {{
                                        mode === "login"
                                            ? "Iniciar sesión"
                                            : mode === "register"
                                            ? "Crear cuenta"
                                            : "Recuperar contraseña"
                                    }}
                                </DialogTitle>
                                <button
                                    class="size-9 grid place-items-center rounded-lg hover:bg-slate-100"
                                    @click="close"
                                >
                                    ✕
                                </button>
                            </div>

                            <div class="mb-4" v-if="mode !== 'forgot-password'">
                                <nav
                                    class="inline-flex rounded-xl border border-slate-200 overflow-hidden"
                                >
                                    <button
                                        @click="switchMode('login')"
                                        :class="tabClass('login')"
                                        class="px-4 h-10 text-sm"
                                    >
                                        Entrar
                                    </button>

                                    <button
                                        @click="switchMode('register')"
                                        :class="tabClass('register')"
                                        class="px-4 h-10 text-sm"
                                    >
                                        Crear cuenta
                                    </button>
                                </nav>
                            </div>

                            <form
                                v-if="mode === 'login'"
                                class="grid gap-3"
                                @submit.prevent="doLogin"
                            >
                                <input
                                    v-model.trim="login.email"
                                    type="email"
                                    placeholder="Email"
                                    class="h-11 rounded-xl border px-3 bg-transparent"
                                />
                                <input
                                    v-model="login.password"
                                    type="password"
                                    placeholder="Contraseña"
                                    class="h-11 rounded-xl border px-3 bg-transparent"
                                />
                                <button
                                    class="h-11 rounded-xl bg-gradient-to-tr from-blue-600 to-cyan-400 text-white"
                                    :disabled="auth.loading"
                                >
                                    {{ auth.loading ? "Entrando…" : "Entrar" }}
                                </button>

                                <button
                                    type="button"
                                    @click="switchMode('forgot-password')"
                                    class="text-sm text-blue-600 hover:underline text-center"
                                >
                                    ¿Olvidaste tu contraseña?
                                </button>

                                <p
                                    v-if="auth.error"
                                    class="text-rose-500 text-sm"
                                >
                                    {{ friendlyError(auth.error) }}
                                </p>
                            </form>

                            <!-- Formulario de recuperación de contraseña -->
                            <form
                                v-if="mode === 'forgot-password'"
                                class="grid gap-3"
                                @submit.prevent="doForgotPassword"
                            >
                                <div v-if="!forgotPasswordSent">
                                    <input
                                        v-model.trim="forgotEmail"
                                        type="email"
                                        placeholder="Ingresa tu email"
                                        required
                                        class="h-11 rounded-xl border px-3 bg-transparent w-full"
                                    />

                                    <p class="text-sm text-slate-600 mt-2 mb-4">
                                        Te enviaremos un enlace para restablecer
                                        tu contraseña.
                                    </p>

                                    <button
                                        type="submit"
                                        class="h-11 rounded-xl bg-gradient-to-tr from-blue-600 to-cyan-400 text-white w-full"
                                        :disabled="auth.loading"
                                    >
                                        {{
                                            auth.loading
                                                ? "Enviando…"
                                                : "Enviar enlace"
                                        }}
                                    </button>

                                    <button
                                        type="button"
                                        @click="switchMode('login')"
                                        class="text-sm text-slate-600 hover:underline text-center mt-3 w-full"
                                    >
                                        ← Volver al login
                                    </button>
                                </div>

                                <div v-else class="text-center space-y-4">
                                    <div
                                        class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto"
                                    >
                                        <CheckIcon
                                            class="w-8 h-8 text-green-600"
                                        />
                                    </div>

                                    <div>
                                        <h3
                                            class="text-lg font-medium text-gray-900 mb-2"
                                        >
                                            ¡Enlace enviado!
                                        </h3>
                                        <p class="text-sm text-slate-600">
                                            Hemos enviado un enlace de
                                            recuperación a
                                            <strong>{{ forgotEmail }}</strong>
                                        </p>
                                        <div
                                            v-if="userInfo"
                                            class="mt-2 p-2 bg-blue-50 rounded border"
                                        >
                                            <p class="text-xs text-blue-700">
                                                <strong>Usuario:</strong>
                                                {{ userInfo.name }}
                                                <span
                                                    class="ml-2 px-2 py-0.5 bg-blue-100 rounded text-xs"
                                                >
                                                    {{
                                                        userInfo.role === "root"
                                                            ? "Administrador Root"
                                                            : userInfo.role ===
                                                              "admin"
                                                            ? "Administrador"
                                                            : "Cliente"
                                                    }}
                                                </span>
                                            </p>
                                        </div>
                                        <p class="text-sm text-slate-600 mt-2">
                                            Por favor, revisa tu email y sigue
                                            las instrucciones.
                                        </p>
                                    </div>

                                    <!-- Solo para desarrollo - mostrar token -->
                                    <div
                                        v-if="resetToken"
                                        class="bg-yellow-50 border border-yellow-200 rounded-lg p-3"
                                    >
                                        <p class="text-xs text-yellow-700 mb-2">
                                            <strong>Modo desarrollo:</strong>
                                            Token de recuperación:
                                        </p>
                                        <code
                                            class="text-xs bg-white px-2 py-1 rounded"
                                            >{{ resetToken }}</code
                                        >
                                        <button
                                            type="button"
                                            @click="showResetForm"
                                            class="block w-full mt-2 text-xs bg-yellow-600 text-white px-3 py-1 rounded hover:bg-yellow-700"
                                        >
                                            Usar token ahora
                                        </button>
                                    </div>

                                    <button
                                        type="button"
                                        @click="switchMode('login')"
                                        class="text-sm text-blue-600 hover:underline"
                                    >
                                        ← Volver al login
                                    </button>
                                </div>

                                <p
                                    v-if="auth.error"
                                    class="text-rose-500 text-sm"
                                >
                                    {{ friendlyError(auth.error) }}
                                </p>
                            </form>

                            <!-- Formulario de nueva contraseña -->
                            <form
                                v-if="mode === 'reset-password'"
                                class="grid gap-3"
                                @submit.prevent="doResetPassword"
                            >
                                <p class="text-sm text-slate-600 mb-2">
                                    Ingresa tu nueva contraseña para
                                    <strong>{{ forgotEmail }}</strong>
                                </p>

                                <input
                                    v-model="resetForm.password"
                                    type="password"
                                    placeholder="Nueva contraseña"
                                    required
                                    minlength="8"
                                    class="h-11 rounded-xl border px-3 bg-transparent"
                                />

                                <input
                                    v-model="resetForm.password_confirmation"
                                    type="password"
                                    placeholder="Confirmar nueva contraseña"
                                    required
                                    minlength="8"
                                    class="h-11 rounded-xl border px-3 bg-transparent"
                                />

                                <button
                                    type="submit"
                                    class="h-11 rounded-xl bg-gradient-to-tr from-blue-600 to-cyan-400 text-white"
                                    :disabled="
                                        auth.loading ||
                                        resetForm.password !==
                                            resetForm.password_confirmation
                                    "
                                >
                                    {{
                                        auth.loading
                                            ? "Restableciendo…"
                                            : "Restablecer contraseña"
                                    }}
                                </button>

                                <p
                                    v-if="
                                        resetForm.password &&
                                        resetForm.password_confirmation &&
                                        resetForm.password !==
                                            resetForm.password_confirmation
                                    "
                                    class="text-rose-500 text-xs"
                                >
                                    Las contraseñas no coinciden
                                </p>

                                <p
                                    v-if="auth.error"
                                    class="text-rose-500 text-sm"
                                >
                                    {{ friendlyError(auth.error) }}
                                </p>
                            </form>

                            <form
                                v-if="mode === 'register'"
                                class="grid gap-3"
                                @submit.prevent="doRegister"
                            >
                                <div class="grid sm:grid-cols-2 gap-3">
                                    <input
                                        v-model.trim="reg.first_name"
                                        placeholder="Nombre"
                                        class="h-11 rounded-xl border px-3 bg-transparent"
                                    />
                                    <input
                                        v-model.trim="reg.last_name"
                                        placeholder="Apellido"
                                        class="h-11 rounded-xl border px-3 bg-transparent"
                                    />
                                </div>
                                <div class="grid sm:grid-cols-2 gap-3">
                                    <input
                                        v-model.trim="reg.dni"
                                        placeholder="Documento (DNI / Cédula)"
                                        class="h-11 rounded-xl border px-3 bg-transparent"
                                    />

                                    <input
                                        v-model.trim="reg.username"
                                        placeholder="Usuario (opcional)"
                                        class="h-11 rounded-xl border px-3 bg-transparent"
                                    />
                                </div>
                                <div class="grid sm:grid-cols-2 gap-3">
                                    <input
                                        v-model="reg.birth_date"
                                        type="date"
                                        title="Fecha de Nacimiento"
                                        class="h-11 rounded-xl border px-3 bg-transparent"
                                    />
                                    <select
                                        v-model="reg.gender"
                                        class="h-11 rounded-xl border px-3 bg-transparent"
                                    >
                                        <option value="">
                                            Género (opcional)
                                        </option>
                                        <option value="M">Masculino</option>
                                        <option value="F">Femenino</option>
                                        <option value="X">No binario</option>
                                    </select>
                                </div>
                                <input
                                    v-model.trim="reg.billing_address"
                                    placeholder="Dirección de facturación (opcional)"
                                    class="h-11 rounded-xl border px-3 bg-transparent"
                                />
                                <input
                                    v-model.trim="reg.email"
                                    type="email"
                                    placeholder="Email"
                                    class="h-11 rounded-xl border px-3 bg-transparent"
                                />
                                <div class="grid sm:grid-cols-2 gap-3">
                                    <input
                                        v-model="reg.password"
                                        type="password"
                                        placeholder="Contraseña"
                                        class="h-11 rounded-xl border px-3 bg-transparent"
                                    />

                                    <input
                                        v-model="reg.password_confirmation"
                                        type="password"
                                        placeholder="Confirmar contraseña"
                                        class="h-11 rounded-xl border px-3 bg-transparent"
                                    />
                                </div>
                                <label
                                    class="inline-flex items-center gap-2 text-sm text-slate-600"
                                >
                                    <input
                                        type="checkbox"
                                        v-model="reg.news_opt_in"
                                        class="rounded"
                                    />
                                    Quiero recibir noticias y promociones
                                </label>

                                <button
                                    class="h-11 rounded-xl bg-gradient-to-tr from-blue-600 to-cyan-400 text-white"
                                    :disabled="auth.loading"
                                >
                                    {{
                                        auth.loading
                                            ? "Creando…"
                                            : "Crear cuenta"
                                    }}
                                </button>

                                <ul
                                    v-if="regErrorsList.length"
                                    class="text-rose-500 text-sm list-disc pl-5"
                                >
                                    <li
                                        v-for="(e, i) in regErrorsList"
                                        :key="i"
                                    >
                                        {{ e }}
                                    </li>
                                </ul>
                            </form>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import { reactive, computed, watch, ref } from "vue";
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionRoot,
    TransitionChild,
} from "@headlessui/vue";
import { CheckIcon } from "lucide-vue-next";
import { useAuth } from "../../stores/auth";
import { useUi } from "../../stores/ui";
import { useRouter } from "vue-router";

const props = defineProps({
    open: Boolean,
    mode: { type: String, default: "login" },
});
const emit = defineEmits(["update:open"]);

const auth = useAuth();
const ui = useUi();
const router = useRouter();

const login = reactive({ email: "", password: "" });
const reg = reactive({
    first_name: "",
    last_name: "",
    dni: "",
    username: "",
    birth_date: "",
    gender: "",
    billing_address: "",
    email: "",
    password: "",
    password_confirmation: "",
    news_opt_in: false,
});

// Variables para recuperación de contraseña
const forgotEmail = ref("");
const forgotPasswordSent = ref(false);
const resetToken = ref("");
const userInfo = ref(null);
const resetForm = reactive({
    password: "",
    password_confirmation: "",
});

// Limpia los errores al cambiar de modo
watch(
    () => props.mode,
    () => {
        auth.error = null;
        // Resetear estados cuando se cambia de modo
        if (props.mode === "login") {
            forgotPasswordSent.value = false;
            resetToken.value = "";
        }
    }
);

function close() {
    emit("update:open", false);
    // Limpiar estados al cerrar
    forgotPasswordSent.value = false;
    resetToken.value = "";
    userInfo.value = null;
    forgotEmail.value = "";
    resetForm.password = "";
    resetForm.password_confirmation = "";
}
function switchMode(m) {
    ui.openAuth(m);
}
function tabClass(m) {
    return (
        (props.mode === m ? "bg-slate-100 " : "bg-transparent") +
        " transition-colors"
    );
}

async function doLogin() {
    try {
        const result = await auth.login(login);
        close();
        
        // Si es un admin que necesita completar el registro, redirigir
        if (result.requires_completion) {
            router.push('/admin/complete-registration');
        } else {
            window.location.reload(); // recargar para actualizar estado
        }
    } catch {}
}

async function doRegister() {
    try {
        await auth.register(reg);
        // Si el registro es exitoso, intenta hacer login automáticamente
        await auth.login({ email: reg.email, password: reg.password });
        close();
        window.location.reload(); // recargar para actualizar estado
    } catch {}
}

async function doForgotPassword() {
    try {
        const response = await auth.forgotPassword(forgotEmail.value);
        forgotPasswordSent.value = true;
        // Guardar información del usuario
        if (response.user_info) {
            userInfo.value = response.user_info;
        }
        // En desarrollo, guardar el token para testing
        if (response.reset_token) {
            resetToken.value = response.reset_token;
        }
    } catch (error) {
        console.error("Error al enviar recuperación:", error);
    }
}

function showResetForm() {
    ui.openAuth("reset-password");
}

async function doResetPassword() {
    try {
        const response = await auth.resetPassword({
            token: resetToken.value,
            email: forgotEmail.value,
            password: resetForm.password,
            password_confirmation: resetForm.password_confirmation,
        });

        // Mostrar notificación de éxito más elegante
        const successMessage = userInfo.value
            ? `Contraseña restablecida correctamente para ${userInfo.value.name} (${userInfo.value.role}). Por favor, inicia sesión.`
            : "Contraseña restablecida correctamente. Por favor, inicia sesión.";

        // Crear una notificación visual mejor
        const notification = document.createElement("div");
        notification.className =
            "fixed top-4 right-4 bg-green-500 text-white p-4 rounded-lg shadow-lg z-[100]";
        notification.innerHTML = `
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <span>¡Contraseña restablecida correctamente!</span>
            </div>
        `;
        document.body.appendChild(notification);

        // Remover notificación después de 5 segundos
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 5000);

        // Limpiar formularios
        resetForm.password = "";
        resetForm.password_confirmation = "";
        forgotEmail.value = "";
        forgotPasswordSent.value = false;
        resetToken.value = "";
        userInfo.value = null;

        // Cambiar a modo login
        ui.openAuth("login");
    } catch (error) {
        console.error("Error al restablecer contraseña:", error);
    }
}

const regErrorsList = computed(() => {
    const e = auth.error;
    if (!e || !e.errors) return [];
    return Object.values(e.errors).flat();
});

function friendlyError(e) {
    if (!e) return "";
    if (typeof e === "string") return e;
    return e.error || e.message || "Error desconocido";
}
</script>
