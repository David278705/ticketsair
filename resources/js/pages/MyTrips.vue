<template>
    <section class="container py-8">
        <h1 class="text-2xl font-bold mb-4">Mis viajes</h1>

        <div v-if="loading" class="text-sm text-slate-500">Cargando...</div>

        <div v-else>
            <div v-if="!list.data?.length" class="text-sm text-slate-500">
                No tienes reservas/compras aún.
            </div>

            <div class="grid gap-4">
                <article
                    v-for="b in list.data"
                    :key="b.id"
                    class="rounded-2xl border border-slate-200 dark:border-slate-800 p-4"
                >
                    <header
                        class="flex flex-wrap items-center justify-between gap-2"
                    >
                        <div>
                            <h3 class="font-semibold">
                                {{ b.flight.origin.name }} →
                                {{ b.flight.destination.name }}
                                <span
                                    class="text-xs ml-2 px-2 py-0.5 rounded-full border"
                                    :class="chip(b)"
                                    >{{ b.type }} / {{ b.status }}</span
                                >
                            </h3>
                            <p class="text-sm text-slate-500">
                                Sale: {{ fmt(b.flight.departure_at) }} · Llega:
                                {{ fmt(b.flight.arrival_at) }}
                            </p>
                        </div>
                        <div class="text-right">
                            <div class="text-sm">
                                Total:
                                <b>${{ (+b.total_amount).toLocaleString() }}</b>
                            </div>
                            <div class="text-xs text-slate-500">
                                Pax: {{ b.seats_count }}
                            </div>
                        </div>
                    </header>

                    <!-- Pasajeros -->
                    <div class="mt-3 overflow-auto">
                        <table class="min-w-[640px] w-full text-sm">
                            <thead class="text-slate-500">
                                <tr>
                                    <th class="text-left py-2">Pasajero</th>
                                    <th class="text-left">Documento</th>
                                    <th class="text-left">Asiento</th>
                                    <th class="text-left">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="p in b.passengers"
                                    :key="p.id"
                                    class="border-t"
                                >
                                    <td class="py-2">
                                        {{ p.first_name }} {{ p.last_name }}
                                    </td>
                                    <td>{{ p.dni }}</td>
                                    <td>
                                        {{ p.seat ? p.seat.number : "—" }}
                                        <span
                                            class="uppercase text-xs text-slate-400"
                                            >({{ p.class }})</span
                                        >
                                    </td>
                                    <td class="flex flex-wrap gap-2 py-2">
                                        <button
                                            v-if="canChangeSeat(b, p)"
                                            class="h-9 px-3 rounded-lg border"
                                            @click="openSeat(b, p)"
                                        >
                                            Cambiar silla
                                        </button>
                                        <button
                                            v-if="canCheckin(b, p)"
                                            class="h-9 px-3 rounded-lg bg-blue-600 text-white"
                                            @click="checkin(b, p)"
                                        >
                                            Check-in
                                        </button>
                                        <a
                                            v-if="ticketPdf(b, p)"
                                            class="h-9 px-3 rounded-lg border inline-flex items-center"
                                            :href="ticketPdf(b, p)"
                                            target="_blank"
                                            >Pasabordo</a
                                        >
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Acciones booking -->
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button
                            v-if="canCancel(b)"
                            class="h-10 px-4 rounded-xl border"
                            @click="cancel(b)"
                        >
                            Cancelar
                        </button>
                    </div>
                </article>
            </div>

            <!-- Paginación -->
            <div
                v-if="list?.meta?.links?.length"
                class="mt-6 flex flex-wrap gap-2"
            >
                <button
                    v-for="l in list.meta.links"
                    :key="l.label"
                    :disabled="!l.url"
                    @click="go(l.url)"
                    class="px-3 h-9 rounded-lg border"
                    v-html="l.label"
                />
            </div>
        </div>
    </section>

    <!-- Modal cambio de asiento -->
    <SeatChangeModal
        v-model:open="seatOpen"
        :flight-id="seatFlightId"
        :booking-passenger-id="seatPassengerId"
        :klass="seatClass"
        :mine-seat-ids="mineSeatIds"
        :disabled="seatDisabled"
        :token="auth.token"
        @changed="reload()"
    />
</template>

<script setup>
import { ref, onMounted } from "vue";
import { api } from "../lib/api";
import { useAuth } from "../stores/auth";
import SeatChangeModal from "../components/seats/SeatChangeModal.vue";

const auth = useAuth();

const list = ref({ data: [], meta: null });
const loading = ref(false);

async function load(url) {
    loading.value = true;
    try {
        const { data } = await api.get(url || "/me/bookings", {
            headers: { Authorization: "Bearer " + auth.token },
        });
        list.value = data;
    } finally {
        loading.value = false;
    }
}
onMounted(() => load());

function go(url) {
    const u = new URL(url);
    load(u.pathname + u.search);
}
function fmt(d) {
    return new Date(d).toLocaleString();
}

function chip(b) {
    const base = "ml-2";
    if (b.status === "confirmed")
        return base + " border-emerald-300 text-emerald-700";
    if (b.status === "pending")
        return base + " border-amber-300 text-amber-700";
    if (b.status === "cancelled")
        return base + " border-rose-300 text-rose-700";
    if (b.status === "expired")
        return base + " border-slate-300 text-slate-500";
    return base + " border-slate-300 text-slate-500";
}

// Reglas UI
function flightNotDeparted(b) {
    return new Date(b.flight.departure_at) > new Date();
}
function canCancel(b) {
    if (b.type === "purchase")
        return (
            flightNotDeparted(b) &&
            new Date(b.flight.departure_at) - new Date() > 60 * 60 * 1000
        );
    if (b.type === "reservation")
        return b.status === "pending" && new Date(b.expires_at) > new Date();
    return false;
}
function passengerTicket(b, p) {
    return b.tickets?.find((t) => t.booking_passenger_id === p.id);
}
function canCheckin(b, p) {
    const t = passengerTicket(b, p);
    return (
        b.type === "purchase" &&
        b.status === "confirmed" &&
        flightNotDeparted(b) &&
        t &&
        t.status !== "checked_in"
    );
}
function ticketPdf(b, p) {
    const t = passengerTicket(b, p);
    return t?.boarding_pass_pdf_path
        ? `/storage/${t.boarding_pass_pdf_path}`
        : null;
}
function canChangeSeat(b, p) {
    const t = passengerTicket(b, p);
    return (
        b.type === "purchase" &&
        b.status === "confirmed" &&
        flightNotDeparted(b) &&
        t &&
        t.status !== "checked_in" &&
        !p.seat_changed_once
    );
}

// Acciones
async function cancel(b) {
    if (!confirm("¿Seguro que deseas cancelar?")) return;
    await api.post(
        `/bookings/${b.id}/cancel`,
        {},
        { headers: { Authorization: "Bearer " + auth.token } }
    );
    await load();
}
async function checkin(b, p) {
    const t = passengerTicket(b, p);
    if (!t) return alert("No hay ticket");
    try {
        await api.post("/checkin/fast", { ticket_code: t.ticket_code });
        alert("Check-in exitoso. Se generará tu pasabordo.");
        await load();
    } catch (e) {
        alert("No se pudo hacer check-in");
    }
}

// Modal cambio de asiento
const seatOpen = ref(false);
const seatFlightId = ref(null);
const seatPassengerId = ref(null);
const seatClass = ref("economy");
const seatDisabled = ref(false);
const mineSeatIds = ref([]);

function openSeat(b, p) {
    seatFlightId.value = b.flight.id;
    seatPassengerId.value = p.id;
    seatClass.value = p.class;
    seatDisabled.value = false;
    mineSeatIds.value = p.seat ? [p.seat.id] : [];
    seatOpen.value = true;
}
function reload() {
    load();
}
</script>
