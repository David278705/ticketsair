<template>
    <div class="space-y-3">
        <div class="grid grid-cols-6 gap-2">
            <button
                v-for="s in visibleSeats"
                :key="s.id"
                class="h-10 rounded-lg border text-sm"
                :class="seatClass(s)"
                :disabled="s.status !== 'available' || disabled"
                @click="$emit('select', s)"
            >
                {{ s.number }}
            </button>
        </div>
        <div class="flex gap-4 text-xs text-slate-500">
            <span class="inline-flex items-center gap-1"
                ><i class="size-3 rounded bg-white border"></i> Libre</span
            >
            <span class="inline-flex items-center gap-1"
                ><i class="size-3 rounded bg-slate-300"></i> Ocupado</span
            >
            <span class="inline-flex items-center gap-1"
                ><i class="size-3 rounded bg-blue-200"></i> Tu asiento</span
            >
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { api } from "../../lib/api";
const props = defineProps({
    flightId: Number,
    selectedClass: String,
    disabled: Boolean,
});
const seats = ref([]);
onMounted(async () => {
    const { data } = await api.get(`/flights/${props.flightId}/seats`);
    seats.value = data;
});
const visibleSeats = computed(() =>
    seats.value.filter((s) => s.class === props.selectedClass)
);
function seatClass(s) {
    if (s.status === "available")
        return "bg-white  border-slate-300  hover:bg-blue-50";
    if (s.__mine) return "bg-blue-200  border-blue-300 ";
    return "bg-slate-200  border-slate-300  cursor-not-allowed";
}
</script>
