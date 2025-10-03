<template>
    <section class="container mx-auto py-8 px-4">
        <header class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <h1 class="text-3xl font-bold text-slate-800">Gestión de Vuelos</h1>
            <div class="flex gap-2">
                <button
                    class="h-10 px-4 rounded-lg border bg-white hover:bg-slate-50 transition-colors flex items-center gap-2"
                    @click="openCreate()"
                >
                    <span class="text-xl">✈️</span>
                    Nuevo vuelo
                </button>
            </div>
        </header>

        <!-- Filtros -->
        <div class="rounded-xl border p-4 mb-6 grid gap-3 md:grid-cols-3 lg:grid-cols-5 bg-slate-50">
            <input
                v-model="filters.code"
                placeholder="Código de vuelo"
                class="h-10 rounded-lg border-slate-300 px-3"
            />
            <select v-model="filters.status" class="h-10 rounded-lg border-slate-300 px-3">
                <option value="">Todos los estados</option>
                <option value="scheduled">Programado</option>
                <option value="completed">Completado</option>
                <option value="cancelled">Cancelado</option>
            </select>
            <select v-model="filters.origin_id" class="h-10 rounded-lg border-slate-300 px-3">
                <option value="">Cualquier origen</option>
                <option v-for="c in cities" :key="c.id" :value="c.id">
                    {{ c.name }}
                </option>
            </select>
            <select v-model="filters.destination_id" class="h-10 rounded-lg border-slate-300 px-3">
                <option value="">Cualquier destino</option>
                <option v-for="c in cities" :key="c.id" :value="c.id">
                    {{ c.name }}
                </option>
            </select>
            <button
                class="h-10 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition-colors"
                @click="reload()"
            >
                Buscar
            </button>
        </div>

        <!-- Tabla de vuelos -->
        <div class="rounded-xl border overflow-x-auto">
            <table class="min-w-[1024px] w-full text-sm text-left">
                <thead class="bg-slate-100 text-slate-600">
                    <tr>
                        <th class="p-3 font-semibold">Código</th>
                        <th class="p-3 font-semibold">Ruta</th>
                        <th class="p-3 font-semibold">Avión</th>
                        <th class="p-3 font-semibold">Salida</th>
                        <th class="p-3 font-semibold">Duración</th>
                        <th class="p-3 font-semibold">Estado</th>
                        <th class="p-3 font-semibold">Precio Base</th>
                        <th class="p-3 font-semibold">Capacidad</th>
                        <th class="p-3 font-semibold">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-if="list.data.length === 0">
                        <td colspan="9" class="text-center p-8 text-slate-500">
                            No se encontraron vuelos con los filtros actuales.
                        </td>
                    </tr>
                    <tr v-for="f in list.data" :key="f.id" class="hover:bg-slate-50 transition-colors">
                        <td class="p-3 font-mono text-blue-600">{{ f.code }}</td>
                        <td class="p-3">
                            <span class="font-medium">{{ f.origin.name }}</span>
                            →
                            <span class="font-medium">{{ f.destination.name }}</span>
                        </td>
                        <td class="p-3">
                            <div class="text-xs">
                                <div class="font-medium">{{ f.aircraft_data?.name || 'N/A' }}</div>
                                <div class="text-slate-500">{{ f.aircraft_id || 'Sin asignar' }}</div>
                            </div>
                        </td>
                        <td class="p-3">{{ fmt(f.departure_at) }}</td>
                        <td class="p-3">{{ formatDuration(f.duration_minutes) }}</td>
                        <td class="p-3">
                            <span
                                class="px-2 py-1 rounded-full border text-xs font-medium"
                                :class="chip(f.status)"
                            >
                                {{ translateStatus(f.status) }}
                            </span>
                        </td>
                        <td class="p-3 font-medium">
                            ${{ (+f.price_per_seat).toLocaleString("es-CO") }}
                        </td>
                        <td class="p-3">{{ f.capacity_total }}</td>
                        <td class="p-3">
                            <div class="flex flex-wrap gap-2">
                                <button
                                    class="h-9 px-3 rounded-lg border text-sm hover:bg-slate-100"
                                    @click="openEdit(f)"
                                >
                                    Editar
                                </button>
                                <button
                                    class="h-9 px-3 rounded-lg border border-rose-300 text-rose-600 text-sm hover:bg-rose-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="f.status !== 'scheduled'"
                                    @click="cancelFlight(f)"
                                >
                                    Cancelar
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div v-if="list?.meta?.links?.length > 3" class="mt-6 flex justify-center flex-wrap gap-2">
            <button
                v-for="l in list.meta.links"
                :key="l.label"
                :disabled="!l.url"
                @click="go(l.url)"
                class="px-4 h-9 rounded-lg border text-sm font-medium transition-colors disabled:opacity-50"
                :class="{
                    'bg-blue-600 text-white border-blue-600': l.active,
                    'hover:bg-slate-100': !l.active,
                }"
                v-html="l.label"
            />
        </div>

        <!-- Modal Crear/Editar -->
        <BaseModal v-model:open="editOpen">
            <template #title>{{ form.id ? "Editar Vuelo" : "✈️ Nuevo Vuelo" }}</template>
            <div class="grid gap-4">
                <!-- Tipo de vuelo -->
                <div>
                    <label for="flight_scope" class="block text-sm font-medium text-gray-700 mb-1">
                        Tipo de Vuelo *
                    </label>
                    <select
                        id="flight_scope"
                        v-model="form.scope"
                        class="h-10 rounded-lg border px-3 w-full"
                        @change="filterAircraftByScope"
                    >
                        <option value="national">Nacional</option>
                        <option value="international">Internacional</option>
                    </select>
                </div>
                
                <!-- Origen y Destino -->
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label for="flight_origin" class="block text-sm font-medium text-gray-700 mb-1">
                            Ciudad de Origen *
                        </label>
                        <select
                            id="flight_origin"
                            v-model="form.origin_id"
                            class="h-10 rounded-lg border px-3 w-full"
                            @change="calculateFlightDuration"
                        >
                            <option value="">Selecciona origen</option>
                            <option v-for="c in filteredOriginCities" :key="c.id" :value="c.id">
                                {{ c.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label for="flight_destination" class="block text-sm font-medium text-gray-700 mb-1">
                            Ciudad de Destino *
                        </label>
                        <select
                            id="flight_destination"
                            v-model="form.destination_id"
                            class="h-10 rounded-lg border px-3 w-full"
                            @change="calculateFlightDuration"
                        >
                            <option value="">Selecciona destino</option>
                            <option 
                                v-for="c in filteredDestinationCities" 
                                :key="c.id" 
                                :value="c.id"
                                :disabled="c.id === form.origin_id"
                            >
                                {{ c.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Selección de avión -->
                <div>
                    <label for="flight_aircraft" class="block text-sm font-medium text-gray-700 mb-1">
                        Avión a Utilizar *
                    </label>
                    <select
                        id="flight_aircraft"
                        v-model="form.aircraft_id"
                        class="h-10 rounded-lg border px-3 w-full"
                        @change="calculateFlightDuration"
                    >
                        <option value="">Selecciona un avión</option>
                        <option v-for="aircraftItem in filteredAircraft" :key="aircraftItem.id" :value="aircraftItem.id">
                            {{ aircraftItem.name }} - Eco: {{ aircraftItem.capacity_economy }} asientos - {{ aircraftItem.average_speed_kmh }} km/h
                        </option>
                    </select>
                    <div v-if="selectedAircraft" class="mt-2 p-3 bg-blue-50 rounded-lg text-sm">
                        <div class="font-medium">{{ selectedAircraft.name }}</div>
                        <div class="text-slate-600 grid grid-cols-2 gap-2 mt-1">
                            <span>Velocidad: {{ selectedAircraft.average_speed_kmh }} km/h</span>
                            <span>Alcance: {{ selectedAircraft.range_km }} km</span>
                            <span class="font-medium text-blue-700">Económica: {{ selectedAircraft.capacity_economy }} asientos</span>
                            <span>Premium: {{ selectedAircraft.capacity_premium }} asientos</span>
                        </div>
                        <div v-if="calculatedInfo.duration_minutes > 0" class="mt-2 pt-2 border-t border-blue-200">
                            <div class="font-medium text-blue-800">Tiempo estimado de vuelo: {{ formatDuration(calculatedInfo.duration_minutes) }}</div>
                            <div v-if="calculatedInfo.arrival_time" class="text-blue-600 text-xs mt-1">
                                Llegada aproximada: {{ formatDateTime(calculatedInfo.arrival_time) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fecha de salida y información calculada -->
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label for="flight_departure" class="block text-sm font-medium text-gray-700 mb-1">
                            Fecha y Hora de Salida *
                        </label>
                        <input
                            id="flight_departure"
                            v-model="form.departure_at"
                            type="datetime-local"
                            :min="minDateTime"
                            class="h-10 rounded-lg border px-3 w-full"
                        />
                    </div>
                    <div>
                        <label for="flight_price" class="block text-sm font-medium text-gray-700 mb-1">
                            Precio por Asiento *
                        </label>
                        <input
                            id="flight_price"
                            v-model.number="form.price_per_seat"
                            type="number"
                            min="0"
                            max="9999999"
                            class="h-10 rounded-lg border px-3 w-full"
                            placeholder="ej: 150000"
                        />
                    </div>
                </div>

                <!-- Información calculada automáticamente -->
                <div v-if="calculatedInfo.duration_minutes" class="p-4 bg-green-50 rounded-lg">
                    <h4 class="font-medium text-green-800 mb-2">Información del Vuelo Calculada</h4>
                    <div class="text-sm text-green-700 grid grid-cols-2 gap-2">
                        <span>Duración estimada: {{ calculatedInfo.duration_minutes }} minutos</span>
                        <span>Distancia: {{ calculatedInfo.distance_km || 'N/A' }} km</span>
                        <span v-if="calculatedInfo.arrival_time">
                            Llegada estimada: {{ fmt(calculatedInfo.arrival_time) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Errores -->
            <ul
                v-if="formErrors.length"
                class="mt-4 text-rose-500 text-sm list-disc list-inside bg-rose-50 p-3 rounded-lg"
            >
                <li v-for="(e, i) in formErrors" :key="i">{{ e }}</li>
            </ul>

            <!-- Botones -->
            <div class="mt-6 flex justify-end gap-3">
                <button class="h-10 px-4 rounded-lg border" @click="editOpen = false">
                    Cerrar
                </button>
                <button
                    class="h-10 px-6 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 disabled:opacity-70"
                    @click="save"
                    :disabled="saving"
                >
                    {{ saving ? "Guardando..." : "Guardar" }}
                </button>
            </div>
        </BaseModal>
    </section>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { api } from '@/lib/api'
import BaseModal from '@/components/ui/BaseModal.vue'

// Estados reactivos
const list = ref({ data: [], meta: { links: [] } })
const cities = ref([])
const aircraft = ref({})
const editOpen = ref(false)
const saving = ref(false)
const loading = ref(false)

// Filtros
const filters = ref({
    code: '',
    status: '',
    origin_id: '',
    destination_id: ''
})

// Formulario
const form = ref({
    id: null,
    origin_id: '',
    destination_id: '',
    scope: 'national',
    departure_at: '',
    aircraft_id: '',
    price_per_seat: 0
})

// Información calculada
const calculatedInfo = ref({
    duration_minutes: 0,
    distance_km: 0,
    arrival_time: null
})

const formErrors = ref([])

// Computadas
const minDateTime = computed(() => {
    const now = new Date()
    now.setHours(now.getHours() + 1)
    return now.toISOString().slice(0, 16)
})

const filteredOriginCities = computed(() => {
    if (form.value.scope === 'national') {
        return cities.value.filter(c => c.scope === 'national')
    } else {
        return cities.value.filter(c => c.scope === 'national') // Origen siempre nacional para internacionales
    }
})

const filteredDestinationCities = computed(() => {
    if (form.value.scope === 'national') {
        return cities.value.filter(c => c.scope === 'national' && c.id !== form.value.origin_id)
    } else {
        return cities.value.filter(c => c.scope === 'international')
    }
})

const filteredAircraft = computed(() => {
    if (!aircraft.value || Object.keys(aircraft.value).length === 0) {
        return []
    }
    
    const aircraftList = Object.values(aircraft.value)
    
    if (form.value.scope === 'national') {
        return aircraftList.filter(a => a.suitable_for && a.suitable_for.includes('national'))
    } else {
        return aircraftList.filter(a => 
            a.suitable_for && (
                a.suitable_for.includes('international_short') || 
                a.suitable_for.includes('international_medium') || 
                a.suitable_for.includes('international_long')
            )
        )
    }
})

const selectedAircraft = computed(() => {
    return aircraft.value[form.value.aircraft_id] || null
})

// Métodos
const fmt = (datetime) => {
    if (!datetime) return ''
    return new Date(datetime).toLocaleDateString('es-CO', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const chip = (status) => {
    switch (status) {
        case 'scheduled':
            return 'bg-blue-100 text-blue-700 border-blue-200'
        case 'completed':
            return 'bg-green-100 text-green-700 border-green-200'
        case 'cancelled':
            return 'bg-rose-100 text-rose-700 border-rose-200'
        default:
            return 'bg-slate-100 text-slate-700 border-slate-200'
    }
}

const formatDuration = (minutes) => {
    if (!minutes) return '0 min'
    const hours = Math.floor(minutes / 60)
    const mins = minutes % 60
    if (hours === 0) return `${mins} min`
    return `${hours}h ${mins}min`
}

const formatDateTime = (dateTime) => {
    if (!dateTime) return ''
    return new Date(dateTime).toLocaleDateString('es-CO', {
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const translateStatus = (status) => {
    switch (status) {
        case 'scheduled':
            return 'Programado'
        case 'completed':
            return 'Completado'
        case 'cancelled':
            return 'Cancelado'
        default:
            return status
    }
}

const reload = async () => {
    loading.value = true
    try {
        const params = new URLSearchParams()
        Object.entries(filters.value).forEach(([key, value]) => {
            if (value) params.append(key, value)
        })
        
        const response = await api.get(`/admin/flights?${params}`)
        list.value = response.data
    } catch (error) {
        console.error('Error loading flights:', error)
    } finally {
        loading.value = false
    }
}

const loadCities = async () => {
    try {
        const response = await api.get('/cities')
        cities.value = response.data
    } catch (error) {
        console.error('Error loading cities:', error)
    }
}

const loadAircraft = async () => {
    try {
        const response = await api.get('/admin/flights/aircraft')
        aircraft.value = response.data
    } catch (error) {
        console.error('Error loading aircraft:', error)
    }
}

const calculateFlightDuration = async () => {
    if (!form.value.origin_id || !form.value.destination_id || !form.value.aircraft_id) {
        calculatedInfo.value = { duration_minutes: 0, distance_km: 0, arrival_time: null }
        return
    }

    try {
        const response = await api.post('/admin/flights/calculate-duration', {
            origin_id: form.value.origin_id,
            destination_id: form.value.destination_id,
            aircraft_id: form.value.aircraft_id
        })

        calculatedInfo.value = {
            duration_minutes: response.data.duration_minutes,
            distance_km: response.data.distance_km,
            arrival_time: form.value.departure_at ? 
                new Date(new Date(form.value.departure_at).getTime() + response.data.duration_minutes * 60000) : 
                null
        }
    } catch (error) {
        console.error('Error calculating duration:', error)
    }
}

const filterAircraftByScope = () => {
    form.value.aircraft_id = ''
    form.value.origin_id = ''
    form.value.destination_id = ''
    calculatedInfo.value = { duration_minutes: 0, distance_km: 0, arrival_time: null }
}

const openCreate = () => {
    form.value = {
        id: null,
        origin_id: '',
        destination_id: '',
        scope: 'national',
        departure_at: '',
        aircraft_id: '',
        price_per_seat: 0
    }
    calculatedInfo.value = { duration_minutes: 0, distance_km: 0, arrival_time: null }
    formErrors.value = []
    editOpen.value = true
}

const openEdit = (flight) => {
    form.value = {
        id: flight.id,
        origin_id: flight.origin_id,
        destination_id: flight.destination_id,
        scope: flight.scope,
        departure_at: flight.departure_at ? new Date(flight.departure_at).toISOString().slice(0, 16) : '',
        aircraft_id: flight.aircraft_id || '',
        price_per_seat: flight.price_per_seat
    }
    formErrors.value = []
    editOpen.value = true
    calculateFlightDuration()
}

const save = async () => {
    formErrors.value = []
    saving.value = true

    try {
        const endpoint = form.value.id ? `/admin/flights/${form.value.id}` : '/admin/flights'
        const method = form.value.id ? 'put' : 'post'
        
        await api[method](endpoint, {
            origin_id: form.value.origin_id,
            destination_id: form.value.destination_id,
            scope: form.value.scope,
            departure_at: form.value.departure_at,
            aircraft_id: form.value.aircraft_id,
            price_per_seat: form.value.price_per_seat
        })

        editOpen.value = false
        reload()
    } catch (error) {
        if (error.response?.data?.errors) {
            formErrors.value = Object.values(error.response.data.errors).flat()
        } else {
            formErrors.value = ['Error al guardar el vuelo']
        }
    } finally {
        saving.value = false
    }
}

const cancelFlight = async (flight) => {
    if (!confirm(`¿Seguro que deseas cancelar el vuelo ${flight.code}?`)) return

    try {
        await api.post(`/admin/flights/${flight.id}/cancel`)
        reload()
    } catch (error) {
        alert('Error al cancelar el vuelo')
    }
}

const go = (url) => {
    if (!url) return
    const urlObj = new URL(url)
    const params = new URLSearchParams(urlObj.search)
    // Aplicar filtros actuales
    Object.entries(filters.value).forEach(([key, value]) => {
        if (value) params.set(key, value)
    })
    
    api.get(`/admin/flights?${params}`).then(response => {
        list.value = response.data
    })
}

// Watchers
watch(() => [form.value.departure_at], () => {
    if (calculatedInfo.value.duration_minutes && form.value.departure_at) {
        calculatedInfo.value.arrival_time = new Date(
            new Date(form.value.departure_at).getTime() + calculatedInfo.value.duration_minutes * 60000
        )
    }
})

// Ciclo de vida
onMounted(() => {
    loadCities()
    loadAircraft()
    reload()
})
</script>