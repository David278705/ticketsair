<template>
    <div
        v-if="open"
        class="fixed inset-0 z-[60] grid place-items-center bg-slate-900/40"
        @click.self="close()"
    >
        <div
            class="w-full max-w-2xl rounded-2xl bg-white dark:bg-slate-950 p-6 shadow-lg"
        >
            <h3 class="text-lg font-semibold mb-2">Cambiar asiento</h3>
            <p class="text-sm text-slate-500 mb-4">
                Clase: <b class="uppercase">{{ klass }}</b>
            </p>
            <SeatMap
                :flight-id="flightId"
                :selected-class="klass"
                :mine-seat-ids="mineSeatIds"
                :disabled="disabled"
                @select="onSelect"
            />
            <p
                v-if="msg"
                class="mt-3 text-sm"
                :class="ok ? 'text-emerald-600' : 'text-rose-600'"
            >
                {{ msg }}
            </p>
            <div class="mt-5 flex justify-end gap-2">
                <button class="h-10 rounded-xl border px-4" @click="close">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import SeatMap from "./SeatMap.vue";
import { ref } from "vue";
import { api } from "../../lib/api";
const props = defineProps({
    open: Boolean,
    flightId: Number,
    bookingPassengerId: Number,
    klass: String,
    mineSeatIds: { type: Array, default: () => [] },
    disabled: Boolean,
    token: String,
});
const emit = defineEmits(["update:open", "changed"]);
const msg = ref("");
const ok = ref(false);
function close() {
    emit("update:open", false);
}
async function onSelect(seat) {
    msg.value = "Procesando...";
    ok.value = false;
    try {
        await api.post(
            "/seat-change",
            {
                booking_passenger_id: props.bookingPassengerId,
                to_seat_id: seat.id,
            },
            { headers: { Authorization: "Bearer " + props.token } }
        );
        msg.value = "Asiento cambiado ✅";
        ok.value = true;
        emit("changed");
    } catch (e) {
        msg.value = e.response?.data?.error || "No se pudo cambiar el asiento";
    }
}
</script>
