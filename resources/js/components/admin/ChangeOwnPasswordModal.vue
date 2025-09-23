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
                            class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all"
                        >
                            <DialogTitle
                                as="h3"
                                class="text-lg font-medium leading-6 text-gray-900 mb-4"
                            >
                                Cambiar Mi Contraseña
                            </DialogTitle>

                            <form
                                @submit.prevent="submitForm"
                                class="space-y-4"
                            >
                                <!-- Contraseña actual -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Contraseña Actual *
                                    </label>
                                    <div class="relative">
                                        <input
                                            v-model="form.current_password"
                                            :type="showCurrentPassword ? 'text' : 'password'"
                                            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                            :class="{
                                                'border-red-500': errors.current_password,
                                            }"
                                            placeholder="Ingresa tu contraseña actual"
                                        />
                                        <button
                                            type="button"
                                            @click="showCurrentPassword = !showCurrentPassword"
                                            class="absolute inset-y-0 right-0 flex items-center pr-3"
                                        >
                                            <component :is="showCurrentPassword ? EyeOff : Eye" class="w-4 h-4 text-gray-500" />
                                        </button>
                                    </div>
                                    <p
                                        v-if="errors.current_password"
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        {{ errors.current_password[0] }}
                                    </p>
                                </div>

                                <!-- Nueva contraseña -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Nueva Contraseña *
                                    </label>
                                    <div class="relative">
                                        <input
                                            v-model="form.new_password"
                                            :type="showNewPassword ? 'text' : 'password'"
                                            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                            :class="{
                                                'border-red-500': errors.new_password,
                                            }"
                                            placeholder="Mínimo 8 caracteres"
                                        />
                                        <button
                                            type="button"
                                            @click="showNewPassword = !showNewPassword"
                                            class="absolute inset-y-0 right-0 flex items-center pr-3"
                                        >
                                            <component :is="showNewPassword ? EyeOff : Eye" class="w-4 h-4 text-gray-500" />
                                        </button>
                                    </div>
                                    <p
                                        v-if="errors.new_password"
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        {{ errors.new_password[0] }}
                                    </p>
                                </div>

                                <!-- Confirmar nueva contraseña -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Confirmar Nueva Contraseña *
                                    </label>
                                    <div class="relative">
                                        <input
                                            v-model="form.new_password_confirmation"
                                            :type="showConfirmPassword ? 'text' : 'password'"
                                            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                            :class="{
                                                'border-red-500': errors.new_password_confirmation,
                                            }"
                                            placeholder="Repite la nueva contraseña"
                                        />
                                        <button
                                            type="button"
                                            @click="showConfirmPassword = !showConfirmPassword"
                                            class="absolute inset-y-0 right-0 flex items-center pr-3"
                                        >
                                            <component :is="showConfirmPassword ? EyeOff : Eye" class="w-4 h-4 text-gray-500" />
                                        </button>
                                    </div>
                                    <p
                                        v-if="errors.new_password_confirmation"
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        {{ errors.new_password_confirmation[0] }}
                                    </p>
                                </div>

                                <!-- Indicador de fortaleza de contraseña -->
                                <div v-if="form.new_password" class="space-y-2">
                                    <div class="text-sm font-medium text-gray-700">
                                        Fortaleza de la contraseña:
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div
                                            class="h-2 rounded-full transition-all duration-300"
                                            :class="passwordStrengthClass"
                                            :style="`width: ${passwordStrengthPercent}%`"
                                        ></div>
                                    </div>
                                    <p class="text-xs" :class="passwordStrengthTextClass">
                                        {{ passwordStrengthText }}
                                    </p>
                                </div>

                                <!-- Botones -->
                                <div class="flex justify-end gap-3 mt-6">
                                    <button
                                        type="button"
                                        @click="closeModal"
                                        class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                                        :disabled="loading"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="submit"
                                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                        :disabled="loading || !formValid"
                                    >
                                        <div
                                            v-if="loading"
                                            class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"
                                        ></div>
                                        Cambiar Contraseña
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
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
} from "@headlessui/vue";
import { Eye, EyeOff } from "lucide-vue-next";
import { api } from "../../lib/api";

// Props & Emits
const props = defineProps({
    open: Boolean,
});

const emit = defineEmits(["update:open", "changed"]);

// Estado del formulario
const form = reactive({
    current_password: "",
    new_password: "",
    new_password_confirmation: "",
});

const loading = ref(false);
const errors = ref({});

// Estados para mostrar/ocultar contraseñas
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

// Validación del formulario
const formValid = computed(() => {
    return (
        form.current_password &&
        form.new_password &&
        form.new_password_confirmation &&
        form.new_password === form.new_password_confirmation &&
        form.new_password.length >= 8
    );
});

// Fortaleza de la contraseña
const passwordStrength = computed(() => {
    const password = form.new_password;
    if (!password) return 0;

    let score = 0;
    
    // Longitud
    if (password.length >= 8) score += 25;
    if (password.length >= 12) score += 25;
    
    // Caracteres
    if (/[a-z]/.test(password)) score += 12.5;
    if (/[A-Z]/.test(password)) score += 12.5;
    if (/[0-9]/.test(password)) score += 12.5;
    if (/[^A-Za-z0-9]/.test(password)) score += 12.5;
    
    return Math.min(100, score);
});

const passwordStrengthPercent = computed(() => passwordStrength.value);

const passwordStrengthClass = computed(() => {
    const strength = passwordStrength.value;
    if (strength < 25) return "bg-red-500";
    if (strength < 50) return "bg-orange-500";
    if (strength < 75) return "bg-yellow-500";
    return "bg-green-500";
});

const passwordStrengthTextClass = computed(() => {
    const strength = passwordStrength.value;
    if (strength < 25) return "text-red-600";
    if (strength < 50) return "text-orange-600";
    if (strength < 75) return "text-yellow-600";
    return "text-green-600";
});

const passwordStrengthText = computed(() => {
    const strength = passwordStrength.value;
    if (strength < 25) return "Muy débil";
    if (strength < 50) return "Débil";
    if (strength < 75) return "Buena";
    return "Muy fuerte";
});

// Métodos
const closeModal = () => {
    emit("update:open", false);
    resetForm();
};

const resetForm = () => {
    Object.keys(form).forEach((key) => {
        form[key] = "";
    });
    errors.value = {};
    showCurrentPassword.value = false;
    showNewPassword.value = false;
    showConfirmPassword.value = false;
};

const submitForm = async () => {
    loading.value = true;
    errors.value = {};

    try {
        const { data } = await api.post("/admin/change-own-password", {
            current_password: form.current_password,
            new_password: form.new_password,
            new_password_confirmation: form.new_password_confirmation,
        });

        if (data.status === "success") {
            emit("changed");
            closeModal();
        }
    } catch (error) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        } else if (error.response?.data?.message) {
            alert(`Error: ${error.response.data.message}`);
        } else {
            alert("Error al cambiar la contraseña");
        }
    } finally {
        loading.value = false;
    }
};

// Limpiar formulario cuando se cierra el modal
watch(() => props.open, (newVal) => {
    if (!newVal) {
        resetForm();
    }
});
</script>