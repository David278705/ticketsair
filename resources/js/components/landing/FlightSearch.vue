<template>
    <section id="buscar" class="md:mb-5 container -mt-8 md:-mt-10">
        <div
            class="rounded-2xl border border-slate-200/80 bg-white/60 backdrop-blur p-4 md:p-5 shadow-sm transition-transform duration-300 hover:-translate-y-0.5 hover:shadow-lg"
        >
            <div class="grid gap-4 md:grid-cols-5 md:items-end">
                <!-- Origen -->
                <div class="relative">
                    <label
                        for="origen"
                        class="block text-xs text-slate-500 mb-2"
                        >Origen</label
                    >

                    <Listbox v-model="originId" by="id" v-slot="{ open }">
                        <ListboxButton
                            ref="originBtn"
                            class="relative w-full cursor-default rounded-xl bg-white border border-slate-300 h-11 pl-10 pr-10 text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 flex items-center"
                        >
                            <MapPin
                                class="absolute left-3 w-5 h-5 text-slate-400"
                            />
                            <span
                                class="block truncate"
                                :class="{ 'text-slate-400': !originId }"
                            >
                                {{ originLabel }}
                            </span>
                            <span
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2"
                            >
                                <ChevronsUpDown
                                    class="h-5 w-5 text-gray-400"
                                    aria-hidden="true"
                                />
                            </span>
                        </ListboxButton>

                        <Teleport to="body">
                            <ListboxOptions
                                v-if="open"
                                ref="originOpts"
                                :style="originFloatingStyles"
                                static
                                class="z-50 mt-1 max-h-80 w-64 rounded-xl bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-none sm:text-sm flex flex-col"
                            >
                                <div
                                    class="px-3 py-2 bg-white/95 backdrop-blur border-b border-slate-200/60 flex-shrink-0"
                                >
                                    <input
                                        v-model="originQuery"
                                        placeholder="Buscar ciudad…"
                                        class="w-full h-9 rounded-lg border px-3 bg-white border-slate-300 outline-none"
                                    />
                                </div>
                                <div class="overflow-y-auto flex-1">
                                    <ListboxOption
                                        v-for="c in filteredOrigins"
                                        :key="c.id"
                                        :value="c"
                                        v-slot="{ active, selected }"
                                    >
                                        <li
                                            :class="[
                                                active
                                                    ? 'bg-blue-100  text-blue-900 '
                                                    : 'text-gray-900',
                                                'relative cursor-default select-none py-2 pl-10 pr-4',
                                            ]"
                                        >
                                            <span
                                                :class="[
                                                    selected
                                                        ? 'font-medium'
                                                        : 'font-normal',
                                                    'block truncate',
                                                ]"
                                                >{{ c.name }}</span
                                            >
                                            <span
                                                v-if="selected"
                                                class="absolute inset-y-0 left-0 flex items-center pl-3 text-blue-600"
                                            >
                                                <Check
                                                    class="h-5 w-5"
                                                    aria-hidden="true"
                                                />
                                            </span>
                                        </li>
                                    </ListboxOption>
                                </div>
                            </ListboxOptions>
                        </Teleport>
                    </Listbox>
                </div>

                <!-- Destino -->
                <div class="relative">
                    <label
                        for="destino"
                        class="block text-xs text-slate-500 mb-2"
                        >Destino</label
                    >

                    <Listbox v-model="destinationId" by="id" v-slot="{ open }">
                        <ListboxButton
                            ref="destBtn"
                            class="relative w-full cursor-default rounded-xl bg-white border border-slate-300 h-11 pl-10 pr-10 text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 flex items-center"
                        >
                            <MapPin
                                class="absolute left-3 w-5 h-5 text-slate-400"
                            />
                            <span
                                class="block truncate"
                                :class="{ 'text-slate-400': !destinationId }"
                            >
                                {{ destinationLabel }}
                            </span>
                            <span
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2"
                            >
                                <ChevronsUpDown
                                    class="h-5 w-5 text-gray-400"
                                    aria-hidden="true"
                                />
                            </span>
                        </ListboxButton>

                        <Teleport to="body">
                            <ListboxOptions
                                v-if="open"
                                ref="destOpts"
                                :style="destFloatingStyles"
                                static
                                class="z-50 mt-1 max-h-80 w-64 rounded-xl bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-none sm:text-sm flex flex-col"
                            >
                                <div
                                    class="px-3 py-2 bg-white/95 backdrop-blur border-b border-slate-200/60 flex-shrink-0"
                                >
                                    <input
                                        v-model="destQuery"
                                        placeholder="Buscar ciudad…"
                                        class="w-full h-9 rounded-lg border px-3 bg-white border-slate-300 outline-none"
                                    />
                                </div>
                                <div class="overflow-y-auto flex-1">
                                    <ListboxOption
                                        v-for="c in filteredDestinations"
                                        :key="c.id"
                                        :value="c"
                                        v-slot="{ active, selected }"
                                    >
                                        <li
                                            :class="[
                                                active
                                                    ? 'bg-blue-100  text-blue-900'
                                                    : 'text-gray-900',
                                                'relative cursor-default select-none py-2 pl-10 pr-4',
                                            ]"
                                        >
                                            <span
                                                :class="[
                                                    selected
                                                        ? 'font-medium'
                                                        : 'font-normal',
                                                    'block truncate',
                                                ]"
                                                >{{ c.name }}</span
                                            >
                                            <span
                                                v-if="selected"
                                                class="absolute inset-y-0 left-0 flex items-center pl-3 text-blue-600"
                                            >
                                                <Check
                                                    class="h-5 w-5"
                                                    aria-hidden="true"
                                                />
                                            </span>
                                        </li>
                                    </ListboxOption>
                                </div>
                            </ListboxOptions>
                        </Teleport>
                    </Listbox>
                </div>

                <!-- Fecha -->
                <div>
                    <label class="block text-xs text-slate-500 mb-2"
                        >Fecha</label
                    >
                    <div class="relative">
                        <CalendarDays
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 pointer-events-none z-10"
                        />
                        <input
                            type="date"
                            v-model="dateInput"
                            :min="minDate"
                            class="relative w-full rounded-xl bg-white border border-slate-300 h-11 pl-10 pr-3 text-left focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Selecciona una fecha"
                        />
                    </div>
                </div>

                <!-- Clase -->
                <div class="relative">
                    <label class="block text-xs text-slate-500 mb-2"
                        >Clase</label
                    >
                    <Listbox v-model="selectedClass" v-slot="{ open }">
                        <ListboxButton
                            ref="listboxButtonRef"
                            class="relative w-full cursor-default rounded-xl bg-white border border-slate-300 h-11 pl-3 pr-10 text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                        >
                            <span class="block truncate">{{
                                selectedClass.name
                            }}</span>
                            <span
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2"
                            >
                                <ChevronsUpDown
                                    class="h-5 w-5 text-gray-400"
                                    aria-hidden="true"
                                />
                            </span>
                        </ListboxButton>
                        <Teleport to="body">
                            <ListboxOptions
                                v-if="open"
                                ref="listboxOptionsRef"
                                :style="listboxFloatingStyles"
                                static
                                class="z-50 mt-1 max-h-60 w-52 overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-none sm:text-sm"
                            >
                                <ListboxOption
                                    v-for="clase in classes"
                                    :key="clase.id"
                                    :value="clase"
                                    v-slot="{ active, selected }"
                                >
                                    <li
                                        :class="[
                                            active
                                                ? 'bg-blue-100  text-blue-900'
                                                : 'text-gray-900 ',
                                            'relative cursor-default select-none py-2 pl-10 pr-4',
                                        ]"
                                    >
                                        <span
                                            :class="[
                                                selected
                                                    ? 'font-medium'
                                                    : 'font-normal',
                                                'block truncate',
                                            ]"
                                            >{{ clase.name }}</span
                                        >
                                        <span
                                            v-if="selected"
                                            class="absolute inset-y-0 left-0 flex items-center pl-3 text-blue-600"
                                        >
                                            <Check
                                                class="h-5 w-5"
                                                aria-hidden="true"
                                            />
                                        </span>
                                    </li>
                                </ListboxOption>
                            </ListboxOptions>
                        </Teleport>
                    </Listbox>
                </div>

                <!-- Buscar -->
                <div class="md:col-start-5">
                    <button
                        type="button"
                        @click="search"
                        class="w-full rounded-xl bg-gradient-to-tr from-blue-600 to-cyan-400 text-white h-11 px-6 shadow hover:opacity-90 transition-opacity flex items-center justify-center gap-2 disabled:opacity-60"
                        :disabled="loading"
                    >
                        <Search class="w-5 h-5" />
                        <span>{{
                            loading ? "Buscando…" : "Buscar vuelos"
                        }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Resultados PRUEBA -->
        <div
            v-if="resultsLoading"
            class="container mt-6 text-sm text-slate-500"
        >
            Cargando resultados… CAMBIO 1
        </div>

        <div
            v-else
            class="container grid gap-4 sm:grid-cols-2 lg:grid-cols-3 mt-6"
            v-if="results.data?.length"
        >
            <article
                v-for="f in results.data"
                :key="f.id"
                class="rounded-2xl border border-slate-200 p-4 transition-transform hover:-translate-y-0.5 hover:shadow-lg relative"
            >
                <!-- Badge de descuento -->
                <div
                    v-if="f.active_promotion"
                    class="absolute -top-2 -right-2 bg-gradient-to-br from-red-500 to-pink-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg animate-pulse"
                >
                    -{{ f.active_promotion.discount_percent }}%
                </div>

                <header class="flex justify-between">
                    <h3 class="font-semibold">
                        {{ f.origin.name }} → {{ f.destination.name }}
                    </h3>
                    <span class="text-sm">{{
                        new Date(f.departure_at).toLocaleString()
                    }}</span>
                </header>
                <div class="mt-2 text-sm">
                    <span v-if="!f.active_promotion">
                        Precio por silla:
                        <b>{{ formatPrice(+f.price_per_seat) }}</b>
                    </span>
                    <div v-else class="flex items-center gap-2">
                        <span class="text-gray-500 line-through text-xs">
                            {{ formatPrice(+f.price_per_seat) }}
                        </span>
                        <span class="text-green-600 font-bold text-base">
                            {{
                                formatPrice(
                                    +f.price_per_seat *
                                        (1 -
                                            f.active_promotion
                                                .discount_percent /
                                                100)
                                )
                            }}
                        </span>
                        <span class="text-xs text-gray-500">por silla</span>
                    </div>
                </div>
                <div class="mt-3 flex gap-2">
                    <button
                        class="h-10 px-4 rounded-xl border border-slate-300"
                        @click="tryReserve(f)"
                        :disabled="actionLoading"
                    >
                        Reservar
                    </button>
                    <button
                        class="h-10 px-4 rounded-xl bg-blue-600 text-white"
                        @click="tryBuy(f)"
                        :disabled="actionLoading"
                    >
                        Comprar
                    </button>
                </div>
            </article>
        </div>

        <div
            v-if="!resultsLoading && results.data && !results.data.length"
            class="container mt-6 text-sm text-slate-500"
        >
            No encontramos vuelos con esos filtros.
        </div>

        <div
            v-if="results?.meta?.links?.length"
            class="container mt-6 flex flex-wrap gap-2"
        >
            <button
                v-for="l in results.meta.links"
                :key="l.label"
                :disabled="!l.url"
                @click="go(l.url)"
                class="px-3 h-9 rounded-lg border border-slate-300"
                v-html="l.label"
            />
        </div>

        <!-- Modal de Información de Vuelo (seleccionar cantidad de pasajeros) -->
        <FlightInfoModal
            v-model:open="flightInfoOpen"
            :flight="currentFlight"
            :action-type="pendingAction"
            @submit="onFlightInfoSubmit"
        />

        <!-- Modal de Pasajeros -->
        <PassengersModal
            v-model:open="passengersOpen"
            :passengers-count="passengersCount"
            :base-price-per-seat="discountedBasePricePerSeat"
            :first-class-price="discountedFirstClassPrice"
            @submit="onPassengersSubmit"
        />

        <!-- Modal de Pago -->
        <PaymentModal
            v-model:open="paymentOpen"
            :total-amount="pendingBooking?.total_amount || 0"
            :booking-info="pendingBooking"
            @payment-success="onPaymentSuccess"
        />
    </section>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import {
    Listbox,
    ListboxButton,
    ListboxOptions,
    ListboxOption,
} from "@headlessui/vue";
import {
    MapPin,
    ChevronsUpDown,
    Check,
    Search,
    CalendarDays,
} from "lucide-vue-next";
import { useFloating, autoUpdate, offset } from "@floating-ui/vue";
import { api } from "../../lib/api";
import { useAuth } from "../../stores/auth";
import { useUi } from "../../stores/ui";
import { useCurrency } from "../../composables/useCurrency";
import { useSweetAlert } from "../../composables/useSweetAlert";
import FlightInfoModal from "../booking/FlightInfoModal.vue";
import PassengersModal from "../booking/PassengersModal.vue";
import PaymentModal from "../booking/PaymentModal.vue";

const auth = useAuth();
const ui = useUi();
const { formatPrice } = useCurrency();
const { success, error: showError } = useSweetAlert();

// Ciudades
const cities = ref([]);
const originId = ref(null);
const destinationId = ref(null);
const originQuery = ref("");
const destQuery = ref("");

const listboxButtonRef = ref(null);
const listboxOptionsRef = ref(null);
const { floatingStyles: listboxFloatingStyles } = useFloating(
    listboxButtonRef,
    listboxOptionsRef,
    {
        whileElementsMounted: autoUpdate,
        placement: "bottom-start",
        middleware: [offset(8)],
        strategy: "fixed", // evita saltos al hacer scroll
    }
);

const originLabel = computed(() => originId.value?.name || "Selecciona origen");
const destinationLabel = computed(
    () => destinationId.value?.name || "Selecciona destino"
);

const filteredOrigins = computed(() =>
    cities.value.filter((c) =>
        c.name.toLowerCase().includes(originQuery.value.toLowerCase())
    )
);
const filteredDestinations = computed(() =>
    cities.value.filter((c) =>
        c.name.toLowerCase().includes(destQuery.value.toLowerCase())
    )
);

// Fecha y clase
const date = ref();
const dateInput = ref("");
const minDate = computed(() => {
    const today = new Date();
    return today.toISOString().split('T')[0];
});

const classes = [
    { id: "economy", name: "Economy" },
    { id: "first", name: "First" },
];
const selectedClass = ref(classes[0]);

// FloatingUI
const originBtn = ref(null),
    originOpts = ref(null);
const destBtn = ref(null),
    destOpts = ref(null);
const { floatingStyles: originFloatingStyles } = useFloating(
    originBtn,
    originOpts,
    {
        whileElementsMounted: autoUpdate,
        placement: "bottom-start",
        middleware: [offset(8)],
        strategy: "fixed",
    }
);
const { floatingStyles: destFloatingStyles } = useFloating(destBtn, destOpts, {
    whileElementsMounted: autoUpdate,
    placement: "bottom-start",
    middleware: [offset(8)],
    strategy: "fixed",
});

// Resultados
const loading = ref(false);
const resultsLoading = ref(false);
const results = ref({ data: [], meta: null });
const actionLoading = ref(false);

// Estado de compra/reserva
const flightInfoOpen = ref(false);
const passengersOpen = ref(false);
const paymentOpen = ref(false);
const pendingAction = ref(null); // 'reservation' | 'purchase'
const currentFlight = ref(null);
const passengersCount = ref(1);
const pendingPassengers = ref([]);
const pendingBooking = ref(null);

// Precios con descuento aplicado
const discountedBasePricePerSeat = computed(() => {
    if (!currentFlight.value) return 0;
    const basePrice = parseFloat(currentFlight.value.price_per_seat) || 0;
    if (currentFlight.value.active_promotion) {
        const discount = currentFlight.value.active_promotion.discount_percent;
        return basePrice * (1 - discount / 100);
    }
    return basePrice;
});

const discountedFirstClassPrice = computed(() => {
    if (!currentFlight.value) return 0;
    const firstPrice =
        parseFloat(currentFlight.value.first_class_price) ||
        parseFloat(currentFlight.value.price_per_seat) * 2 ||
        0;
    if (currentFlight.value.active_promotion) {
        const discount = currentFlight.value.active_promotion.discount_percent;
        return firstPrice * (1 - discount / 100);
    }
    return firstPrice;
});

onMounted(async () => {
    const { data } = await api.get("/cities");
    cities.value = data;
});

// Buscar
async function search(url) {
    loading.value = true;
    resultsLoading.value = true;
    try {
        const params = {
            origin_id: originId.value?.id || "",
            destination_id: destinationId.value?.id || "",
            date: dateInput.value || "",
            class: selectedClass.value.id,
        };
        const { data } = await api.get("/flights", { params });
        results.value = data;
    } finally {
        loading.value = false;
        resultsLoading.value = false;
    }
}
function go(url) {
    const u = new URL(url);
    search(u.pathname + u.search);
}

// Flujo de acciones
function ensureAuth() {
    if (!auth.user) {
        ui.openAuth("login");
        return false;
    }
    return true;
}
function ensureClass() {
    if (!selectedClass.value) selectedClass.value = classes[0];
}

function tryReserve(f) {
    if (!ensureAuth()) return;
    ensureClass();
    currentFlight.value = f;
    pendingAction.value = "reservation";
    flightInfoOpen.value = true;
}
function tryBuy(f) {
    if (!ensureAuth()) return;
    ensureClass();
    currentFlight.value = f;
    pendingAction.value = "purchase";
    flightInfoOpen.value = true;
}

function onFlightInfoSubmit({ passengersCount: count }) {
    passengersCount.value = count;
    flightInfoOpen.value = false;
    passengersOpen.value = true;
}

async function onPassengersSubmit(passengers) {
    if (!currentFlight.value || !pendingAction.value) return;

    pendingPassengers.value = passengers;

    // Calcular precio total basado en las clases individuales de cada pasajero
    // usando precios ya con descuento aplicado
    const total = passengers.reduce((sum, p) => {
        const pricePerSeat =
            p.flight_class === "first"
                ? discountedFirstClassPrice.value
                : discountedBasePricePerSeat.value;
        return sum + pricePerSeat;
    }, 0);

    pendingBooking.value = {
        total_amount: total,
        passengers_count: passengers.length,
    };

    // Si es reserva, NO abrir modal de pago
    if (pendingAction.value === "reservation") {
        // Procesar la reserva directamente sin pago
        await processBooking(null);
    } else {
        // Si es compra, abrir modal de pago
        paymentOpen.value = true;
    }
}

async function onPaymentSuccess(paymentData) {
    await processBooking(paymentData);
}

async function processBooking(paymentData) {
    if (!currentFlight.value || !pendingAction.value) return;
    actionLoading.value = true;

    try {
        // Preparar los datos de pasajeros con sus clases individuales
        const passengersWithClass = pendingPassengers.value.map((p) => ({
            ...p,
            class: p.flight_class, // Agregar clase individual
        }));

        const payload = {
            flight_id: currentFlight.value.id,
            type: pendingAction.value,
            passengers: passengersWithClass,
        };

        // Si hay datos de pago, incluirlos
        if (paymentData) {
            payload.payment = paymentData;
        }

        const response = await api.post("/bookings", payload, {
            headers: { Authorization: "Bearer " + auth.token },
        });

        const booking = response.data;
        const isPurchase = pendingAction.value === "purchase";

        // Mostrar mensaje de éxito con código de reserva
        const message = isPurchase
            ? `Código de Reserva: ${booking.reservation_code}\n\nRevisa tu correo para más detalles.`
            : `Código de Reserva: ${booking.reservation_code}\n\nTienes 24 horas para completar tu compra. Recibirás un recordatorio por correo.`;

        await success(
            isPurchase
                ? "¡Compra realizada exitosamente!"
                : "¡Reserva creada exitosamente!",
            message
        );

        // Redirigir a Mis Viajes después de 2 segundos
        setTimeout(() => {
            window.location.href = "/mis-viajes";
        }, 2000);
    } catch (e) {
        const errorMessage =
            e.response?.data?.message ||
            e.response?.data?.error ||
            "No se pudo procesar la solicitud.";
        showError("Error al procesar", errorMessage);
    } finally {
        actionLoading.value = false;
    }
}
</script>
