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
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="font-semibold text-lg">
                                        {{ b.flight.origin.name }} →
                                        {{ b.flight.destination.name }}
                                    </h3>
                                    <!-- Badge de Reubicación -->
                                    <span
                                        v-if="b.relocated_from_flight_id"
                                        class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800"
                                    >
                                        ✈️ Reubicado
                                    </span>
                                </div>
                                <p class="text-sm text-slate-500">
                                    Sale:
                                    {{ formatDate(b.flight.departure_at) }}
                                </p>
                                <!-- Información del vuelo original si fue reubicado -->
                                <p
                                    v-if="
                                        b.relocated_from_flight_id &&
                                        b.original_flight
                                    "
                                    class="text-xs text-slate-400 mt-1"
                                >
                                    Vuelo original cancelado:
                                    {{ b.original_flight.origin.name }} →
                                    {{ b.original_flight.destination.name }}
                                    ({{
                                        formatDate(
                                            b.original_flight.departure_at
                                        )
                                    }})
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
                                                v-if="canCheckin(b, p)"
                                                @click="performCheckin(b, p)"
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
                            @click="buyNow(b)"
                            class="h-10 px-4 text-sm rounded-xl bg-blue-600 text-white hover:bg-blue-700"
                        >
                            💳 Completar Compra
                        </button>
                        <button
                            v-if="canCancel(b)"
                            @click="cancelBooking(b)"
                            class="h-10 px-4 text-sm rounded-xl border border-rose-300 text-rose-600 hover:bg-rose-50"
                        >
                            {{ getCancelButtonText(b) }}
                        </button>
                    </footer>
                </article>
            </div>
        </div>

        <!-- Modal de Check-in -->
        <CheckinModal
            v-if="selectedCheckinInfo"
            v-model:open="isCheckinModalOpen"
            :booking="selectedCheckinInfo.booking"
            :passenger="selectedCheckinInfo.passenger"
            :ticket="selectedCheckinInfo.ticket"
            :flight-id="selectedCheckinInfo.flightId"
            :klass="selectedCheckinInfo.klass"
            @checkin-success="fetchBookings"
        />

        <!-- Modal de Pago Unificado -->
        <UnifiedPaymentModal
            v-model:open="paymentOpen"
            :total-amount="selectedBooking?.total_amount || 0"
            :booking-info="{
                flight: selectedBooking?.flight,
                passengers_count: selectedBooking?.passengers?.length || 0,
                class: selectedBooking?.passengers?.[0]?.class || 'economy',
                action: 'purchase',
            }"
            @payment-success="convertToPurchase"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { api } from "../lib/api";
import { useAuth } from "../stores/auth";
import { useCurrency } from "../composables/useCurrency";
import { useSweetAlert } from "../composables/useSweetAlert";
import CheckinModal from "../components/booking/CheckinModal.vue";
import UnifiedPaymentModal from "../components/booking/UnifiedPaymentModal.vue";

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

// Estados para el modal de check-in
const isCheckinModalOpen = ref(false);
const selectedCheckinInfo = ref(null);

// Estados para el modal de pago unificado
const paymentOpen = ref(false);
const selectedBooking = ref(null);

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
    // Nunca mostrar el botón si ya está cancelada o expirada
    if (booking.status === "cancelled" || booking.status === "expired") {
        return false;
    }

    // Lógica estándar para todos los vuelos (reubicados o no)
    if (isFlightDeparted(booking)) return false;
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
    if (!ticket || ticket.status !== "issued" || isFlightDeparted(booking)) {
        return false;
    }

    // Validar ventana de check-in (24h nacional, 48h internacional)
    const departureDate = new Date(booking.flight.departure_at);
    const now = new Date();
    const hoursBeforeFlight = (departureDate - now) / (1000 * 60 * 60);
    const requiredHours = booking.flight.scope === "international" ? 48 : 24;

    return hoursBeforeFlight <= requiredHours && hoursBeforeFlight > 0;
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

function getCancelButtonText(booking) {
    // Si es un vuelo reubicado, el botón debe decir "Recibir reembolso"
    if (booking.relocated_from_flight_id) {
        return "Recibir reembolso";
    }
    // En caso contrario, usar el texto estándar
    return `Cancelar ${booking.type === "purchase" ? "Compra" : "Reserva"}`;
}

// --- Acciones del Usuario ---

async function buyNow(booking) {
    // Abrimos el modal de pago unificado
    selectedBooking.value = booking;
    paymentOpen.value = true;
}

async function convertToPurchase(paymentData) {
    if (!selectedBooking.value) return;

    try {
        await api.post(
            `/bookings/${selectedBooking.value.id}/convert-to-purchase`,
            { payment: paymentData },
            { headers: { Authorization: "Bearer " + auth.token } }
        );
        await success(
            "¡Compra completada exitosamente!",
            "Recibirás un correo de confirmación."
        );
        paymentOpen.value = false;
        selectedBooking.value = null;
        fetchBookings();
    } catch (e) {
        showError(
            "Error al completar la compra",
            e.response?.data?.message || e.message
        );
    }
}

async function cancelBooking(booking) {
    // Mensajes personalizados para vuelos reubicados
    const isRelocated = booking.relocated_from_flight_id;
    const title = isRelocated
        ? "¿Solicitar reembolso?"
        : "¿Confirmar cancelación?";
    const message = isRelocated
        ? "Recibirás un reembolso completo en tu billetera por este vuelo reubicado."
        : `¿Estás seguro de que quieres cancelar esta ${booking.type}?`;
    const confirmText = isRelocated ? "Sí, recibir reembolso" : "Sí, cancelar";

    const confirmed = await showConfirm(title, message, confirmText, "No");

    if (confirmed) {
        try {
            await api.post(
                `/bookings/${booking.id}/cancel`,
                {},
                { headers: { Authorization: "Bearer " + auth.token } }
            );

            const successTitle = isRelocated
                ? "Reembolso procesado"
                : "Solicitud procesada";
            const successMessage = isRelocated
                ? "El reembolso ha sido acreditado en tu billetera."
                : "La solicitud ha sido procesada.";

            await info(successTitle, successMessage);
            fetchBookings();
        } catch (e) {
            showError(
                "Error al cancelar",
                e.response?.data?.message || e.message
            );
        }
    }
}

async function performCheckin(booking, passenger) {
    const ticket = findTicketForPassenger(passenger);
    if (!ticket) {
        showError("Error", "No se encontró el tiquete para este pasajero.");
        return;
    }

    // Validar ventana de check-in antes de abrir modal
    const departureDate = new Date(booking.flight.departure_at);
    const now = new Date();
    const hoursBeforeFlight = (departureDate - now) / (1000 * 60 * 60);
    const requiredHours = booking.flight.scope === "international" ? 48 : 24;

    if (hoursBeforeFlight > requiredHours) {
        const checkInDate = new Date(
            departureDate.getTime() - requiredHours * 60 * 60 * 1000
        );
        showError(
            "Check-in no disponible",
            `El check-in estará disponible ${requiredHours} horas antes del vuelo (${checkInDate.toLocaleString(
                "es-CO",
                { dateStyle: "short", timeStyle: "short" }
            )})`
        );
        return;
    }

    if (hoursBeforeFlight < 0) {
        showError("Error", "El vuelo ya ha partido");
        return;
    }

    // Abrir modal de check-in con selección de asiento y equipaje
    selectedCheckinInfo.value = {
        booking,
        passenger,
        ticket,
        flightId: booking.flight.id,
        klass: passenger.class,
    };
    isCheckinModalOpen.value = true;
}
</script>
