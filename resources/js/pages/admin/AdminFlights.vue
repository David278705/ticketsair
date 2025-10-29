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

        <div
            class="rounded-xl border p-4 mb-6 grid gap-3 md:grid-cols-3 lg:grid-cols-5 bg-slate-50"
        >
            <input
                v-model="filters.code"
                placeholder="Código de vuelo"
                class="h-10 rounded-lg border-slate-300 px-3"
            />
            <select
                v-model="filters.status"
                class="h-10 rounded-lg border-slate-300 px-3"
            >
                <option value="">Todos los estados</option>
                <option value="scheduled">Programado</option>
                <option value="completed">Completado</option>
                <option value="cancelled">Cancelado</option>
            </select>
            <select
                v-model="filters.origin_id"
                class="h-10 rounded-lg border-slate-300 px-3"
            >
                <option value="">Cualquier origen</option>
                <option v-for="c in cities" :key="c.id" :value="c.id">
                    {{ c.name }}
                </option>
            </select>
            <select
                v-model="filters.destination_id"
                class="h-10 rounded-lg border-slate-300 px-3"
            >
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

        <div class="rounded-xl border overflow-x-auto">
            <table class="min-w-[1024px] w-full text-sm text-left">
                <thead class="bg-slate-100 text-slate-600">
                    <tr>
                        <th class="p-3 font-semibold">Código</th>
                        <th class="p-3 font-semibold">Ruta</th>
                        <th class="p-3 font-semibold">Salida</th>
                        <th class="p-3 font-semibold">Estado</th>
                        <th class="p-3 font-semibold">Precio Base</th>
                        <th class="p-3 font-semibold">Capacidad (F/E)</th>
                        <th class="p-3 font-semibold">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-if="list.data.length === 0">
                        <td colspan="7" class="text-center p-8 text-slate-500">
                            No se encontraron vuelos con los filtros actuales.
                        </td>
                    </tr>
                    <tr
                        v-for="f in list.data"
                        :key="f.id"
                        class="hover:bg-slate-50 transition-colors"
                    >
                        <td class="p-3 font-mono text-blue-600">
                            {{ f.code }}
                        </td>
                        <td class="p-3">
                            <span class="font-medium">{{ f.origin.name }}</span>
                            →
                            <span class="font-medium">{{
                                f.destination.name
                            }}</span>
                        </td>
                        <td class="p-3">{{ fmt(f.departure_at) }}</td>
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
                        <td class="p-3">
                            {{ f.capacity_first }}/{{ f.capacity_economy }}
                        </td>
                        <td class="p-3">
                            <div class="flex flex-wrap gap-2">
                                <button
                                    class="h-9 px-3 rounded-lg border text-sm hover:bg-slate-100 disabled:opacity-50 disabled:cursor-not-allowed"
                                    @click="openEdit(f)"
                                    :disabled="!canModifyFlight(f)"
                                    :title="
                                        !canModifyFlight(f)
                                            ? 'No se puede editar un vuelo que ya se realizó o está completado'
                                            : ''
                                    "
                                >
                                    Editar
                                </button>
                                <button
                                    class="h-9 px-3 rounded-lg border text-sm hover:bg-slate-100 disabled:opacity-50 disabled:cursor-not-allowed"
                                    @click="openPromo(f)"
                                    :disabled="!canCreatePromo(f)"
                                    :title="
                                        !canCreatePromo(f)
                                            ? 'No se pueden crear promociones para vuelos pasados o completados'
                                            : ''
                                    "
                                >
                                    Promo
                                </button>
                                <button
                                    class="h-9 px-3 rounded-lg border border-rose-300 text-rose-600 text-sm hover:bg-rose-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="!canCancelFlight(f)"
                                    :title="
                                        !canCancelFlight(f)
                                            ? 'Solo se pueden cancelar vuelos programados y futuros'
                                            : ''
                                    "
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

        <div
            v-if="list?.meta?.links?.length > 3"
            class="mt-6 flex justify-center flex-wrap gap-2"
        >
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
            <template #title>{{
                form.id ? "Editar Vuelo" : "✈️ Nuevo Vuelo"
            }}</template>
            <div class="grid gap-4">
                <!-- Primera fila: Tipo de vuelo -->
                <div>
                    <label
                        for="flight_scope"
                        class="block text-sm font-medium text-gray-700 mb-1"
                    >
                        Tipo de Vuelo *
                    </label>
                    <select
                        id="flight_scope"
                        v-model="form.scope"
                        class="h-10 rounded-lg border px-3 w-full"
                    >
                        <option value="national">Nacional</option>
                        <option value="international">Internacional</option>
                    </select>
                    <p class="text-xs text-slate-500 mt-1">
                        <span v-if="form.scope === 'national'">
                            🇨🇴 Vuelos dentro de Colombia
                        </span>
                        <span v-else>
                            ✈️ Vuelos desde Colombia al exterior
                        </span>
                    </p>
                </div>

                <!-- Selector de Avión -->
                <div>
                    <label
                        for="flight_aircraft"
                        class="block text-sm font-medium text-gray-700 mb-1"
                    >
                        Avión *
                    </label>
                    <select
                        id="flight_aircraft"
                        v-model="form.aircraft_id"
                        class="h-10 rounded-lg border px-3 w-full"
                    >
                        <option value="">Selecciona un avión</option>
                        <option v-for="a in aircraft" :key="a.id" :value="a.id">
                            {{ a.name }} - {{ a.brand }} ({{
                                a.capacity_first
                            }}F + {{ a.capacity_economy }}E =
                            {{ a.capacity_first + a.capacity_economy }}
                            asientos)
                        </option>
                    </select>
                    <p class="text-xs text-slate-500 mt-1">
                        La duración y capacidades se calcularán automáticamente
                    </p>
                </div>

                <!-- Segunda fila: Origen y Destino -->
                <!-- Segunda fila: Origen y Destino -->
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label
                            for="flight_origin"
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Ciudad de Origen *
                        </label>
                        <select
                            id="flight_origin"
                            v-model="form.origin_id"
                            class="h-10 rounded-lg border px-3 w-full"
                        >
                            <option value="">Selecciona origen</option>
                            <option
                                v-for="c in availableOriginCities"
                                :key="c.id"
                                :value="c.id"
                            >
                                {{ c.name }}
                            </option>
                        </select>
                        <p class="text-xs text-slate-500 mt-1">
                            <span v-if="form.scope === 'national'">
                                Cualquier ciudad de Colombia
                            </span>
                            <span v-else>
                                Solo: Pereira, Bogotá, Medellín, Cali, Cartagena
                            </span>
                        </p>
                    </div>
                    <div>
                        <label
                            for="flight_destination"
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Ciudad de Destino *
                        </label>
                        <select
                            id="flight_destination"
                            v-model="form.destination_id"
                            class="h-10 rounded-lg border px-3 w-full"
                        >
                            <option value="">Selecciona destino</option>
                            <option
                                v-for="c in availableDestinationCities"
                                :key="c.id"
                                :value="c.id"
                            >
                                {{ c.name }}
                            </option>
                        </select>
                        <p class="text-xs text-slate-500 mt-1">
                            <span v-if="form.scope === 'national'">
                                Cualquier ciudad de Colombia (diferente al
                                origen)
                            </span>
                            <span v-else>
                                Solo: Madrid, Londres, New York, Buenos Aires,
                                Miami
                            </span>
                        </p>
                    </div>
                </div>

                <!-- Tercera fila: Otros campos -->
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label
                            for="flight_departure"
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Fecha y Hora de Salida *
                        </label>
                        <input
                            id="flight_departure"
                            v-model="form.departure_at"
                            type="datetime-local"
                            required
                            class="h-10 rounded-lg border px-3 w-full"
                        />
                    </div>
                    <div v-if="form.aircraft_id">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Duración Calculada
                        </label>
                        <div
                            class="h-10 rounded-lg border border-blue-200 bg-blue-50 px-3 flex items-center text-blue-900 font-semibold"
                        >
                            {{ form.duration_minutes }} minutos
                            <span class="ml-2 text-xs text-blue-600">
                                (≈ {{ Math.floor(form.duration_minutes / 60) }}h
                                {{ form.duration_minutes % 60 }}min)
                            </span>
                        </div>
                        <p class="text-xs text-blue-600 mt-1">
                            ✓ Calculado automáticamente según distancia y
                            velocidad del avión
                        </p>
                    </div>
                    <div>
                        <label
                            for="flight_price"
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            💺 Precio Clase Económica *
                        </label>
                        <input
                            id="flight_price"
                            v-model.number="form.price_per_seat"
                            type="number"
                            min="0"
                            max="9999999"
                            pattern="[0-9]+"
                            title="Solo se permiten números"
                            class="h-10 rounded-lg border px-3 w-full"
                            placeholder="ej: 150000"
                            @input="validateNumericInput($event)"
                        />
                        <p class="text-xs text-gray-500 mt-1">
                            Precio base por asiento en clase económica
                        </p>
                    </div>
                    <div>
                        <label
                            for="flight_price_first"
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            ⭐ Precio Primera Clase *
                        </label>
                        <input
                            id="flight_price_first"
                            v-model.number="form.first_class_price"
                            type="number"
                            min="0"
                            max="9999999"
                            pattern="[0-9]+"
                            title="Solo se permiten números"
                            class="h-10 rounded-lg border px-3 w-full"
                            placeholder="ej: 300000"
                            @input="validateNumericInput($event)"
                        />
                        <p class="text-xs text-gray-500 mt-1">
                            Precio por asiento en primera clase (generalmente 2x
                            económica)
                        </p>
                    </div>
                    <div v-if="form.aircraft_id">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Capacidad Primera Clase
                        </label>
                        <div
                            class="h-10 rounded-lg border border-blue-200 bg-blue-50 px-3 flex items-center text-blue-900 font-semibold"
                        >
                            {{ form.capacity_first }} asientos
                        </div>
                        <p class="text-xs text-blue-600 mt-1">
                            ✓ Del avión seleccionado
                        </p>
                    </div>
                    <div v-if="form.aircraft_id">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Capacidad Clase Económica
                        </label>
                        <div
                            class="h-10 rounded-lg border border-blue-200 bg-blue-50 px-3 flex items-center text-blue-900 font-semibold"
                        >
                            {{ form.capacity_economy }} asientos
                        </div>
                        <p class="text-xs text-blue-600 mt-1">
                            ✓ Del avión seleccionado
                        </p>
                    </div>
                </div>

                <!-- Campo de imagen -->
                <div>
                    <label
                        for="flight_image"
                        class="block text-sm font-medium text-gray-700 mb-1"
                    >
                        Imagen del Vuelo
                    </label>
                    <input
                        id="flight_image"
                        type="file"
                        @change="onFlightImageChange"
                        accept="image/*"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                    />
                    <p class="text-xs text-slate-500 mt-1">
                        Imagen representativa del vuelo (se usará para crear la
                        noticia automáticamente)
                    </p>
                </div>
            </div>
            <ul
                v-if="formErrors.length"
                class="mt-4 text-rose-500 text-sm list-disc list-inside bg-rose-50 p-3 rounded-lg"
            >
                <li v-for="(e, i) in formErrors" :key="i">{{ e }}</li>
            </ul>
            <div class="mt-6 flex justify-end gap-3">
                <button
                    class="h-10 px-4 rounded-lg border"
                    @click="editOpen = false"
                >
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

        <!-- Modal Promoción -->
        <BaseModal v-model:open="promoOpen">
            <template #title
                >Crear promoción — {{ currentFlight?.code }}</template
            >
            <div class="grid md:grid-cols-2 gap-3">
                <div>
                    <label
                        for="promo_title"
                        class="block text-sm font-medium text-gray-700 mb-1"
                    >
                        Título de la Promoción *
                    </label>
                    <input
                        id="promo_title"
                        v-model="promo.title"
                        placeholder="ej: Descuento de Temporada"
                        class="h-10 rounded-lg border px-3 w-full"
                    />
                </div>
                <div>
                    <label
                        for="promo_discount"
                        class="block text-sm font-medium text-gray-700 mb-1"
                    >
                        Porcentaje de Descuento *
                    </label>
                    <input
                        id="promo_discount"
                        v-model.number="promo.discount_percent"
                        type="number"
                        min="1"
                        max="90"
                        pattern="[0-9]+"
                        title="Solo se permiten números"
                        class="h-10 rounded-lg border px-3 w-full"
                        placeholder="ej: 25"
                        @input="validateNumericInput($event)"
                    />
                </div>
                <div>
                    <label
                        for="promo_start"
                        class="block text-sm font-medium text-gray-700 mb-1"
                    >
                        Fecha de Inicio *
                    </label>
                    <input
                        id="promo_start"
                        v-model="promo.starts_at"
                        type="datetime-local"
                        :min="toLocalInput(new Date())"
                        :max="promo.ends_at"
                        class="h-10 rounded-lg border px-3 w-full"
                        required
                    />
                </div>
                <div>
                    <label
                        for="promo_end"
                        class="block text-sm font-medium text-gray-700 mb-1"
                    >
                        Fecha de Fin *
                    </label>
                    <input
                        id="promo_end"
                        v-model="promo.ends_at"
                        type="datetime-local"
                        :min="promo.starts_at"
                        :max="
                            toLocalInput(new Date(currentFlight?.departure_at))
                        "
                        class="h-10 rounded-lg border px-3 w-full"
                        required
                    />
                </div>
                <label class="inline-flex items-center gap-2 mt-1"
                    ><input type="checkbox" v-model="promo.is_active" />
                    Activa</label
                >
            </div>
            <p v-if="promoError" class="mt-2 text-rose-600 text-sm">
                {{ promoError }}
            </p>
            <div class="mt-4 flex justify-end gap-2">
                <button
                    class="h-10 px-4 rounded-lg border"
                    @click="promoOpen = false"
                >
                    Cerrar
                </button>
                <button
                    class="h-10 px-4 rounded-lg bg-blue-600 text-white"
                    @click="savePromo"
                >
                    Guardar
                </button>
            </div>
        </BaseModal>
    </section>
</template>

<script setup>
import { reactive, ref, onMounted, computed, watch, nextTick } from "vue";
import { api } from "../../lib/api";
import { useAuth } from "../../stores/auth";
import BaseModal from "../../components/ui/BaseModal.vue";

// --- Componente Modal (Reutilizable) ---
const Modal = {
    props: ["open"],
    emits: ["update:open"],
    template: `
    <transition enter-active-class="duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100"
                leave-active-class="duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="open" class="fixed inset-0 z-[70] grid place-items-center bg-slate-900/40 backdrop-blur-sm p-4" @click.self="$emit('update:open', false)">
            <div class="w-full max-w-3xl rounded-2xl bg-white  p-6 shadow-xl">
                <h3 class="text-xl font-semibold mb-4 text-slate-800 "><slot name="title"/></h3>
                <slot/>
            </div>
        </div>
    </transition>`,
};

// --- Estado del Componente ---
const auth = useAuth();
const list = ref({ data: [], meta: null });
const cities = ref([]);
const aircraft = ref([]);
const currentFlight = ref(null);

// Ciudades filtradas según el alcance del vuelo
const availableOriginCities = computed(() => {
    if (form.scope === "national") {
        // Para vuelos nacionales: solo ciudades colombianas
        return cities.value.filter((city) => city.scope === "national");
    } else {
        // Para vuelos internacionales: solo ciudades colombianas como origen
        const allowedOrigins = [
            "Pereira",
            "Bogotá",
            "Medellín",
            "Cali",
            "Cartagena",
        ];
        return cities.value.filter(
            (city) =>
                city.scope === "national" && allowedOrigins.includes(city.name)
        );
    }
});

const availableDestinationCities = computed(() => {
    if (form.scope === "national") {
        // Para vuelos nacionales: solo ciudades colombianas (excluyendo el origen)
        return cities.value.filter(
            (city) => city.scope === "national" && city.id !== form.origin_id
        );
    } else {
        // Para vuelos internacionales: solo destinos internacionales específicos
        const allowedDestinations = [
            "Madrid",
            "Londres",
            "New York",
            "Buenos Aires",
            "Miami",
        ];
        return cities.value.filter(
            (city) =>
                city.scope === "international" &&
                allowedDestinations.includes(city.name)
        );
    }
});

const filters = reactive({
    code: "",
    status: "",
    origin_id: "",
    destination_id: "",
});

// Estado para Crear/Editar Vuelo
const editOpen = ref(false);
const saving = ref(false);
const formErrors = ref([]);
const form = reactive({
    id: null,
    scope: "national",
    origin_id: "",
    destination_id: "",
    aircraft_id: "",
    departure_at: "",
    duration_minutes: 90,
    price_per_seat: 0,
    first_class_price: 0,
    capacity_first: 0,
    capacity_economy: 100,
    image: null,
});

// Estado para Promociones
const promoOpen = ref(false);
const promoError = ref("");
const promo = reactive({
    title: "",
    discount_percent: 10,
    starts_at: "",
    ends_at: "",
    is_active: true,
});

// --- Watchers ---
// Variable para controlar si estamos editando
const isEditingFlight = ref(false);

// Limpiar origen y destino cuando cambie el tipo de vuelo (SOLO si no estamos editando)
watch(
    () => form.scope,
    (newScope, oldScope) => {
        // No limpiar si estamos en modo edición O si no hay cambio real
        if (isEditingFlight.value || newScope === oldScope || !oldScope) return;

        form.origin_id = "";
        form.destination_id = "";
    }
);

// Limpiar destino cuando cambie el origen (SOLO si no estamos editando y para vuelos nacionales)
watch(
    () => form.origin_id,
    (newOrigin, oldOrigin) => {
        // No limpiar si estamos en modo edición O si es la primera asignación
        if (isEditingFlight.value || !oldOrigin) return;

        if (form.scope === "national" && form.destination_id === newOrigin) {
            form.destination_id = "";
        }
    }
);

// Calcular duración automáticamente cuando se selecciona avión, origen y destino
watch(
    [() => form.origin_id, () => form.destination_id, () => form.aircraft_id],
    () => {
        calculateFlightDuration();
    }
);

// Actualizar capacidades cuando se selecciona un avión
watch(
    () => form.aircraft_id,
    (newAircraftId) => {
        if (newAircraftId) {
            const selectedAircraft = aircraft.value.find(
                (a) => a.id === newAircraftId
            );
            if (selectedAircraft) {
                form.capacity_first = selectedAircraft.capacity_first;
                form.capacity_economy = selectedAircraft.capacity_economy;
            }
        }
    }
);

// Función para calcular la duración del vuelo
function calculateFlightDuration() {
    if (!form.origin_id || !form.destination_id || !form.aircraft_id) return;

    const originCity = cities.value.find((c) => c.id === form.origin_id);
    const selectedAircraft = aircraft.value.find(
        (a) => a.id === form.aircraft_id
    );

    if (originCity && originCity.distances && selectedAircraft) {
        const distanceKm = originCity.distances[form.destination_id];
        if (distanceKm && selectedAircraft.speed_kmh) {
            // Calcular tiempo en horas y convertir a minutos
            const hours = distanceKm / selectedAircraft.speed_kmh;
            const minutes = Math.round(hours * 60);
            // Agregar 15 minutos de buffer (taxi, despegue, aterrizaje)
            form.duration_minutes = minutes + 15;
        }
    }
}

// --- Ciclo de Vida ---
onMounted(async () => {
    const citiesResponse = await api.get("/cities");
    cities.value = citiesResponse.data;

    const aircraftResponse = await api.get("/aircraft");
    aircraft.value = aircraftResponse.data;

    await reload();
});

// --- Carga y Paginación de Datos ---
async function reload(url) {
    try {
        const { data } = await api.get(url || "/admin/flights", {
            params: filters,
            headers: { Authorization: "Bearer " + auth.token },
        });
        list.value = data;
    } catch (error) {
        console.error("Error al recargar los vuelos:", error);
        // Opcional: mostrar un error al usuario
    }
}

function go(url) {
    if (!url) return;
    const u = new URL(url);
    reload(u.pathname + u.search);
}

// --- Funciones de Ayuda (Helpers) ---
function fmt(d) {
    return new Date(d).toLocaleString("es-CO", {
        dateStyle: "medium",
        timeStyle: "short",
    });
}

function chip(status) {
    const styles = {
        scheduled: "border-amber-300 text-amber-700 bg-amber-50 ",
        completed: "border-emerald-300 text-emerald-700 bg-emerald-50 ",
        cancelled: "border-rose-300 text-rose-700 bg-rose-50 ",
    };
    return styles[status] || "border-slate-300 text-slate-600";
}

// Traducir estados al español
function translateStatus(status) {
    const translations = {
        scheduled: "Programado",
        completed: "Completado",
        cancelled: "Cancelado",
    };
    return translations[status] || status;
}

// Verificar si un vuelo ya pasó
function isFlightPast(flight) {
    const flightDate = new Date(flight.departure_at);
    const now = new Date();
    return flightDate < now;
}

// Verificar si se puede modificar un vuelo (editar)
function canModifyFlight(flight) {
    return flight.status === "scheduled" && !isFlightPast(flight);
}

// Verificar si se pueden crear promociones
function canCreatePromo(flight) {
    return flight.status === "scheduled" && !isFlightPast(flight);
}

// Verificar si se puede cancelar un vuelo
function canCancelFlight(flight) {
    return flight.status === "scheduled" && !isFlightPast(flight);
}

function toLocalInput(dt) {
    const d = new Date(dt);
    d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
    return d.toISOString().slice(0, 16);
}

// --- Lógica CRUD para Vuelos ---
const VUELO_VACIO = {
    id: null,
    scope: "national",
    origin_id: "",
    destination_id: "",
    aircraft_id: "",
    departure_at: "",
    duration_minutes: 90,
    price_per_seat: 0,
    first_class_price: 0,
    capacity_first: 0,
    capacity_economy: 100,
    image: null,
};

function openCreate() {
    isEditingFlight.value = false;
    Object.assign(form, VUELO_VACIO);
    formErrors.value = [];
    editOpen.value = true;
}

async function openEdit(f) {
    // Activar flag ANTES de cualquier cambio
    isEditingFlight.value = true;

    formErrors.value = [];
    editOpen.value = true;

    // Esperar a que el modal esté montado
    await nextTick();

    // Ahora asignar los valores
    Object.assign(form, {
        id: f.id,
        scope: f.scope,
        aircraft_id: f.aircraft_id,
        origin_id: f.origin_id,
        destination_id: f.destination_id,
        departure_at: toLocalInput(f.departure_at),
        duration_minutes: f.duration_minutes,
        price_per_seat: f.price_per_seat,
        first_class_price: f.first_class_price || f.price_per_seat * 2,
        capacity_first: f.capacity_first,
        capacity_economy: f.capacity_economy,
        image: null,
    });

    // Esperar otro ciclo para que Vue procese todos los cambios
    await nextTick();

    // Mantener el flag activo un poco más para asegurar que los watchers no interfieran
    setTimeout(() => {
        isEditingFlight.value = false;
    }, 100);
}

function onFlightImageChange(event) {
    form.image = event.target.files?.[0] || null;
}

async function save() {
    saving.value = true;
    formErrors.value = [];

    try {
        // Validaciones del frontend
        const errors = [];
        
        if (!form.origin_id) {
            errors.push('La ciudad de origen es requerida.');
        }
        
        if (!form.destination_id) {
            errors.push('La ciudad de destino es requerida.');
        }
        
        if (form.origin_id && form.destination_id && form.origin_id === form.destination_id) {
            errors.push('La ciudad de destino debe ser diferente al origen.');
        }
        
        if (!form.departure_at) {
            errors.push('La fecha y hora de salida es requerida.');
        } else {
            // Validar que sea una fecha válida
            const departureDate = new Date(form.departure_at);
            if (isNaN(departureDate.getTime())) {
                errors.push('La fecha de salida no es válida.');
            } else if (departureDate <= new Date()) {
                errors.push('La fecha de salida debe ser posterior a la fecha actual.');
            }
        }
        
        if (!form.price_per_seat || form.price_per_seat <= 0) {
            errors.push('El precio por asiento es requerido y debe ser mayor a 0.');
        }
        
        if (!form.first_class_price || form.first_class_price <= 0) {
            errors.push('El precio de primera clase es requerido y debe ser mayor a 0.');
        }
        
        if (!form.duration_minutes || form.duration_minutes < 10) {
            errors.push('La duración del vuelo debe ser de al menos 10 minutos.');
        }
        
        if (!form.capacity_economy || form.capacity_economy < 1) {
            errors.push('La capacidad de clase económica debe ser al menos 1.');
        }
        
        if (!form.id && !form.image) {
            errors.push('La imagen del vuelo es requerida.');
        }
        
        // Si hay errores, mostrarlos y detener el envío
        if (errors.length > 0) {
            formErrors.value = errors;
            saving.value = false;
            return;
        }
        
        if (!form.id) {
            // --- CREAR ---
            const fd = new FormData();
            fd.append("scope", form.scope);
            fd.append("origin_id", form.origin_id);
            fd.append("destination_id", form.destination_id);
            if (form.aircraft_id) {
                fd.append("aircraft_id", form.aircraft_id);
            }
            fd.append(
                "departure_at",
                new Date(form.departure_at).toISOString()
            );
            fd.append("duration_minutes", form.duration_minutes);
            fd.append("price_per_seat", form.price_per_seat);
            fd.append(
                "first_class_price",
                form.first_class_price || form.price_per_seat * 2
            );
            fd.append("capacity_first", form.capacity_first);
            fd.append("capacity_economy", form.capacity_economy);
            if (form.image) {
                fd.append("image", form.image);
            }

            await api.post("/admin/flights", fd, {
                headers: {
                    Authorization: "Bearer " + auth.token,
                    "Content-Type": "multipart/form-data",
                },
            });
        } else {
            // --- ACTUALIZAR ---
            const fd = new FormData();
            fd.append("price_per_seat", form.price_per_seat);
            fd.append(
                "first_class_price",
                form.first_class_price || form.price_per_seat * 2
            );
            fd.append(
                "departure_at",
                new Date(form.departure_at).toISOString()
            );
            fd.append("origin_id", form.origin_id);
            fd.append("destination_id", form.destination_id);
            fd.append("duration_minutes", form.duration_minutes);
            if (form.aircraft_id) {
                fd.append("aircraft_id", form.aircraft_id);
            }
            if (form.image) {
                fd.append("image", form.image);
            }
            fd.append("_method", "PUT");

            await api.post(`/admin/flights/${form.id}`, fd, {
                headers: {
                    Authorization: "Bearer " + auth.token,
                    "Content-Type": "multipart/form-data",
                },
            });
        }
        editOpen.value = false;
        await reload();
    } catch (e) {
        const errs = e.response?.data?.errors || e.response?.data || e.message;

        // Verificar si es un error de validación específico
        if (e.response?.status === 422) {
            const errorMessage = e.response?.data?.message;

            if (errorMessage && errorMessage.includes("pasado")) {
                alert(
                    "No se puede editar un vuelo cuya fecha de salida ya pasó."
                );
            } else if (errorMessage) {
                alert(errorMessage);
            }
        }

        formErrors.value = Array.isArray(errs)
            ? errs
            : Object.values(errs || {}).flat();
    } finally {
        saving.value = false;
    }
}

async function cancelFlight(f) {
    if (
        !confirm(
            `¿Seguro que quieres cancelar el vuelo ${f.code}? Esta acción no se puede deshacer.`
        )
    )
        return;

    try {
        await api.post(
            `/admin/flights/${f.id}/cancel`,
            {},
            { headers: { Authorization: "Bearer " + auth.token } }
        );
        await reload();
    } catch (error) {
        alert("Error al cancelar el vuelo.");
        console.error(error);
    }
}

// --- Lógica para Promociones ---
function openPromo(f) {
    // Validar si el vuelo ya se realizó
    const flightDate = new Date(f.departure_at);
    const now = new Date();

    if (f.status === "completed" || flightDate < now) {
        alert(
            "No se pueden crear promociones para vuelos que ya se han realizado."
        );
        return;
    }

    currentFlight.value = f;
    Object.assign(promo, {
        title: `Promo ${f.code}`,
        discount_percent: 10,
        starts_at: toLocalInput(new Date()), // Fecha actual como inicio por defecto
        ends_at: toLocalInput(flightDate), // Fecha del vuelo como fin por defecto
        is_active: true,
    });
    promoError.value = "";
    promoOpen.value = true;
}

async function savePromo() {
    try {
        // Validar fechas
        const startDate = new Date(promo.starts_at);
        const endDate = new Date(promo.ends_at);
        const flightDate = new Date(currentFlight.value.departure_at);
        const now = new Date();

        // Validaciones
        const errors = [];
        if (startDate >= endDate) {
            errors.push(
                "La fecha de inicio debe ser anterior a la fecha de fin"
            );
        }
        if (endDate > flightDate) {
            errors.push(
                "La promoción debe terminar antes de la fecha del vuelo"
            );
        }
        if (startDate < now) {
            errors.push("La fecha de inicio no puede ser en el pasado");
        }

        if (errors.length > 0) {
            promoError.value = errors.join(". ");
            return;
        }

        const payload = {
            title: promo.title,
            discount_percent: Number(promo.discount_percent),
            starts_at: startDate.toISOString(),
            ends_at: endDate.toISOString(),
            is_active: !!promo.is_active,
            description: "", // opcional
        };

        await api.post(
            `/flights/${currentFlight.value.id}/promotions`,
            payload,
            {
                headers: { Authorization: "Bearer " + auth.token },
            }
        );
        promoOpen.value = false;
        alert("Promoción creada exitosamente");
    } catch (e) {
        promoError.value = e.response?.data?.message || "Error al crear promo";
    }
}

// Validation functions
function validateNumericInput(event) {
    // Only allow numbers
    const regex = /[^0-9]/g;
    event.target.value = event.target.value.replace(regex, "");
}
</script>
