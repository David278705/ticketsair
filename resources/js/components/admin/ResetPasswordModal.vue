<template>
    <TransitionRoot appear :show="open" as="template">
        <Dialog as="div" @close="closeModal" class="relative z-50">
            <TransitionChild
                as="template"
                enter="duration-300 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-200 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-black/25" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div
                    class="flex min-h-full items-center justify-center p-4 text-center"
                >
                    <TransitionChild
                        as="template"
                        enter="duration-300 ease-out"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="duration-200 ease-in"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel
                            class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-slate-800 p-6 text-left align-middle shadow-xl transition-all"
                        >
                            <DialogTitle
                                as="h3"
                                class="text-lg font-medium leading-6 text-gray-900 dark:text-white mb-4"
                            >
                                Restablecer Contraseña
                            </DialogTitle>

                            <div
                                v-if="user"
                                class="mb-4 p-3 bg-gray-50 dark:bg-slate-700 rounded-lg"
                            >
                                <p
                                    class="text-sm text-gray-600 dark:text-gray-300"
                                >
                                    Restableciendo contraseña para:
                                </p>
                                <p
                                    class="font-medium text-gray-900 dark:text-white"
                                >
                                    {{ user.first_name }}
                                    {{ user.last_name }} ({{ user.email }})
                                </p>
                            </div>

                            <form
                                @submit.prevent="submitForm"
                                class="space-y-4"
                            >
                                <!-- Nueva contraseña -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                    >
                                        Nueva contraseña *
                                    </label>
                                    <input
                                        v-model="form.password"
                                        type="password"
                                        required
                                        minlength="8"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
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
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                    >
                                        Confirmar nueva contraseña *
                                    </label>
                                    <input
                                        v-model="form.password_confirmation"
                                        type="password"
                                        required
                                        minlength="8"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
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

                                <!-- Advertencia -->
                                <div
                                    class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-700 rounded-lg p-3"
                                >
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <AlertTriangle
                                                class="h-5 w-5 text-yellow-400"
                                            />
                                        </div>
                                        <div class="ml-3">
                                            <p
                                                class="text-sm text-yellow-700 dark:text-yellow-200"
                                            >
                                                El usuario será notificado del
                                                cambio de contraseña y deberá
                                                iniciar sesión nuevamente.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end gap-3 mt-6">
                                    <button
                                        type="button"
                                        @click="closeModal"
                                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="
                                            loading ||
                                            passwordMismatch ||
                                            !form.password ||
                                            !form.password_confirmation
                                        "
                                        class="px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50"
                                    >
                                        {{
                                            loading
                                                ? "Restableciendo..."
                                                : "Restablecer Contraseña"
                                        }}
                                    </button>
                                </div>
                            </form>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import { ref, reactive, computed, watch } from "vue";
import {
    TransitionRoot,
    TransitionChild,
    Dialog,
    DialogPanel,
    DialogTitle,
} from "@headlessui/vue";
import { AlertTriangle } from "lucide-vue-next";
import { api } from "../../lib/api";

const props = defineProps({
    open: Boolean,
    user: Object,
});

const emit = defineEmits(["update:open", "update:user", "reset"]);

const loading = ref(false);
const errors = ref({});

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

const closeModal = () => {
    emit("update:open", false);
    emit("update:user", null);
    resetForm();
};

const resetForm = () => {
    form.password = "";
    form.password_confirmation = "";
    errors.value = {};
};

const submitForm = async () => {
    if (!props.user || passwordMismatch.value) return;

    loading.value = true;
    errors.value = {};

    try {
        const { data } = await api.post(
            `/admin/users/${props.user.id}/reset-password`,
            {
                password: form.password,
                password_confirmation: form.password_confirmation,
            }
        );

        if (data.status === "success") {
            emit("reset");
            closeModal();
        }
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        } else {
            alert(
                error.response?.data?.message ||
                    "Error al restablecer contraseña"
            );
        }
    } finally {
        loading.value = false;
    }
};

watch(
    () => props.open,
    (newVal) => {
        if (!newVal) {
            resetForm();
        }
    }
);
</script>
