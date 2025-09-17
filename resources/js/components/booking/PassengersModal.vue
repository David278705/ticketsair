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
                            class="w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white p-6 shadow-xl transition-all"
                        >
                            <DialogTitle class="text-lg font-semibold mb-4">
                                Datos de los pasajeros para el vuelo
                            </DialogTitle>

                            <div
                                class="space-y-3 max-h-[60vh] overflow-auto pr-2"
                            >
                                <div
                                    v-for="(p, idx) in passengers"
                                    :key="idx"
                                    class="grid grid-cols-2 gap-3 border rounded-xl p-4 relative"
                                >
                                    <input
                                        v-model="p.first_name"
                                        placeholder="Nombres"
                                        class="h-10 rounded-lg border px-3 bg-transparent col-span-2 sm:col-span-1"
                                    />
                                    <input
                                        v-model="p.last_name"
                                        placeholder="Apellidos"
                                        class="h-10 rounded-lg border px-3 bg-transparent col-span-2 sm:col-span-1"
                                    />
                                    <input
                                        v-model="p.dni"
                                        placeholder="Documento"
                                        class="h-10 rounded-lg border px-3 bg-transparent col-span-2 sm:col-span-1"
                                    />
                                    <input
                                        v-model="p.birth_date"
                                        type="date"
                                        title="Fecha de Nacimiento"
                                        class="h-10 rounded-lg border px-3 bg-transparent col-span-2 sm:col-span-1"
                                    />
                                    <select
                                        v-model="p.gender"
                                        class="h-10 rounded-lg border px-3 bg-transparent col-span-2 sm:col-span-1"
                                    >
                                        <option value="">Género</option>
                                        <option value="M">Masculino</option>
                                        <option value="F">Femenino</option>
                                        <option value="X">No binario</option>
                                    </select>
                                    <input
                                        v-model="p.email"
                                        type="email"
                                        placeholder="Email (Opcional)"
                                        class="h-10 rounded-lg border px-3 bg-transparent col-span-2 sm:col-span-1"
                                    />
                                    <button
                                        v-if="passengers.length > 1"
                                        @click="removePassenger(idx)"
                                        class="absolute top-2 right-2 text-rose-500 hover:text-rose-700 transition-colors"
                                        title="Eliminar pasajero"
                                    >
                                        ✕
                                    </button>
                                </div>
                            </div>

                            <div class="mt-4 flex items-center justify-between">
                                <button
                                    class="h-10 rounded-xl border px-4 text-sm hover:bg-slate-100"
                                    @click="addPassenger"
                                    :disabled="passengers.length >= 5"
                                >
                                    + Añadir pasajero
                                </button>
                                <p v-if="error" class="text-sm text-rose-600">
                                    {{ error }}
                                </p>
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <button
                                    class="h-11 rounded-xl border px-5"
                                    @click="close"
                                >
                                    Cancelar
                                </button>
                                <button
                                    class="h-11 rounded-xl bg-blue-600 text-white px-5"
                                    @click="submit"
                                >
                                    Confirmar
                                </button>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import { ref, watch } from "vue";
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionRoot,
    TransitionChild,
} from "@headlessui/vue";

const props = defineProps({
    open: Boolean,
});
const emit = defineEmits(["update:open", "submit"]);

const passengers = ref([createBlankPassenger()]);
const error = ref("");

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            // Resetear al abrir
            passengers.value = [createBlankPassenger()];
            error.value = "";
        }
    }
);

function createBlankPassenger() {
    return {
        dni: "",
        first_name: "",
        last_name: "",
        birth_date: "",
        gender: "",
        email: "",
    };
}

function addPassenger() {
    if (passengers.value.length < 5) {
        passengers.value.push(createBlankPassenger());
    }
}

function removePassenger(index) {
    passengers.value.splice(index, 1);
}

function close() {
    emit("update:open", false);
}

function getAge(dateString) {
    if (!dateString) return 999;
    const birthDate = new Date(dateString);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age;
}

function submit() {
    // Validar campos vacíos
    for (const p of passengers.value) {
        if (
            !p.dni ||
            !p.first_name ||
            !p.last_name ||
            !p.birth_date ||
            !p.gender
        ) {
            error.value = "Todos los campos de los pasajeros son obligatorios.";
            return;
        }
    }

    // Validar menores
    const hasMinor = passengers.value.some((p) => getAge(p.birth_date) < 18);
    const hasAdult = passengers.value.some((p) => getAge(p.birth_date) >= 18);

    if (hasMinor && !hasAdult) {
        error.value = "Los menores de edad no pueden viajar solos.";
        return;
    }

    error.value = "";
    emit("submit", passengers.value);
    close();
}
</script>
