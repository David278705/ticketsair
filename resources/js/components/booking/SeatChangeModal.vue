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
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="ease-in duration-150"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel
                            class="w-full max-w-2xl transform rounded-2xl bg-white dark:bg-slate-950 p-6 shadow-xl transition-all"
                        >
                            <DialogTitle class="text-lg font-semibold mb-2"
                                >Cambiar asiento</DialogTitle
                            >
                            <p class="text-sm text-slate-500 mb-4">
                                Clase: <b class="uppercase">{{ klass }}</b
                                >. Selecciona un asiento disponible.
                            </p>

                            <div v-if="seats.length" class="space-y-3">
                                <div class="grid grid-cols-6 gap-2">
                                    <button
                                        v-for="s in visibleSeats"
                                        :key="s.id"
                                        class="h-10 rounded-lg border text-xs"
                                        :class="seatClass(s)"
                                        :disabled="s.status !== 'available'"
                                        @click="onSelect(s)"
                                    >
                                        {{ s.number }}
                                    </button>
                                </div>
                                <div
                                    class="flex flex-wrap gap-4 text-xs text-slate-500"
                                >
                                    <span class="inline-flex items-center gap-1"
                                        ><i
                                            class="size-3 rounded-sm bg-white border"
                                        ></i>
                                        Libre</span
                                    >
                                    <span class="inline-flex items-center gap-1"
                                        ><i
                                            class="size-3 rounded-sm bg-slate-300"
                                        ></i>
                                        Ocupado</span
                                    >
                                    <span class="inline-flex items-center gap-1"
                                        ><i
                                            class="size-3 rounded-sm bg-blue-500"
                                        ></i>
                                        Tu asiento</span
                                    >
                                </div>
                            </div>
                            <div v-else class="text-center text-slate-500 py-4">
                                Cargando asientos...
                            </div>

                            <p
                                v-if="message"
                                class="mt-4 text-sm font-medium"
                                :class="
                                    isError
                                        ? 'text-rose-500'
                                        : 'text-emerald-500'
                                "
                            >
                                {{ message }}
                            </p>

                            <div class="mt-5 flex justify-end">
                                <button
                                    class="h-10 rounded-xl border px-5"
                                    @click="close"
                                >
                                    Cerrar
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
import { ref, computed, watch } from "vue";
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionRoot,
    TransitionChild,
} from "@headlessui/vue";
import { api } from "../../lib/api";
import { useAuth } from "../../stores/auth";

const props = defineProps({
    open: Boolean,
    flightId: Number,
    bookingPassengerId: Number,
    klass: String,
    currentSeatId: Number,
});
const emit = defineEmits(["update:open", "seatChanged"]);

const auth = useAuth();
const seats = ref([]);
const message = ref("");
const isError = ref(false);

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen && props.flightId) {
            fetchSeats();
            message.value = "";
            isError.value = false;
        }
    }
);

const visibleSeats = computed(() =>
    seats.value.filter((s) => s.class === props.klass)
);

async function fetchSeats() {
    seats.value = [];
    const { data } = await api.get(`/flights/${props.flightId}/seats`);
    seats.value = data;
}

function seatClass(seat) {
    if (seat.id === props.currentSeatId)
        return "bg-blue-500 text-white border-blue-600";
    if (seat.status === "available")
        return "bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 hover:bg-blue-100 dark:hover:bg-blue-900";
    return "bg-slate-300 dark:bg-slate-700 border-slate-400 dark:border-slate-600 cursor-not-allowed";
}

async function onSelect(seat) {
    message.value = "Procesando cambio...";
    isError.value = false;
    try {
        await api.post(
            "/seat-change",
            {
                booking_passenger_id: props.bookingPassengerId,
                to_seat_id: seat.id,
            },
            {
                headers: { Authorization: "Bearer " + auth.token },
            }
        );
        message.value = "¡Asiento cambiado con éxito!";
        emit("seatChanged");
        setTimeout(() => close(), 1500); // Cierra el modal tras el éxito
    } catch (e) {
        isError.value = true;
        message.value =
            e.response?.data?.error ||
            e.response?.data?.message ||
            "No se pudo cambiar el asiento.";
    }
}

function close() {
    emit("update:open", false);
}
</script>
