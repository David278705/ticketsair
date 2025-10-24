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
                                class="z-50 mt-1 max-h-60 w-64 overflow-auto rounded-xl bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-none sm:text-sm"
                            >
                                <div
                                    class="px-3 py-2 sticky top-0 bg-white/95 backdrop-blur border-b border-slate-200/60"
                                >
                                    <input
                                        v-model="originQuery"
                                        placeholder="Buscar ciudad…"
                                        class="w-full h-9 rounded-lg border px-3 bg-white border-slate-300 outline-none"
                                    />
                                </div>
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
                                class="z-50 mt-1 max-h-60 w-64 overflow-auto rounded-xl bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-none sm:text-sm"
                            >
                                <div
                                    class="px-3 py-2 sticky top-0 bg-white/95 backdrop-blur border-b border-slate-200/60"
                                >
                                    <input
                                        v-model="destQuery"
                                        placeholder="Buscar ciudad…"
                                        class="w-full h-9 rounded-lg border px-3 bg-white border-slate-300 outline-none"
                                    />
                                </div>
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
                            </ListboxOptions>
                        </Teleport>
                    </Listbox>
                </div>

                <!-- Fecha -->
                <div>
                    <label class="block text-xs text-slate-500 mb-2"
                        >Fecha</label
                    >
                    <Popover class="relative" v-slot="{ close }">
                        <PopoverButton
                            ref="datePickerButtonRef"
                            class="relative w-full cursor-default rounded-xl bg-white border border-slate-300 h-11 pl-10 pr-3 text-left focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center"
                        >
                            <CalendarDays
                                class="absolute left-3 w-5 h-5 text-slate-400"
                            />
                            <span
                                :class="{ 'text-slate-400': !date }"
                                class="truncate"
                                >{{ formattedDate }}</span
                            >
                        </PopoverButton>

                        <Teleport to="body">
                            <PopoverPanel
                                ref="datePickerPanelRef"
                                :style="datePickerFloatingStyles"
                                class="z-50 focus:outline-none"
                            >
                                <VDatePicker
                                    v-model="date"
                                    :is-dark="isDark"
                                    :min-date="new Date()"
                                    @update:model-value="close()"
                                />
                            </PopoverPanel>
                        </Teleport>
                    </Popover>
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
                class="rounded-2xl border border-slate-200 p-4 transition-transform hover:-translate-y-0.5 hover:shadow-lg"
            >
                <header class="flex justify-between">
                    <h3 class="font-semibold">
                        {{ f.origin.name }} → {{ f.destination.name }}
                    </h3>
                    <span class="text-sm">{{
                        new Date(f.departure_at).toLocaleString()
                    }}</span>
                </header>
                <p class="mt-2 text-sm">
                    Precio por silla:
                    <b>${{ (+f.price_per_seat).toLocaleString() }}</b>
                </p>
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

        <!-- Modal de Pasajeros -->
        <PassengersModal
            v-model:open="passengersOpen"
            @submit="onPassengersSubmit"
        />

        <!-- Modal de Pago -->
        <PaymentModal
            v-model:open="paymentOpen"
            :total-amount="pendingBooking?.total_amount || 0"
            @payment-success="onPaymentSuccess"
        />
    </section>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import {
    Popover,
    PopoverButton,
    PopoverPanel,
    Listbox,
    ListboxButton,
    ListboxOptions,
    ListboxOption,
} from "@headlessui/vue";
import { DatePicker as VDatePicker } from "v-calendar";
import "v-calendar/style.css";
import {
    MapPin,
    ChevronsUpDown,
    Check,
    Search,
    CalendarDays,
} from "lucide-vue-next";
import { useFloating, autoUpdate, offset } from "@floating-ui/vue";
import { useDark } from "@vueuse/core";
import { api } from "../../lib/api";
import { useAuth } from "../../stores/auth";
import { useUi } from "../../stores/ui";
import PassengersModal from "../booking/PassengersModal.vue";
import PaymentModal from "../booking/PaymentModal.vue";

const isDark = useDark();
const auth = useAuth();
const ui = useUi();

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
const classes = [
    { id: "economy", name: "Economy" },
    { id: "first", name: "First" },
];
const selectedClass = ref(classes[0]);
const formattedDate = computed(() =>
    date.value
        ? date.value.toLocaleDateString("es-ES", {
              day: "2-digit",
              month: "2-digit",
              year: "numeric",
          })
        : "Selecciona una fecha"
);

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
const datePickerButtonRef = ref(null);
const datePickerPanelRef = ref(null);
const { floatingStyles: datePickerFloatingStyles } = useFloating(
    datePickerButtonRef,
    datePickerPanelRef,
    {
        whileElementsMounted: autoUpdate,
        placement: "bottom-start",
        middleware: [offset(8)],
    }
);

// Resultados
const loading = ref(false);
const resultsLoading = ref(false);
const results = ref({ data: [], meta: null });
const actionLoading = ref(false);

// Estado de compra/reserva
const passengersOpen = ref(false);
const paymentOpen = ref(false);
const pendingAction = ref(null); // 'reservation' | 'purchase'
const currentFlight = ref(null);
const pendingPassengers = ref([]);
const pendingBooking = ref(null);

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
            date: date.value ? date.value.toISOString().slice(0, 10) : "",
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
    passengersOpen.value = true;
}
function tryBuy(f) {
    if (!ensureAuth()) return;
    ensureClass();
    currentFlight.value = f;
    pendingAction.value = "purchase";
    passengersOpen.value = true;
}

async function onPassengersSubmit(passengers) {
    if (!currentFlight.value || !pendingAction.value) return;

    pendingPassengers.value = passengers;

    // SIEMPRE abrir modal de pago (tanto para reserva como para compra)
    const total = passengers.length * currentFlight.value.price_per_seat;
    pendingBooking.value = {
        total_amount: total,
        passengers_count: passengers.length,
    };
    paymentOpen.value = true;
}

async function onPaymentSuccess(paymentData) {
    await processBooking(pendingPassengers.value, paymentData);
}

async function processBooking(passengers, paymentData) {
    if (!currentFlight.value || !pendingAction.value) return;
    actionLoading.value = true;

    try {
        const payload = {
            flight_id: currentFlight.value.id,
            type: pendingAction.value,
            class: selectedClass.value.id,
            passengers,
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
            ? `✅ ¡Compra realizada exitosamente!\n\nCódigo de Reserva: ${booking.reservation_code}\n\nRevisa tu correo para más detalles. Redirigiendo a Mis Viajes...`
            : `✅ ¡Reserva creada exitosamente!\n\nCódigo de Reserva: ${booking.reservation_code}\n\nTienes 24 horas para completar tu compra. Recibirás un recordatorio por correo.\n\nRedirigiendo a Mis Viajes...`;

        alert(message);

        // Redirigir a Mis Viajes después de 2 segundos
        setTimeout(() => {
            window.location.href = "/mis-viajes";
        }, 2000);
    } catch (e) {
        const errorMessage =
            e.response?.data?.message ||
            e.response?.data?.error ||
            "No se pudo procesar la solicitud.";
        alert(`❌ Error: ${errorMessage}`);
    } finally {
        actionLoading.value = false;
    }
}
</script>
