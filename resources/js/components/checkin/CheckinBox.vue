<template>
    <section class="container mt-12">
        <h2 class="text-xl font-semibold mb-3">Check-in rápido</h2>
        <form class="flex flex-col sm:flex-row gap-3" @submit.prevent="submit">
            <input
                v-model="code"
                placeholder="Código de reserva o Ticket"
                class="h-11 rounded-xl border px-3 flex-1"
            />
            <button class="h-11 px-5 rounded-xl bg-blue-600 text-white">
                Check-in
            </button>
        </form>
        <p v-if="msg" class="mt-3 text-sm">{{ msg }}</p>
    </section>
</template>

<script setup>
import { ref } from "vue";
import { api } from "../../lib/api";
const code = ref("");
const msg = ref("");
async function submit() {
    msg.value = "Procesando...";
    try {
        await api.post("/checkin/fast", { ticket_code: code.value });
        msg.value = "Check-in exitoso. Descarga tu pasabordo desde tu cuenta.";
    } catch (e) {
        msg.value = "No encontrado o ya registrado.";
    }
}
</script>
