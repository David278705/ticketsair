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
                                Editar Usuario
                            </DialogTitle>

                            <form
                                @submit.prevent="submitForm"
                                class="space-y-4"
                                v-if="user"
                            >
                                <!-- Email -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Email
                                    </label>
                                    <input
                                        v-model="form.email"
                                        type="email"
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

                                <!-- Nombre -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-1"
                                        >
                                            Nombre
                                        </label>
                                        <input
                                            v-model="form.first_name"
                                            type="text"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            :class="{
                                                'border-red-500':
                                                    errors.first_name,
                                            }"
                                        />
                                        <p
                                            v-if="errors.first_name"
                                            class="text-red-500 text-xs mt-1"
                                        >
                                            {{ errors.first_name[0] }}
                                        </p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-1"
                                        >
                                            Apellido
                                        </label>
                                        <input
                                            v-model="form.last_name"
                                            type="text"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            :class="{
                                                'border-red-500':
                                                    errors.last_name,
                                            }"
                                        />
                                        <p
                                            v-if="errors.last_name"
                                            class="text-red-500 text-xs mt-1"
                                        >
                                            {{ errors.last_name[0] }}
                                        </p>
                                    </div>
                                </div>

                                <!-- DNI -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        DNI
                                    </label>
                                    <input
                                        v-model="form.dni"
                                        type="text"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        :class="{
                                            'border-red-500': errors.dni,
                                        }"
                                    />
                                    <p
                                        v-if="errors.dni"
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        {{ errors.dni[0] }}
                                    </p>
                                </div>

                                <!-- Fecha de nacimiento -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Fecha de nacimiento
                                    </label>
                                    <input
                                        v-model="form.birth_date"
                                        type="date"
                                        :min="minDate"
                                        :max="maxDate"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        :class="{
                                            'border-red-500': errors.birth_date,
                                        }"
                                    />
                                    <p
                                        v-if="errors.birth_date"
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        {{ errors.birth_date[0] }}
                                    </p>
                                </div>

                                <!-- Género -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Género
                                    </label>
                                    <select
                                        v-model="form.gender"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    >
                                        <option value="">Seleccionar</option>
                                        <option value="M">Masculino</option>
                                        <option value="F">Femenino</option>
                                        <option value="X">Otro</option>
                                    </select>
                                </div>

                                <!-- Username -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Nombre de usuario
                                    </label>
                                    <input
                                        v-model="form.username"
                                        type="text"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        :class="{
                                            'border-red-500': errors.username,
                                        }"
                                    />
                                    <p
                                        v-if="errors.username"
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        {{ errors.username[0] }}
                                    </p>
                                </div>

                                <!-- Dirección de facturación -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Dirección de facturación
                                    </label>
                                    <textarea
                                        v-model="form.billing_address"
                                        rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        :class="{
                                            'border-red-500':
                                                errors.billing_address,
                                        }"
                                    />
                                    <p
                                        v-if="errors.billing_address"
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        {{ errors.billing_address[0] }}
                                    </p>
                                </div>

                                <div class="flex justify-end gap-3 mt-6">
                                    <button
                                        type="button"
                                        @click="closeModal"
                                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="loading"
                                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                                    >
                                        {{
                                            loading
                                                ? "Actualizando..."
                                                : "Actualizar Usuario"
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
import { api } from "../../lib/api";

const props = defineProps({
    open: Boolean,
    user: Object,
});

const emit = defineEmits(["update:open", "update:user", "updated"]);

const loading = ref(false);
const errors = ref({});

const form = reactive({
    email: "",
    first_name: "",
    last_name: "",
    dni: "",
    birth_date: "",
    gender: "",
    username: "",
    billing_address: "",
});

const maxDate = computed(() => {
    const today = new Date();
    today.setFullYear(today.getFullYear() - 18);
    return today.toISOString().split("T")[0];
});

const minDate = computed(() => {
    const eightyYearsAgo = new Date();
    eightyYearsAgo.setFullYear(eightyYearsAgo.getFullYear() - 80);
    return eightyYearsAgo.toISOString().split('T')[0];
});

const closeModal = () => {
    emit("update:open", false);
    emit("update:user", null);
    errors.value = {};
};

const populateForm = () => {
    if (props.user) {
        form.email = props.user.email || "";
        form.first_name = props.user.first_name || "";
        form.last_name = props.user.last_name || "";
        form.dni = props.user.dni || "";
        form.birth_date = props.user.birth_date || "";
        form.gender = props.user.gender || "";
        form.username = props.user.username || "";
        form.billing_address = props.user.billing_address || "";
    }
};

const submitForm = async () => {
    if (!props.user) return;

    loading.value = true;
    errors.value = {};

    try {
        // Solo enviar campos que han cambiado
        const changedFields = {};
        Object.keys(form).forEach((key) => {
            if (form[key] !== (props.user[key] || "")) {
                changedFields[key] = form[key];
            }
        });

        if (Object.keys(changedFields).length === 0) {
            closeModal();
            return;
        }

        const { data } = await api.put(
            `/admin/users/${props.user.id}/credentials`,
            changedFields
        );

        if (data.status === "success") {
            emit("updated", data.data);
            closeModal();
            alert("Usuario actualizado exitosamente");
        }
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        } else {
            alert(
                error.response?.data?.message || "Error al actualizar usuario"
            );
        }
    } finally {
        loading.value = false;
    }
};

watch(
    () => props.user,
    () => {
        populateForm();
    },
    { immediate: true }
);

watch(
    () => props.open,
    (newVal) => {
        if (newVal) {
            populateForm();
        } else {
            errors.value = {};
        }
    }
);
</script>
