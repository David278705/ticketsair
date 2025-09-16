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
                            class="w-full max-w-lg transform overflow-hidden rounded-2xl bg-white dark:bg-slate-950 p-6 shadow-xl transition-all"
                        >
                            <div class="flex items-center justify-between mb-3">
                                <DialogTitle class="text-lg font-semibold">
                                    {{
                                        mode === "login"
                                            ? "Iniciar sesión"
                                            : "Crear cuenta"
                                    }}
                                </DialogTitle>
                                <button
                                    class="size-9 grid place-items-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-900"
                                    @click="close"
                                >
                                    ✕
                                </button>
                            </div>

                            <div class="mb-4">
                                <nav
                                    class="inline-flex rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden"
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
                                <p
                                    v-if="auth.error"
                                    class="text-rose-500 text-sm"
                                >
                                    {{ friendlyError(auth.error) }}
                                </p>
                            </form>

                            <form
                                v-else
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
                                    class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300"
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
import { reactive, computed, watch } from "vue";
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionRoot,
    TransitionChild,
} from "@headlessui/vue";
import { useAuth } from "../../stores/auth";
import { useUi } from "../../stores/ui";

const props = defineProps({
    open: Boolean,
    mode: { type: String, default: "login" },
});
const emit = defineEmits(["update:open"]);

const auth = useAuth();
const ui = useUi();

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

// Limpia los errores al cambiar de modo
watch(
    () => props.mode,
    () => {
        auth.error = null;
    }
);

function close() {
    emit("update:open", false);
}
function switchMode(m) {
    ui.openAuth(m);
}
function tabClass(m) {
    return (
        (props.mode === m
            ? "bg-slate-100 dark:bg-slate-900"
            : "bg-transparent") + " transition-colors"
    );
}

async function doLogin() {
    try {
        await auth.login(login);
        close();
        window.location.reload(); // recargar para actualizar estado
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
