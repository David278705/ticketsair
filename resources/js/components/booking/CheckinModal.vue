<template>
    <TransitionRoot as="template" :show="open">
        <Dialog class="relative z-50" @close="closeModal">
            <TransitionChild
                as="template"
                enter="ease-out duration-300"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in duration-200"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div
                    class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"
                />
            </TransitionChild>

            <div class="fixed inset-0 z-50 overflow-y-auto">
                <div
                    class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0"
                >
                    <TransitionChild
                        as="template"
                        enter="ease-out duration-300"
                        enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to="opacity-100 translate-y-0 sm:scale-100"
                        leave="ease-in duration-200"
                        leave-from="opacity-100 translate-y-0 sm:scale-100"
                        leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    >
                        <DialogPanel
                            class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl"
                        >
                            <!-- Header -->
                            <div
                                class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <DialogTitle
                                            class="text-xl font-semibold text-white"
                                        >
                                            Check-in
                                        </DialogTitle>
                                        <p class="mt-1 text-sm text-blue-100">
                                            {{ passenger.first_name }}
                                            {{ passenger.last_name }}
                                        </p>
                                    </div>
                                    <button
                                        @click="closeModal"
                                        class="rounded-lg p-1 text-white hover:bg-white/20"
                                    >
                                        <svg
                                            class="h-6 w-6"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
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
                            </div>

                            <!-- Content -->
                            <div class="max-h-[70vh] overflow-y-auto p-6">
                                <!-- Información del Vuelo -->
                                <div
                                    class="mb-6 rounded-lg border bg-slate-50 p-4"
                                >
                                    <h3
                                        class="mb-2 text-sm font-semibold text-slate-700"
                                    >
                                        Información del Vuelo
                                    </h3>
                                    <div class="grid grid-cols-2 gap-3 text-sm">
                                        <div>
                                            <span class="text-slate-500"
                                                >Vuelo:</span
                                            >
                                            <span class="ml-2 font-medium">{{
                                                booking.flight.flight_number
                                            }}</span>
                                        </div>
                                        <div>
                                            <span class="text-slate-500"
                                                >Clase:</span
                                            >
                                            <span
                                                class="ml-2 font-medium capitalize"
                                                >{{ klass }}</span
                                            >
                                        </div>
                                        <div>
                                            <span class="text-slate-500"
                                                >Origen:</span
                                            >
                                            <span class="ml-2 font-medium">{{
                                                booking.flight.origin?.name
                                            }}</span>
                                        </div>
                                        <div>
                                            <span class="text-slate-500"
                                                >Destino:</span
                                            >
                                            <span class="ml-2 font-medium">{{
                                                booking.flight.destination?.name
                                            }}</span>
                                        </div>
                                        <div class="col-span-2">
                                            <span class="text-slate-500"
                                                >Salida:</span
                                            >
                                            <span class="ml-2 font-medium">{{
                                                formatFlightDate(
                                                    booking.flight.departure_at
                                                )
                                            }}</span>
                                        </div>
                                        <div
                                            class="col-span-2"
                                            v-if="passenger.seat"
                                        >
                                            <span class="text-slate-500"
                                                >Asiento Actual:</span
                                            >
                                            <span
                                                class="ml-2 font-medium text-blue-600"
                                                >{{
                                                    passenger.seat.seat_number
                                                }}</span
                                            >
                                            <span
                                                class="ml-1 text-xs text-slate-500"
                                                >(puedes cambiarlo)</span
                                            >
                                        </div>
                                    </div>
                                </div>

                                <!-- Selección de Asiento -->
                                <div class="mb-6">
                                    <h3
                                        class="mb-3 text-base font-semibold text-slate-800"
                                    >
                                        Selecciona tu Asiento
                                    </h3>

                                    <div
                                        v-if="loadingSeats"
                                        class="flex justify-center py-8"
                                    >
                                        <div
                                            class="h-8 w-8 animate-spin rounded-full border-4 border-blue-600 border-t-transparent"
                                        ></div>
                                    </div>

                                    <div
                                        v-else-if="seats.length > 0"
                                        class="space-y-3"
                                    >
                                        <!-- Leyenda -->
                                        <div
                                            class="flex flex-wrap gap-4 text-xs"
                                        >
                                            <div
                                                class="flex items-center gap-1"
                                            >
                                                <div
                                                    class="h-6 w-6 rounded border-2 border-slate-300 bg-white"
                                                ></div>
                                                <span class="text-slate-600"
                                                    >Disponible</span
                                                >
                                            </div>
                                            <div
                                                class="flex items-center gap-1"
                                            >
                                                <div
                                                    class="h-6 w-6 rounded border-2 border-red-300 bg-red-100"
                                                ></div>
                                                <span class="text-slate-600"
                                                    >Ocupado</span
                                                >
                                            </div>
                                            <div
                                                class="flex items-center gap-1"
                                            >
                                                <div
                                                    class="h-6 w-6 rounded border-2 border-blue-500 bg-blue-500"
                                                ></div>
                                                <span class="text-slate-600"
                                                    >Seleccionado</span
                                                >
                                            </div>
                                        </div>

                                        <!-- Grid de Asientos -->
                                        <div
                                            class="relative rounded-2xl border-2 border-slate-300 bg-white p-6 shadow-xl overflow-hidden"
                                        >
                                            <!-- Fondo de Avión - Imagen personalizada -->
                                            <div
                                                class="absolute inset-0 pointer-events-none"
                                            >
                                                <img
                                                    src="/public/img/avion.jpeg"
                                                    alt="Airplane Layout"
                                                    class="w-full h-full object-contain opacity-40"
                                                />
                                            </div>

                                            <!-- Contenido: Grid de Asientos -->
                                            <div
                                                class="relative mx-auto max-w-md space-y-2"
                                            >
                                                <div
                                                    v-for="row in seatRows"
                                                    :key="row"
                                                    class="flex items-center justify-center gap-2"
                                                >
                                                    <span
                                                        class="w-8 text-center text-xs font-medium text-slate-500"
                                                        >{{ row }}</span
                                                    >
                                                    <div class="flex gap-2">
                                                        <button
                                                            v-for="seat in getSeatsInRow(
                                                                row
                                                            )"
                                                            :key="seat.id"
                                                            @click="
                                                                selectSeat(seat)
                                                            "
                                                            :disabled="
                                                                !seat.available
                                                            "
                                                            :class="[
                                                                'h-10 w-10 rounded text-xs font-medium transition-all relative z-10',
                                                                selectedSeat?.id ===
                                                                seat.id
                                                                    ? 'border-2 border-blue-500 bg-blue-500 text-white shadow-md'
                                                                    : seat.available
                                                                    ? 'border-2 border-slate-300 bg-white text-slate-700 hover:border-blue-400 hover:bg-blue-50'
                                                                    : 'cursor-not-allowed border-2 border-red-300 bg-red-100 text-red-400',
                                                            ]"
                                                        >
                                                            {{
                                                                seat.seat_number
                                                            }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <p
                                            v-if="selectedSeat"
                                            class="text-sm text-blue-600"
                                        >
                                            Asiento seleccionado:
                                            <span class="font-semibold">{{
                                                selectedSeat.seat_number
                                            }}</span>
                                        </p>
                                    </div>

                                    <div
                                        v-else
                                        class="rounded-lg border border-amber-200 bg-amber-50 p-4"
                                    >
                                        <p class="text-sm text-amber-700">
                                            No hay asientos disponibles para tu
                                            clase.
                                        </p>
                                    </div>
                                </div>

                                <!-- Selección de Equipaje -->
                                <div class="mb-6">
                                    <h3
                                        class="mb-3 text-base font-semibold text-slate-800"
                                    >
                                        Equipaje
                                    </h3>

                                    <div class="space-y-4">
                                        <!-- Tipo de Equipaje -->
                                        <div>
                                            <label
                                                class="mb-2 block text-sm font-medium text-slate-700"
                                                >Tipo de Equipaje</label
                                            >
                                            <div class="grid grid-cols-2 gap-3">
                                                <button
                                                    @click="
                                                        luggageType = 'cabin'
                                                    "
                                                    :class="[
                                                        'rounded-lg border-2 p-4 text-left transition-all',
                                                        luggageType === 'cabin'
                                                            ? 'border-blue-500 bg-blue-50'
                                                            : 'border-slate-300 bg-white hover:border-blue-300',
                                                    ]"
                                                >
                                                    <div
                                                        class="flex items-start gap-3"
                                                    >
                                                        <div
                                                            :class="[
                                                                'mt-1 h-5 w-5 rounded-full border-2 flex items-center justify-center',
                                                                luggageType ===
                                                                'cabin'
                                                                    ? 'border-blue-500'
                                                                    : 'border-slate-300',
                                                            ]"
                                                        >
                                                            <div
                                                                v-if="
                                                                    luggageType ===
                                                                    'cabin'
                                                                "
                                                                class="h-3 w-3 rounded-full bg-blue-500"
                                                            ></div>
                                                        </div>
                                                        <div>
                                                            <p
                                                                class="font-medium text-slate-800"
                                                            >
                                                                Equipaje de
                                                                Cabina
                                                            </p>
                                                            <p
                                                                class="mt-1 text-xs text-slate-500"
                                                            >
                                                                Máx. 10kg,
                                                                55x40x20cm
                                                            </p>
                                                            <p
                                                                class="mt-1 text-xs font-semibold text-blue-600"
                                                            >
                                                                Incluido
                                                            </p>
                                                        </div>
                                                    </div>
                                                </button>

                                                <button
                                                    @click="
                                                        luggageType = 'hold'
                                                    "
                                                    :class="[
                                                        'rounded-lg border-2 p-4 text-left transition-all',
                                                        luggageType === 'hold'
                                                            ? 'border-blue-500 bg-blue-50'
                                                            : 'border-slate-300 bg-white hover:border-blue-300',
                                                    ]"
                                                >
                                                    <div
                                                        class="flex items-start gap-3"
                                                    >
                                                        <div
                                                            :class="[
                                                                'mt-1 h-5 w-5 rounded-full border-2 flex items-center justify-center',
                                                                luggageType ===
                                                                'hold'
                                                                    ? 'border-blue-500'
                                                                    : 'border-slate-300',
                                                            ]"
                                                        >
                                                            <div
                                                                v-if="
                                                                    luggageType ===
                                                                    'hold'
                                                                "
                                                                class="h-3 w-3 rounded-full bg-blue-500"
                                                            ></div>
                                                        </div>
                                                        <div>
                                                            <p
                                                                class="font-medium text-slate-800"
                                                            >
                                                                Equipaje
                                                                Facturado
                                                            </p>
                                                            <p
                                                                class="mt-1 text-xs text-slate-500"
                                                            >
                                                                Máx. 23kg por
                                                                pieza
                                                            </p>
                                                            <p
                                                                class="mt-1 text-xs font-semibold text-blue-600"
                                                            >
                                                                {{
                                                                    formatPrice(
                                                                        50000
                                                                    )
                                                                }}
                                                                / pieza
                                                            </p>
                                                        </div>
                                                    </div>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Cantidad de Piezas -->
                                        <div>
                                            <label
                                                class="mb-2 block text-sm font-medium text-slate-700"
                                            >
                                                Cantidad de Piezas
                                                <span
                                                    v-if="
                                                        luggageType === 'cabin'
                                                    "
                                                    class="text-xs text-slate-500"
                                                    >(Máximo 1)</span
                                                >
                                                <span
                                                    v-else
                                                    class="text-xs text-slate-500"
                                                    >(Máximo 3)</span
                                                >
                                            </label>
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <button
                                                    @click="decreaseLuggage"
                                                    :disabled="
                                                        luggagePieces <= 1
                                                    "
                                                    class="flex h-10 w-10 items-center justify-center rounded-lg border-2 border-slate-300 bg-white text-slate-700 transition-all hover:border-blue-400 hover:bg-blue-50 disabled:cursor-not-allowed disabled:opacity-40"
                                                >
                                                    <svg
                                                        class="h-5 w-5"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M20 12H4"
                                                        />
                                                    </svg>
                                                </button>

                                                <div
                                                    class="flex h-10 w-16 items-center justify-center rounded-lg border-2 border-slate-300 bg-slate-50"
                                                >
                                                    <span
                                                        class="text-lg font-semibold text-slate-800"
                                                        >{{
                                                            luggagePieces
                                                        }}</span
                                                    >
                                                </div>

                                                <button
                                                    @click="increaseLuggage"
                                                    :disabled="
                                                        luggagePieces >=
                                                        (luggageType === 'cabin'
                                                            ? 1
                                                            : 3)
                                                    "
                                                    class="flex h-10 w-10 items-center justify-center rounded-lg border-2 border-slate-300 bg-white text-slate-700 transition-all hover:border-blue-400 hover:bg-blue-50 disabled:cursor-not-allowed disabled:opacity-40"
                                                >
                                                    <svg
                                                        class="h-5 w-5"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 4v16m8-8H4"
                                                        />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Resumen de Costos y Método de Pago -->
                            <div
                                v-if="
                                    luggageType === 'hold' && luggagePieces > 0
                                "
                                class="border-t bg-blue-50 px-6 py-4"
                            >
                                <div
                                    class="mb-4 flex items-center justify-between"
                                >
                                    <div>
                                        <p
                                            class="text-sm font-medium text-slate-700"
                                        >
                                            Equipaje Facturado
                                        </p>
                                        <p class="text-xs text-slate-500">
                                            {{ luggagePieces }}
                                            {{
                                                luggagePieces === 1
                                                    ? "pieza"
                                                    : "piezas"
                                            }}
                                            × {{ formatPrice(50000) }}
                                        </p>
                                    </div>
                                    <p class="text-lg font-bold text-blue-600">
                                        {{ formatPrice(luggageTotalCost) }}
                                    </p>
                                </div>

                                <!-- Selector de Método de Pago -->
                                <div
                                    class="mt-4 rounded-lg border border-blue-200 bg-white p-4"
                                >
                                    <label
                                        class="mb-2 block text-sm font-medium text-slate-700"
                                        >Método de Pago</label
                                    >
                                    <div class="grid grid-cols-2 gap-3">
                                        <button
                                            @click="paymentMethod = 'wallet'"
                                            :class="[
                                                'flex items-center gap-2 rounded-lg border-2 p-3 text-left transition-all',
                                                paymentMethod === 'wallet'
                                                    ? 'border-blue-500 bg-blue-50'
                                                    : 'border-slate-200 bg-white hover:border-blue-300',
                                            ]"
                                        >
                                            <svg
                                                class="h-5 w-5"
                                                :class="
                                                    paymentMethod === 'wallet'
                                                        ? 'text-blue-600'
                                                        : 'text-slate-400'
                                                "
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                                                />
                                            </svg>
                                            <span
                                                class="text-sm font-medium"
                                                :class="
                                                    paymentMethod === 'wallet'
                                                        ? 'text-blue-700'
                                                        : 'text-slate-700'
                                                "
                                                >Billetera</span
                                            >
                                        </button>
                                        <button
                                            @click="paymentMethod = 'card'"
                                            :class="[
                                                'flex items-center gap-2 rounded-lg border-2 p-3 text-left transition-all',
                                                paymentMethod === 'card'
                                                    ? 'border-blue-500 bg-blue-50'
                                                    : 'border-slate-200 bg-white hover:border-blue-300',
                                            ]"
                                        >
                                            <svg
                                                class="h-5 w-5"
                                                :class="
                                                    paymentMethod === 'card'
                                                        ? 'text-blue-600'
                                                        : 'text-slate-400'
                                                "
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                                                />
                                            </svg>
                                            <span
                                                class="text-sm font-medium"
                                                :class="
                                                    paymentMethod === 'card'
                                                        ? 'text-blue-700'
                                                        : 'text-slate-700'
                                                "
                                                >Tarjeta</span
                                            >
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="border-t bg-slate-50 px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <button
                                        @click="closeModal"
                                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        @click="submitCheckin"
                                        :disabled="!selectedSeat || submitting"
                                        class="rounded-lg bg-blue-600 px-6 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <span v-if="submitting"
                                            >Procesando...</span
                                        >
                                        <span v-else>Completar Check-in</span>
                                    </button>
                                </div>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>

    <!-- Modal de Pago con Tarjeta -->
    <UnifiedPaymentModal
        v-if="showPaymentModal && luggageTotalCost > 0"
        :open="showPaymentModal"
        :amount="luggageTotalCost"
        :description="`Equipaje facturado - ${luggagePieces} ${
            luggagePieces === 1 ? 'pieza' : 'piezas'
        }`"
        :booking-id="booking.id"
        @close="showPaymentModal = false"
        @payment-success="handlePaymentSuccess"
    />
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
} from "@headlessui/vue";
import UnifiedPaymentModal from "./UnifiedPaymentModal.vue";
import { api } from "../../lib/api";
import { useCurrency } from "../../composables/useCurrency";
import { useSweetAlert } from "../../composables/useSweetAlert";

const props = defineProps({
    open: { type: Boolean, required: true },
    booking: { type: Object, required: true },
    passenger: { type: Object, required: true },
    ticket: { type: Object, required: true },
    flightId: { type: Number, required: true },
    klass: { type: String, required: true },
});

const emit = defineEmits(["update:open", "checkin-success"]);

const { formatPrice } = useCurrency();
const { success, error: showError } = useSweetAlert();

// Estados
const loadingSeats = ref(false);
const submitting = ref(false);
const seats = ref([]);
const selectedSeat = ref(null);
const luggageType = ref("cabin");
const luggagePieces = ref(1); // Inicializar en 1
const paymentMethod = ref("wallet");
const showPaymentModal = ref(false);

// Computed
const seatRows = computed(() => {
    const rows = [...new Set(seats.value.map((s) => s.row_number))];
    return rows.sort((a, b) => a - b);
});

const luggageTotalCost = computed(() => {
    if (luggageType.value === "hold" && luggagePieces.value > 0) {
        return luggagePieces.value * 50000;
    }
    return 0;
});

// Métodos
function getSeatsInRow(row) {
    return seats.value
        .filter((s) => s.row_number === row)
        .sort((a, b) => {
            const colA = a.column || "";
            const colB = b.column || "";
            return colA.localeCompare(colB);
        });
}

function selectSeat(seat) {
    // El backend ya marca como disponibles los asientos correctos
    if (seat.available) {
        selectedSeat.value = seat;
    }
}

function formatFlightDate(dateString) {
    return new Date(dateString).toLocaleString("es-CO", {
        dateStyle: "long",
        timeStyle: "short",
    });
}

function increaseLuggage() {
    const max = luggageType.value === "cabin" ? 1 : 3;
    if (luggagePieces.value < max) {
        luggagePieces.value++;
    }
}

function decreaseLuggage() {
    const min = 1; // Siempre mínimo 1 pieza
    if (luggagePieces.value > min) {
        luggagePieces.value--;
    }
}

async function fetchSeats() {
    loadingSeats.value = true;
    try {
        const params = {
            class: props.klass,
        };

        // Pasar el seat_id actual si existe
        if (props.passenger.seat_id) {
            params.current_seat_id = props.passenger.seat_id;
        }

        const { data } = await api.get(`/flights/${props.flightId}/seats`, {
            params,
        });
        seats.value = data;
    } catch (e) {
        showError("Error", "No se pudieron cargar los asientos disponibles.");
    } finally {
        loadingSeats.value = false;
    }
}

function closeModal() {
    emit("update:open", false);
}

async function submitCheckin() {
    if (!selectedSeat.value) {
        showError("Error", "Debes seleccionar un asiento.");
        return;
    }

    // Si hay equipaje facturado con costo y el método es tarjeta, abrir modal de pago
    if (luggageTotalCost.value > 0 && paymentMethod.value === "card") {
        showPaymentModal.value = true;
        return;
    }

    submitting.value = true;
    try {
        // 1. Si hay equipaje facturado con costo y el método es billetera, procesar pago
        if (luggageTotalCost.value > 0 && paymentMethod.value === "wallet") {
            try {
                // Verificar saldo y procesar pago
                await api.post("/wallet/pay", {
                    amount: luggageTotalCost.value,
                    description: `Equipaje facturado - ${luggagePieces.value} ${
                        luggagePieces.value === 1 ? "pieza" : "piezas"
                    }`,
                    booking_id: props.booking.id,
                });
            } catch (paymentError) {
                if (paymentError.response?.status === 400) {
                    showError(
                        "Saldo Insuficiente",
                        "No tienes saldo suficiente en tu billetera para pagar el equipaje facturado."
                    );
                } else {
                    showError(
                        "Error de Pago",
                        paymentError.response?.data?.message ||
                            "No se pudo procesar el pago del equipaje."
                    );
                }
                submitting.value = false;
                return;
            }
        }

        await completeCheckinProcess();
    } catch (e) {
        showError(
            "Error en el Check-in",
            e.response?.data?.message || e.message
        );
    } finally {
        submitting.value = false;
    }
}

async function handlePaymentSuccess() {
    showPaymentModal.value = false;
    submitting.value = true;
    try {
        await completeCheckinProcess();
    } catch (e) {
        showError(
            "Error en el Check-in",
            e.response?.data?.message || e.message
        );
    } finally {
        submitting.value = false;
    }
}

async function completeCheckinProcess() {
    // 1. Asignar asiento al pasajero
    await api.post(
        `/bookings/${props.booking.id}/passengers/${props.passenger.id}/assign-seat`,
        {
            seat_id: selectedSeat.value.id,
        }
    );

    // 2. Registrar equipaje si hay
    if (luggagePieces.value > 0) {
        await api.post("/luggage", {
            ticket_id: props.ticket.id,
            type: luggageType.value,
            pieces: luggagePieces.value,
            extra_fee: luggageTotalCost.value,
        });
    }

    // 3. Realizar check-in
    await api.post("/checkin/fast", {
        ticket_code: props.ticket.ticket_code,
    });

    await success(
        "Check-in Exitoso",
        "Tu pasabordo ha sido generado y enviado a tu correo."
    );
    emit("checkin-success");
    closeModal();
}

// Watchers
watch(
    () => luggageType.value,
    (newType) => {
        if (newType === "cabin") {
            luggagePieces.value = 1; // Cabina siempre 1 pieza
        } else if (luggagePieces.value < 1) {
            luggagePieces.value = 1; // Mínimo 1 para facturado
        }
    }
);

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            fetchSeats();

            // Pre-seleccionar asiento actual si existe
            if (props.passenger.seat_id) {
                // El asiento actual se seleccionará después de cargar los asientos
                selectedSeat.value = null;
            } else {
                selectedSeat.value = null;
            }

            luggageType.value = "cabin";
            luggagePieces.value = 1; // 1 pieza de cabina por defecto
            paymentMethod.value = "wallet"; // Billetera por defecto
        }
    }
);

// Pre-seleccionar asiento cuando se cargan los asientos
watch(
    () => seats.value,
    (newSeats) => {
        if (
            newSeats.length > 0 &&
            props.passenger.seat_id &&
            !selectedSeat.value
        ) {
            const currentSeat = newSeats.find(
                (s) => s.id === props.passenger.seat_id
            );
            if (currentSeat) {
                selectedSeat.value = currentSeat;
            }
        }
    }
);

onMounted(() => {
    if (props.open) {
        fetchSeats();
    }
});
</script>
