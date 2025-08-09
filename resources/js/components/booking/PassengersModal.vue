<template>
    <div
        v-if="open"
        class="fixed inset-0 z-[60] grid place-items-center bg-slate-900/40"
        @click.self="close()"
    >
        <div
            class="w-full max-w-xl rounded-2xl bg-white dark:bg-slate-950 p-6 shadow-lg"
        >
            <h3 class="text-lg font-semibold mb-4">Datos de pasajeros</h3>
            <div class="space-y-3 max-h-[60vh] overflow-auto">
                <div
                    v-for="(p, idx) in passengers"
                    :key="idx"
                    class="grid grid-cols-2 gap-2 border rounded-xl p-3"
                >
                    <input
                        v-model="p.dni"
                        placeholder="Documento"
                        class="h-10 rounded border px-3 col-span-1"
                    />
                    <input
                        v-model="p.first_name"
                        placeholder="Nombres"
                        class="h-10 rounded border px-3 col-span-1"
                    />
                    <input
                        v-model="p.last_name"
                        placeholder="Apellidos"
                        class="h-10 rounded border px-3 col-span-1"
                    />
                    <input
                        v-model="p.birth_date"
                        type="date"
                        class="h-10 rounded border px-3 col-span-1"
                    />
                    <select
                        v-model="p.gender"
                        class="h-10 rounded border px-3 col-span-1"
                    >
                        <option value="">Género</option>
                        <option>M</option>
                        <option>F</option>
                        <option>X</option>
                    </select>
                    <input
                        v-model="p.email"
                        placeholder="Email (opcional)"
                        class="h-10 rounded border px-3 col-span-1"
                    />
                    <input
                        v-model="p.phone"
                        placeholder="Teléfono (opcional)"
                        class="h-10 rounded border px-3 col-span-1"
                    />
                    <input
                        v-model="p.emergency_contact_name"
                        placeholder="Contacto emergencia"
                        class="h-10 rounded border px-3 col-span-1"
                    />
                    <input
                        v-model="p.emergency_contact_phone"
                        placeholder="Teléfono contacto"
                        class="h-10 rounded border px-3 col-span-1"
                    />
                    <button
                        class="col-span-2 text-sm text-rose-600 underline"
                        @click="remove(idx)"
                        v-if="passengers.length > 1"
                    >
                        Quitar
                    </button>
                </div>
                <button class="h-10 rounded-xl border px-4" @click="add">
                    + Añadir pasajero
                </button>
                <p v-if="error" class="text-sm text-rose-600">{{ error }}</p>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button class="h-10 rounded-xl border px-4" @click="close">
                    Cancelar
                </button>
                <button
                    class="h-10 rounded-xl bg-blue-600 text-white px-5"
                    @click="submit"
                >
                    Continuar
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from "vue";
const props = defineProps({ open: Boolean });
const emit = defineEmits(["update:open", "submit"]);
const passengers = reactive([blank()]);
const error = ref("");
function blank() {
    return {
        dni: "",
        first_name: "",
        last_name: "",
        birth_date: "",
        gender: "",
        email: "",
        phone: "",
        emergency_contact_name: "",
        emergency_contact_phone: "",
    };
}
function add() {
    if (passengers.length < 5) passengers.push(blank());
}
function remove(i) {
    passengers.splice(i, 1);
}
function close() {
    emit("update:open", false);
}
function submit() {
    const hasMinor = passengers.some((p) => age(p.birth_date) < 18);
    const hasAdult = passengers.some((p) => age(p.birth_date) >= 18);
    if (hasMinor && !hasAdult) {
        error.value = "Menores no pueden viajar solos.";
        return;
    }
    emit("submit", passengers);
    close();
}
function age(d) {
    if (!d) return 999;
    const a = new Date(d);
    const diff = Date.now() - a;
    return Math.abs(new Date(diff).getUTCFullYear() - 1970);
}
</script>
