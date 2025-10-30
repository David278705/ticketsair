<template>
    <div class="bg-slate-50 min-h-screen">
        <div class="container py-8 sm:py-12">
            <h1 class="text-3xl font-bold mb-6">Mis Viajes</h1>

            <div v-if="loading" class="text-center text-slate-500">
                Cargando tus viajes...
            </div>
            <div
                v-else-if="!bookings.data?.length"
                class="text-center text-slate-500 p-8 border rounded-xl bg-white"
            >
                No tienes ninguna reserva o compra realizada.
            </div>
            <div v-else class="grid gap-6">
                <article
                    v-for="b in bookings.data"
                    :key="b.id"
                    class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden"
                >
                    <header class="p-4 sm:p-6 border-b border-slate-200">
                        <div
                            class="flex flex-wrap items-center justify-between gap-2"
                        >
                            <div>
                                <h3 class="font-semibold text-lg">
                                    {{ b.flight.origin.name }} →
                                    {{ b.flight.destination.name }}
                                </h3>
                                <p class="text-sm text-slate-500">
                                    Sale:
                                    {{ formatDate(b.flight.departure_at) }}
                                </p>
                            </div>
                            <div class="text-right">
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full"
                                    :class="statusClass(b.status)"
                                >
                                    {{ b.type }} / {{ b.status }}
                                </span>

                                <!-- Mostrar descuento si existe -->
                                <div
                                    v-if="b.discount_amount > 0"
                                    class="mt-2 space-y-1"
                                >
                                    <div class="text-xs text-slate-500">
                                        Precio original:
                                        <span class="line-through">
                                            {{
                                                formatPrice(+b.original_amount)
                                            }}
                                        </span>
                                    </div>
                                    <div
                                        class="text-xs text-green-600 font-semibold"
                                    >
                                        Descuento aplicado: -{{
                                            formatPrice(+b.discount_amount)
                                        }}
                                    </div>
                                    <div
                                        class="text-sm font-bold text-slate-900"
                                    >
                                        Total pagado:
                                        <span class="text-green-600">
                                            {{ formatPrice(+b.total_amount) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Sin descuento -->
                                <div v-else class="text-sm mt-1">
                                    Total:
                                    <b>{{ formatPrice(+b.total_amount) }}</b>
                                </div>
                            </div>
                        </div>
                    </header>

                    <div class="p-4 sm:p-6 overflow-x-auto">
                        <h4 class="font-semibold mb-2">Pasajeros</h4>
                        <table class="min-w-full w-full text-sm">
                            <thead class="text-left text-slate-500">
                                <tr>
                                    <th class="py-2 pr-2">Pasajero</th>
                                    <th class="py-2 px-2">Asiento</th>
                                    <th class="py-2 pl-2">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="p in b.passengers"
                                    :key="p.id"
                                    class="border-t border-slate-200"
                                >
                                    <td class="py-3 pr-2 font-medium">
                                        {{ p.first_name }} {{ p.last_name }}
                                        <br /><span
                                            class="font-normal text-xs text-slate-400"
                                            >{{ p.dni }}</span
                                        >
                                    </td>
                                    <td class="py-3 px-2">
                                        {{
                                            p.seat
                                                ? `${p.seat.number} (${p.class})`
                                                : "Sin asignar"
                                        }}
                                    </td>
                                    <td class="py-3 pl-2">
                                        <div class="flex flex-wrap gap-2">
                                            <button
                                                v-if="canChangeSeat(b, p)"
                                                @click="
                                                    openSeatChangeModal(b, p)
                                                "
                                                class="h-9 px-3 text-xs rounded-lg border hover:bg-slate-100"
                                            >
                                                Cambiar Asiento
                                            </button>
                                            <button
                                                v-if="canCheckin(b, p)"
                                                @click="performCheckin(p)"
                                                class="h-9 px-3 text-xs rounded-lg bg-blue-600 text-white hover:bg-blue-700"
                                            >
                                                Check-in
                                            </button>
                                            <a
                                                v-if="getBoardingPassUrl(p)"
                                                :href="getBoardingPassUrl(p)"
                                                target="_blank"
                                                class="h-9 px-3 text-xs rounded-lg border inline-flex items-center hover:bg-slate-100"
                                                >Ver Pasabordo</a
                                            >
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <footer
                        v-if="canCancel(b) || canConvertToPurchase(b)"
                        class="p-4 sm:p-6 border-t border-slate-200 bg-slate-50 flex flex-wrap gap-3"
                    >
                        <button
                            v-if="canConvertToPurchase(b)"
                            @click="convertToPurchase(b)"
                            class="h-10 px-4 text-sm rounded-xl bg-blue-600 text-white hover:bg-blue-700"
                        >
                            💳 Completar Compra
                        </button>
                        <button
                            v-if="canCancel(b)"
                            @click="cancelBooking(b)"
                            class="h-10 px-4 text-sm rounded-xl border border-rose-300 text-rose-600 hover:bg-rose-50"
                        >
                            Cancelar
                            {{ b.type === "purchase" ? "Compra" : "Reserva" }}
                        </button>
                    </footer>
                </article>
            </div>
        </div>
        <SeatChangeModal
            v-model:open="isSeatModalOpen"
            :flight-id="selectedPassengerInfo.flightId"
            :booking-passenger-id="selectedPassengerInfo.passengerId"
            :klass="selectedPassengerInfo.klass"
            :current-seat-id="selectedPassengerInfo.currentSeatId"
            @seatChanged="fetchBookings"
        />
    </div>
</template>

<script setup>
import { ref, onMounted, reactive } from "vue";
import { api } from "../lib/api";
import { useAuth } from "../stores/auth";
import { useCurrency } from "../composables/useCurrency";
import { useSweetAlert } from "../composables/useSweetAlert";
import SeatChangeModal from "../components/booking/SeatChangeModal.vue";

const auth = useAuth();
const { formatPrice } = useCurrency();
const {
    success,
    error: showError,
    confirm: showConfirm,
    info,
} = useSweetAlert();
const bookings = ref({ data: [] });
const loading = ref(true);

const isSeatModalOpen = ref(false);
const selectedPassengerInfo = reactive({
    flightId: null,
    passengerId: null,
    klass: null,
    currentSeatId: null,
});

onMounted(() => fetchBookings());

async function fetchBookings() {
    loading.value = true;
    try {
        const { data } = await api.get("/me/bookings", {
            headers: { Authorization: "Bearer " + auth.token },
        });
        bookings.value = data;
    } finally {
        loading.value = false;
    }
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleString("es-CO", {
        dateStyle: "long",
        timeStyle: "short",
    });
}

function statusClass(status) {
    const classes = {
        confirmed: "bg-emerald-100  text-emerald-800 ",
        pending: "bg-amber-100  text-amber-800 ",
        cancelled: "bg-rose-100  text-rose-800 ",
        expired: "bg-slate-100  text-slate-600 ",
    };
    return classes[status] || classes.expired;
}

// --- Lógica de Negocio para Acciones en la UI ---

function isFlightDeparted(booking) {
    return new Date(booking.flight.departure_at) < new Date();
}

function canCancel(booking) {
    if (
        isFlightDeparted(booking) ||
        booking.status === "cancelled" ||
        booking.status === "expired"
    )
        return false;
    if (booking.type === "reservation") return booking.status === "pending";
    if (booking.type === "purchase") {
        const hoursBeforeDeparture =
            (new Date(booking.flight.departure_at) - new Date()) /
            (1000 * 60 * 60);
        return hoursBeforeDeparture > 1;
    }
    return false;
}

function findTicketForPassenger(passenger) {
    const booking = bookings.value.data.find((b) =>
        b.passengers.some((p) => p.id === passenger.id)
    );
    return booking?.tickets.find(
        (t) => t.booking_passenger_id === passenger.id
    );
}

function findTicketForPassengerInBooking(booking, passenger) {
    return booking?.tickets?.find(
        (t) => t.booking_passenger_id === passenger.id
    );
}
// y úsalo donde ya tienes b y p

function canCheckin(booking, passenger) {
    const ticket = findTicketForPassenger(passenger);
    return ticket && ticket.status === "issued" && !isFlightDeparted(booking);
}

function canChangeSeat(booking, passenger) {
    const ticket = findTicketForPassenger(passenger);
    return (
        ticket &&
        ticket.status === "issued" &&
        !passenger.seat_changed_once &&
        !isFlightDeparted(booking)
    );
}

function getBoardingPassUrl(passenger) {
    const ticket = findTicketForPassenger(passenger);
    return ticket?.status === "checked_in" && ticket.boarding_pass_pdf_path
        ? `/storage/${ticket.boarding_pass_pdf_path}`
        : null;
}

function canConvertToPurchase(booking) {
    // Solo reservas pendientes que no hayan expirado
    if (booking.type !== "reservation" || booking.status !== "pending")
        return false;
    if (!booking.expires_at) return false;
    return new Date(booking.expires_at) > new Date();
}

// --- Acciones del Usuario ---

async function buyNow(booking) {
    const confirmed = await showConfirm(
        "¿Confirmar compra?",
        `¿Deseas completar la compra de esta reserva por ${formatPrice(
            +booking.total_amount
        )}?`,
        "Sí, comprar",
        "Cancelar"
    );

    if (confirmed) {
        try {
            await api.post(
                `/bookings/${booking.id}/convert-to-purchase`,
                {},
                { headers: { Authorization: "Bearer " + auth.token } }
            );
            await success(
                "¡Compra completada exitosamente!",
                "Recibirás un correo de confirmación."
            );
            fetchBookings();
        } catch (e) {
            showError(
                "Error al completar la compra",
                e.response?.data?.message || e.message
            );
        }
    }
}

async function cancelBooking(booking) {
    const confirmed = await showConfirm(
        "¿Confirmar cancelación?",
        `¿Estás seguro de que quieres cancelar esta ${booking.type}?`,
        "Sí, cancelar",
        "No"
    );

    if (confirmed) {
        try {
            await api.post(
                `/bookings/${booking.id}/cancel`,
                {},
                { headers: { Authorization: "Bearer " + auth.token } }
            );
            await info(
                "Solicitud procesada",
                "La solicitud ha sido procesada."
            );
            fetchBookings();
        } catch (e) {
            showError(
                "Error al cancelar",
                e.response?.data?.message || e.message
            );
        }
    }
}

async function performCheckin(passenger) {
    const ticket = findTicketForPassenger(passenger);
    if (!ticket) {
        showError("Error", "No se encontró el tiquete para este pasajero.");
        return;
    }
    try {
        await api.post("/checkin/fast", { ticket_code: ticket.ticket_code });
        await success("Check-in exitoso", "Tu pasabordo está disponible.");
        fetchBookings();
    } catch (e) {
        showError(
            "Error en el check-in",
            e.response?.data?.message || e.message
        );
    }
}

function openSeatChangeModal(booking, passenger) {
    selectedPassengerInfo.flightId = booking.flight.id;
    selectedPassengerInfo.passengerId = passenger.id;
    selectedPassengerInfo.klass = passenger.class;
    selectedPassengerInfo.currentSeatId = passenger.seat?.id;
    isSeatModalOpen.value = true;
}
</script>
