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
                            class="w-full max-w-3xl transform overflow-hidden rounded-2xl bg-white p-6 shadow-xl transition-all"
                        >
                            <DialogTitle class="text-xl font-bold mb-2">
                                Datos de los pasajeros
                            </DialogTitle>
                            <p class="text-sm text-gray-500 mb-4">
                                Complete la información de cada pasajero y
                                seleccione su clase de vuelo
                            </p>

                            <div
                                class="space-y-4 max-h-[65vh] overflow-auto pr-2"
                            >
                                <div
                                    v-for="(p, idx) in passengers"
                                    :key="idx"
                                    class="border-2 rounded-xl p-5 relative bg-gradient-to-br from-gray-50 to-white"
                                >
                                    <!-- Header del pasajero -->
                                    <div
                                        class="flex items-center justify-between mb-4"
                                    >
                                        <h3
                                            class="font-bold text-gray-900 text-lg"
                                        >
                                            👤 Pasajero {{ idx + 1 }}
                                        </h3>
                                        <button
                                            v-if="passengers.length > 1"
                                            @click="removePassenger(idx)"
                                            class="text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-full p-1 transition-colors"
                                            title="Eliminar pasajero"
                                        >
                                            <svg
                                                class="w-5 h-5"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"
                                                />
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Campos del pasajero -->
                                    <div class="grid grid-cols-2 gap-3 mb-4">
                                        <input
                                            v-model="p.first_name"
                                            placeholder="Nombres *"
                                            class="h-11 rounded-lg border border-gray-300 px-3 bg-white col-span-2 sm:col-span-1 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                        />
                                        <input
                                            v-model="p.last_name"
                                            placeholder="Apellidos *"
                                            class="h-11 rounded-lg border border-gray-300 px-3 bg-white col-span-2 sm:col-span-1 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                        />
                                        <div class="col-span-2 sm:col-span-1">
                                            <input
                                                v-model="p.dni"
                                                placeholder="Documento *"
                                                @input="checkDuplicateDni"
                                                :class="[
                                                    'h-11 rounded-lg border px-3 bg-white w-full focus:ring-2',
                                                    isDniDuplicate(p.dni, idx)
                                                        ? 'border-red-500 focus:border-red-500 focus:ring-red-200'
                                                        : 'border-gray-300 focus:border-blue-500 focus:ring-blue-200',
                                                ]"
                                            />
                                            <p
                                                v-if="
                                                    isDniDuplicate(p.dni, idx)
                                                "
                                                class="text-xs text-red-600 mt-1"
                                            >
                                                ⚠️ Cédula duplicada
                                            </p>
                                        </div>
                                        <input
                                            v-model="p.birth_date"
                                            type="date"
                                            title="Fecha de Nacimiento *"
                                            class="h-11 rounded-lg border border-gray-300 px-3 bg-white col-span-2 sm:col-span-1 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                        />
                                        <select
                                            v-model="p.gender"
                                            class="h-11 rounded-lg border border-gray-300 px-3 bg-white col-span-2 sm:col-span-1 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                        >
                                            <option value="">Género *</option>
                                            <option value="M">Masculino</option>
                                            <option value="F">Femenino</option>
                                            <option value="X">
                                                No binario
                                            </option>
                                        </select>
                                        <div class="col-span-2 sm:col-span-1">
                                            <input
                                                v-model="p.email"
                                                type="email"
                                                placeholder="Email *"
                                                required
                                                @input="checkDuplicateEmail"
                                                :class="[
                                                    'h-11 rounded-lg border px-3 bg-white w-full focus:ring-2',
                                                    isEmailDuplicate(
                                                        p.email,
                                                        idx
                                                    )
                                                        ? 'border-red-500 focus:border-red-500 focus:ring-red-200'
                                                        : 'border-blue-300 focus:border-blue-500 focus:ring-blue-200',
                                                ]"
                                            />
                                            <p
                                                v-if="
                                                    isEmailDuplicate(
                                                        p.email,
                                                        idx
                                                    )
                                                "
                                                class="text-xs text-red-600 mt-1"
                                            >
                                                ⚠️ Email duplicado
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Selección de Clase -->
                                    <div class="pt-4 border-t border-gray-200">
                                        <label
                                            class="block text-sm font-semibold text-gray-700 mb-3"
                                        >
                                            ✈️ Clase de Vuelo para este pasajero
                                        </label>
                                        <div class="grid grid-cols-2 gap-3">
                                            <!-- Económica -->
                                            <button
                                                type="button"
                                                @click="
                                                    p.flight_class = 'economy'
                                                "
                                                :class="[
                                                    'p-4 rounded-xl border-2 transition-all text-left',
                                                    p.flight_class === 'economy'
                                                        ? 'border-blue-600 bg-blue-50 ring-2 ring-blue-200'
                                                        : 'border-gray-300 hover:border-blue-300 hover:bg-gray-50',
                                                ]"
                                            >
                                                <div
                                                    class="flex items-center justify-between mb-2"
                                                >
                                                    <span
                                                        class="font-bold text-gray-900"
                                                        >💺 Económica</span
                                                    >
                                                    <svg
                                                        v-if="
                                                            p.flight_class ===
                                                            'economy'
                                                        "
                                                        class="w-5 h-5 text-blue-600"
                                                        fill="currentColor"
                                                        viewBox="0 0 20 20"
                                                    >
                                                        <path
                                                            fill-rule="evenodd"
                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                            clip-rule="evenodd"
                                                        />
                                                    </svg>
                                                </div>
                                                <p
                                                    class="text-sm text-gray-600 mb-2"
                                                >
                                                    Asiento estándar
                                                </p>
                                                <p
                                                    class="text-lg font-bold text-blue-600"
                                                >
                                                    ${{
                                                        formatPrice(
                                                            basePricePerSeat
                                                        )
                                                    }}
                                                </p>
                                            </button>

                                            <!-- Primera Clase -->
                                            <button
                                                type="button"
                                                @click="
                                                    p.flight_class = 'first'
                                                "
                                                :class="[
                                                    'p-4 rounded-xl border-2 transition-all text-left',
                                                    p.flight_class === 'first'
                                                        ? 'border-amber-600 bg-amber-50 ring-2 ring-amber-200'
                                                        : 'border-gray-300 hover:border-amber-300 hover:bg-gray-50',
                                                ]"
                                            >
                                                <div
                                                    class="flex items-center justify-between mb-2"
                                                >
                                                    <span
                                                        class="font-bold text-gray-900"
                                                        >⭐ Primera</span
                                                    >
                                                    <svg
                                                        v-if="
                                                            p.flight_class ===
                                                            'first'
                                                        "
                                                        class="w-5 h-5 text-amber-600"
                                                        fill="currentColor"
                                                        viewBox="0 0 20 20"
                                                    >
                                                        <path
                                                            fill-rule="evenodd"
                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                            clip-rule="evenodd"
                                                        />
                                                    </svg>
                                                </div>
                                                <p
                                                    class="text-sm text-gray-600 mb-2"
                                                >
                                                    Servicio VIP
                                                </p>
                                                <p
                                                    class="text-lg font-bold text-amber-600"
                                                >
                                                    ${{
                                                        formatPrice(
                                                            firstClassPrice
                                                        )
                                                    }}
                                                </p>
                                            </button>
                                        </div>
                                    </div>
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
import { useCurrency } from "../../composables/useCurrency";

const { formatPrice: formatPriceCurrency } = useCurrency();

const props = defineProps({
    open: Boolean,
    passengersCount: {
        type: Number,
        default: 1,
    },
    basePricePerSeat: {
        type: Number,
        default: 0,
    },
    firstClassPrice: {
        type: Number,
        default: 0,
    },
});
const emit = defineEmits(["update:open", "submit"]);

const passengers = ref([createBlankPassenger()]);
const error = ref("");

// Formatear precio usando el composable de monedas
const formatPrice = (price) => {
    return formatPriceCurrency(Number(price));
};

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            // Resetear al abrir con el número correcto de pasajeros
            const count = props.passengersCount || 1;
            passengers.value = Array.from({ length: count }, () =>
                createBlankPassenger()
            );
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
        flight_class: "economy", // Por defecto económica
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

// Función para verificar si un DNI está duplicado
function isDniDuplicate(dni, currentIndex) {
    if (!dni || dni.trim() === "") return false;
    const normalized = dni.trim().toLowerCase();
    return passengers.value.some(
        (p, idx) =>
            idx !== currentIndex &&
            p.dni &&
            p.dni.trim().toLowerCase() === normalized
    );
}

// Función para verificar si un email está duplicado
function isEmailDuplicate(email, currentIndex) {
    if (!email || email.trim() === "") return false;
    const normalized = email.trim().toLowerCase();
    return passengers.value.some(
        (p, idx) =>
            idx !== currentIndex &&
            p.email &&
            p.email.trim().toLowerCase() === normalized
    );
}

// Trigger manual para re-renderizar cuando cambian los valores
function checkDuplicateDni() {
    // Forzar re-evaluación de la validación
}

function checkDuplicateEmail() {
    // Forzar re-evaluación de la validación
}

function submit() {
    // Validar campos vacíos
    for (const p of passengers.value) {
        if (
            !p.dni ||
            !p.first_name ||
            !p.last_name ||
            !p.birth_date ||
            !p.gender ||
            !p.email
        ) {
            error.value =
                "Todos los campos son obligatorios, incluyendo el email.";
            return;
        }

        // Validar formato de email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(p.email)) {
            error.value = `El email "${p.email}" no es válido.`;
            return;
        }

        // Validar que se haya seleccionado una clase
        if (!p.flight_class) {
            error.value = `Por favor selecciona una clase para ${
                p.first_name || "el pasajero"
            }.`;
            return;
        }
    }

    // Validar cédulas duplicadas dentro de la misma reserva
    const dniCounts = {};
    for (const p of passengers.value) {
        const dniNormalized = p.dni.trim().toLowerCase();
        if (dniCounts[dniNormalized]) {
            error.value = `La cédula "${p.dni}" está duplicada. Cada pasajero debe tener un documento único.`;
            return;
        }
        dniCounts[dniNormalized] = true;
    }

    // Validar emails duplicados dentro de la misma reserva
    const emailCounts = {};
    for (const p of passengers.value) {
        const emailNormalized = p.email.trim().toLowerCase();
        if (emailCounts[emailNormalized]) {
            error.value = `El email "${p.email}" está duplicado. Cada pasajero debe tener un email único.`;
            return;
        }
        emailCounts[emailNormalized] = true;
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
