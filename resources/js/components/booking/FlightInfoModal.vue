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
                            <DialogTitle
                                class="text-2xl font-bold mb-6 text-center"
                            >
                                {{
                                    actionType === "purchase"
                                        ? "Comprar Vuelo"
                                        : "Reservar Vuelo"
                                }}
                            </DialogTitle>

                            <div v-if="flight" class="space-y-6">
                                <!-- Información del Vuelo -->
                                <div
                                    class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-xl p-6 border-2 border-blue-200"
                                >
                                    <h3
                                        class="text-lg font-semibold text-gray-900 mb-4"
                                    >
                                        Información del Vuelo
                                    </h3>

                                    <div class="grid grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <p class="text-sm text-gray-500">
                                                Código de Vuelo
                                            </p>
                                            <p
                                                class="text-lg font-bold text-blue-900"
                                            >
                                                {{ flight.code }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">
                                                Tipo
                                            </p>
                                            <p class="text-lg font-semibold">
                                                {{
                                                    flight.scope ===
                                                    "international"
                                                        ? "🌍 Internacional"
                                                        : "🇨🇴 Nacional"
                                                }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Ruta -->
                                    <div
                                        class="flex items-center justify-between mb-4 p-4 bg-white/60 rounded-lg"
                                    >
                                        <div class="text-center">
                                            <p
                                                class="text-3xl font-bold text-blue-600"
                                            >
                                                {{ flight.origin?.code }}
                                            </p>
                                            <p class="text-sm text-gray-600">
                                                {{ flight.origin?.name }}
                                            </p>
                                        </div>
                                        <div class="flex-1 px-4">
                                            <svg
                                                class="w-full h-8 text-blue-400"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M17 8l4 4m0 0l-4 4m4-4H3"
                                                />
                                            </svg>
                                            <p
                                                class="text-center text-xs text-gray-500 mt-1"
                                            >
                                                ⏱️
                                                {{
                                                    formatDuration(
                                                        flight.duration_minutes
                                                    )
                                                }}
                                            </p>
                                        </div>
                                        <div class="text-center">
                                            <p
                                                class="text-3xl font-bold text-cyan-600"
                                            >
                                                {{ flight.destination?.code }}
                                            </p>
                                            <p class="text-sm text-gray-600">
                                                {{ flight.destination?.name }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Fecha y Hora -->
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="p-3 bg-white/60 rounded-lg">
                                            <p class="text-xs text-gray-500">
                                                🛫 Salida
                                            </p>
                                            <p class="text-sm font-semibold">
                                                {{
                                                    formatDate(
                                                        flight.departure_at
                                                    )
                                                }}
                                            </p>
                                            <p
                                                class="text-xs text-gray-600 mt-1"
                                            >
                                                {{
                                                    formatTime(
                                                        flight.departure_at
                                                    )
                                                }}
                                            </p>
                                        </div>
                                        <div class="p-3 bg-white/60 rounded-lg">
                                            <p class="text-xs text-gray-500">
                                                🛬 Llegada
                                                <span
                                                    v-if="
                                                        arrivalInfo?.timezone_abbr
                                                    "
                                                    class="font-medium"
                                                >
                                                    ({{
                                                        arrivalInfo.timezone_abbr
                                                    }})
                                                </span>
                                            </p>
                                            <p
                                                v-if="arrivalInfo"
                                                class="text-sm font-semibold"
                                                :class="
                                                    arrivalInfo.is_different_day
                                                        ? 'text-orange-600'
                                                        : ''
                                                "
                                            >
                                                {{
                                                    getArrivalDate(arrivalInfo)
                                                }}
                                            </p>
                                            <p
                                                v-if="arrivalInfo"
                                                class="text-xs text-gray-600 mt-1"
                                            >
                                                {{
                                                    getArrivalTime(arrivalInfo)
                                                }}
                                                <span
                                                    v-if="
                                                        arrivalInfo.is_different_day
                                                    "
                                                    class="text-orange-600 font-semibold ml-1"
                                                >
                                                    (+1 día)
                                                </span>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Precios por Clase -->
                                    <div class="grid grid-cols-2 gap-4 mt-4">
                                        <div class="p-3 bg-white/60 rounded-lg">
                                            <p class="text-xs text-gray-500">
                                                Clase Económica
                                            </p>
                                            <p class="text-lg font-bold text-blue-600">
                                                {{ formatCurrency(flight.price_per_seat) }}
                                            </p>
                                            <p v-if="flight.active_promotion" class="text-xs text-green-600 font-semibold">
                                                {{ flight.active_promotion.discount_percent }}% descuento
                                            </p>
                                        </div>
                                        <div class="p-3 bg-white/60 rounded-lg">
                                            <p class="text-xs text-gray-500">
                                                Primera Clase
                                            </p>
                                            <p class="text-lg font-bold text-cyan-600">
                                                {{ formatCurrency(flight.first_class_price) }}
                                            </p>
                                            <p v-if="flight.active_promotion" class="text-xs text-green-600 font-semibold">
                                                {{ flight.active_promotion.discount_percent }}% descuento
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Número de Pasajeros -->
                                <div>
                                    <h3
                                        class="text-lg font-semibold text-gray-900 mb-4"
                                    >
                                        ¿Cuántos pasajeros van a viajar?
                                    </h3>

                                    <div
                                        class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-xl p-6 border-2 border-blue-200"
                                    >
                                        <div
                                            class="flex items-center justify-center gap-6"
                                        >
                                            <button
                                                @click="decrementPassengers"
                                                :disabled="passengersCount <= 1"
                                                class="w-12 h-12 rounded-full bg-white border-2 border-blue-300 text-blue-600 font-bold text-xl hover:bg-blue-50 disabled:opacity-40 disabled:cursor-not-allowed transition-all"
                                            >
                                                −
                                            </button>

                                            <div class="text-center">
                                                <p
                                                    class="text-5xl font-black text-blue-600"
                                                >
                                                    {{ passengersCount }}
                                                </p>
                                                <p
                                                    class="text-sm text-gray-600 mt-1"
                                                >
                                                    {{
                                                        passengersCount === 1
                                                            ? "Pasajero"
                                                            : "Pasajeros"
                                                    }}
                                                </p>
                                            </div>

                                            <button
                                                @click="incrementPassengers"
                                                :disabled="passengersCount >= 5"
                                                class="w-12 h-12 rounded-full bg-white border-2 border-blue-300 text-blue-600 font-bold text-xl hover:bg-blue-50 disabled:opacity-40 disabled:cursor-not-allowed transition-all"
                                            >
                                                +
                                            </button>
                                        </div>

                                        <p
                                            class="text-xs text-center text-gray-500 mt-4"
                                        >
                                            Máximo 5 pasajeros por reserva
                                        </p>

                                        <div
                                            class="mt-6 p-4 bg-white/70 rounded-lg"
                                        >
                                            <p
                                                class="text-sm text-gray-600 mb-2"
                                            >
                                                💡
                                                <span class="font-semibold"
                                                    >Nota:</span
                                                >
                                                Cada pasajero podrá elegir su
                                                clase (Económica o Primera) en
                                                el siguiente paso.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Error -->
                                <p
                                    v-if="error"
                                    class="text-sm text-rose-600 text-center"
                                >
                                    {{ error }}
                                </p>

                                <!-- Botones -->
                                <div class="flex gap-3">
                                    <button
                                        @click="close"
                                        class="flex-1 h-12 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition-colors"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        @click="continueToPassengers"
                                        :disabled="passengersCount < 1"
                                        :class="[
                                            'flex-1 h-12 rounded-xl font-semibold transition-all',
                                            passengersCount >= 1
                                                ? 'bg-gradient-to-r from-blue-600 to-cyan-500 text-white hover:from-blue-700 hover:to-cyan-600 shadow-lg'
                                                : 'bg-gray-300 text-gray-500 cursor-not-allowed',
                                        ]"
                                    >
                                        Continuar → ({{ passengersCount }}
                                        {{
                                            passengersCount === 1
                                                ? "pasajero"
                                                : "pasajeros"
                                        }})
                                    </button>
                                </div>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import {
    TransitionRoot,
    TransitionChild,
    Dialog,
    DialogPanel,
    DialogTitle,
} from "@headlessui/vue";
import { useFlightTime } from "../../composables/useFlightTime";
import { useCurrency } from "../../composables/useCurrency";

const { formatDuration } = useFlightTime();
const { formatPrice: formatCurrency } = useCurrency();

const props = defineProps({
    open: Boolean,
    flight: Object,
    actionType: String, // 'reservation' | 'purchase'
});

const emit = defineEmits(["update:open", "submit"]);

const passengersCount = ref(1);
const error = ref("");

// Computed para calcular info de llegada - AUTÓNOMO Y ROBUSTO
const arrivalInfo = computed(() => {
    if (!props.flight) return null;

    // PRIORIDAD 1: Si viene del backend con arrival_info completo, usarlo directamente
    if (props.flight.arrival_info && props.flight.arrival_info.timezone_abbr) {
        // Asegurarse de que datetime sea un objeto Date válido
        const datetime =
            typeof props.flight.arrival_info.datetime === "string"
                ? new Date(props.flight.arrival_info.datetime)
                : props.flight.arrival_info.datetime;

        return {
            ...props.flight.arrival_info,
            datetime: datetime,
        };
    }

    // PRIORIDAD 2: Calcular localmente con la zona horaria del destino
    // Esto es el fallback si no viene del backend
    const departure = new Date(props.flight.departure_at);
    const arrivalLocal = new Date(
        departure.getTime() + (props.flight.duration_minutes || 0) * 60000
    );

    // Verificar si llega en un día diferente (comparando fechas en UTC)
    const isDifferentDay =
        arrivalLocal.toDateString() !== departure.toDateString();

    // Obtener la zona horaria del destino
    const destTimezone = props.flight.destination?.timezone || "America/Bogota";

    // Calcular la hora de llegada en la zona horaria del destino
    let timezoneAbbr = "COT";
    let arrivalInDestTz = arrivalLocal;

    try {
        // Usar Intl.DateTimeFormat para obtener la abreviatura correcta
        const formatter = new Intl.DateTimeFormat("en-US", {
            timeZone: destTimezone,
            timeZoneName: "short",
        });

        const parts = formatter.formatToParts(arrivalLocal);
        const tzPart = parts.find((part) => part.type === "timeZoneName");
        if (tzPart) {
            timezoneAbbr = tzPart.value;
        }
    } catch (e) {
        console.error("Error calculating timezone:", e);
        timezoneAbbr = "COT";
    }

    return {
        datetime: arrivalInDestTz,
        timezone_abbr: timezoneAbbr,
        is_different_day: isDifferentDay,
    };
});

// Funciones para incrementar/decrementar pasajeros
const incrementPassengers = () => {
    if (passengersCount.value < 5) {
        passengersCount.value++;
    }
};

const decrementPassengers = () => {
    if (passengersCount.value > 1) {
        passengersCount.value--;
    }
};

// Resetear al abrir
watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            passengersCount.value = 1;
            error.value = "";
        }
    }
);

const formatPrice = (price) => {
    return Number(price).toLocaleString("es-CO", {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    });
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("es-ES", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};

const formatTime = (date) => {
    return new Date(date).toLocaleTimeString("es-ES", {
        hour: "2-digit",
        minute: "2-digit",
    });
};

// Funciones helper para formatear la hora de llegada
const getArrivalDate = (arrivalInfo) => {
    if (!arrivalInfo) return "";
    // Si viene del backend como string, usar datetime
    // Si viene del fallback como Date object, usar datetime
    const dateToFormat = arrivalInfo.datetime || arrivalInfo.formatted;
    return formatDate(dateToFormat);
};

const getArrivalTime = (arrivalInfo) => {
    if (!arrivalInfo) return "";
    const dateToFormat = arrivalInfo.datetime || arrivalInfo.formatted;
    return formatTime(dateToFormat);
};

const close = () => {
    emit("update:open", false);
    passengersCount.value = 1;
    error.value = "";
};

const continueToPassengers = () => {
    if (passengersCount.value < 1) {
        error.value = "Debe seleccionar al menos 1 pasajero";
        return;
    }

    emit("submit", { passengersCount: passengersCount.value });
    passengersCount.value = 1;
    error.value = "";
};

// Reset al cerrar
watch(
    () => props.open,
    (newVal) => {
        if (!newVal) {
            passengersCount.value = 1;
            error.value = "";
        }
    }
);
</script>
