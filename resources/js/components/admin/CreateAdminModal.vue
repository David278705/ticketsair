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
                                Invitar Administrador
                            </DialogTitle>

                            <form
                                @submit.prevent="submitForm"
                                class="space-y-4"
                            >
                                <!-- Email -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Email *
                                    </label>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        required
                                        placeholder="admin@ticketsair.com"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        :class="{
                                            'border-red-500': errors.email,
                                        }"
                                    />
                                    <p
                                        v-if="errors.email"
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        {{ errors.email[0] }}
                                    </p>
                                </div>

                                <!-- Nombre Completo -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Nombre Completo *
                                    </label>
                                    <input
                                        v-model="form.full_name"
                                        type="text"
                                        required
                                        placeholder="Juan Pérez García"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        :class="{
                                            'border-red-500': errors.full_name,
                                        }"
                                    />
                                    <p
                                        v-if="errors.full_name"
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        {{ errors.full_name[0] }}
                                    </p>
                                </div>

                                <!-- Información -->
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div class="flex items-start">
                                        <Info class="w-5 h-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" />
                                        <div class="text-sm text-blue-700">
                                            <p class="font-medium mb-1">¿Qué sucederá después?</p>
                                            <ul class="list-disc list-inside space-y-1">
                                                <li>Se enviará una invitación por email</li>
                                                <li>Incluirá credenciales temporales (válidas 24h)</li>
                                                <li>El administrador completará su registro</li>
                                                <li>Podrá acceder al panel una vez completado</li>
                                            </ul>
                                        </div>
                                    </div>
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
                                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                        :disabled="loading"
                                    >
                                        <div
                                            v-if="loading"
                                            class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"
                                        ></div>
                                        {{ loading ? 'Enviando...' : 'Enviar Invitación' }}
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
import { ref, reactive, watch } from "vue";
import {
    TransitionRoot,
    TransitionChild,
    Dialog,
    DialogPanel,
    DialogTitle,
} from "@headlessui/vue";
import { Info } from "lucide-vue-next";
import { api } from "../../lib/api";

const props = defineProps({
    open: Boolean,
});

const emit = defineEmits(["update:open", "created"]);

const loading = ref(false);
const errors = ref({});

const form = reactive({
    email: "",
    full_name: "",
});

const closeModal = () => {
    emit("update:open", false);
    resetForm();
};

const resetForm = () => {
    Object.keys(form).forEach((key) => {
        form[key] = "";
    });
    errors.value = {};
};

const submitForm = async () => {
    loading.value = true;
    errors.value = {};

    try {
        const { data } = await api.post("/admin/users/create-admin", form);

        if (data.status === "success") {
            emit("created", data.data);
            closeModal();
            alert(data.message);
        }
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        } else {
            alert(
                error.response?.data?.message || "Error al enviar invitación"
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
