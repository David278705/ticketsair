<template>
    <section class="container">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <h2 class="text-2xl font-bold text-slate-800 mb-2">Check-in</h2>
            <p class="text-slate-600 mb-6">Ingresa tu código de reserva o número de documento</p>
            
            <form @submit.prevent="search" class="space-y-4">
                <div class="flex flex-col sm:flex-row gap-3">
                    <input
                        v-model="searchValue"
                        type="text"
                        placeholder="Código de reserva o cédula"
                        class="flex-1 h-12 px-4 rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none"
                        required
                    />
                    <button
                        type="submit"
                        :disabled="loading"
                        class="h-12 px-8 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ loading ? 'Buscando...' : 'Buscar' }}
                    </button>
                </div>

                <!-- Mensajes -->
                <div v-if="errorMessage" class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
                    {{ errorMessage }}
                </div>
                
                <div v-if="successMessage" class="p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm">
                    {{ successMessage }}
                </div>

                <!-- Selector de vuelos si hay múltiples -->
                <div v-if="availableFlights.length > 1" class="space-y-3">
                    <p class="text-sm font-medium text-slate-700">Selecciona el vuelo para hacer check-in:</p>
                    <div class="space-y-2">
                        <button
                            v-for="item in availableFlights"
                            :key="item.ticket.id"
                            type="button"
                            @click="performCheckin(item.ticket)"
                            class="w-full p-4 rounded-xl border-2 border-slate-200 hover:border-blue-500 hover:bg-blue-50 text-left transition-all"
                        >
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="font-semibold text-slate-800">
                                        {{ item.flight.origin?.name }} → {{ item.flight.destination?.name }}
                                    </div>
                                    <div class="text-sm text-slate-600 mt-1">
                                        Vuelo {{ item.flight.flight_number }} • {{ formatDate(item.flight.departure_at) }}
                                    </div>
                                    <div class="text-sm text-slate-500 mt-1">
                                        Pasajero: {{ item.passenger.first_name }} {{ item.passenger.last_name }}
                                    </div>
                                </div>
                                <div class="text-blue-600 font-medium">
                                    Seleccionar
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</template>

<script setup>
import { ref } from "vue";
import { api } from "../../lib/api";

const searchValue = ref("");
const loading = ref(false);
const errorMessage = ref("");
const successMessage = ref("");
const availableFlights = ref([]);

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', { 
        day: '2-digit', 
        month: 'short', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

async function search() {
    if (!searchValue.value.trim()) return;
    
    loading.value = true;
    errorMessage.value = "";
    successMessage.value = "";
    availableFlights.value = [];

    try {
        // Primero intentar buscar por código de reserva
        const response = await api.get(`/checkin/search`, {
            params: { search: searchValue.value.trim() }
        });

        const tickets = response.data.tickets || [];

        if (tickets.length === 0) {
            errorMessage.value = "No se encontraron reservas con este código o cédula";
        } else if (tickets.length === 1) {
            // Solo un vuelo, hacer check-in directamente
            await performCheckin(tickets[0]);
        } else {
            // Múltiples vuelos, mostrar selector
            availableFlights.value = tickets.map(ticket => ({
                ticket: ticket,
                flight: ticket.booking.flight,
                passenger: ticket.passenger
            }));
        }
    } catch (error) {
        errorMessage.value = error.response?.data?.message || "Error al buscar. Verifica el código o cédula ingresado";
    } finally {
        loading.value = false;
    }
}

async function performCheckin(ticket) {
    loading.value = true;
    errorMessage.value = "";
    successMessage.value = "";
    availableFlights.value = [];

    try {
        const response = await api.post('/checkin/fast', { 
            ticket_code: ticket.ticket_code 
        });

        successMessage.value = `Check-in exitoso. Se ha enviado tu pase de abordar a ${ticket.passenger.email}`;
        searchValue.value = "";
        
    } catch (error) {
        if (error.response?.data?.error === 'already_checked_in') {
            errorMessage.value = "Este pasajero ya tiene check-in realizado";
        } else if (error.response?.data?.error === 'too_early') {
            errorMessage.value = error.response.data.message;
        } else if (error.response?.data?.error === 'flight_departed') {
            errorMessage.value = "El vuelo ya ha partido";
        } else {
            errorMessage.value = error.response?.data?.message || "Error al realizar check-in";
        }
    } finally {
        loading.value = false;
    }
}
</script>
