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
                                    :title="!canModifyFlight(f) ? 'No se puede editar un vuelo que ya se realizó o está completado' : ''"
                                >
                                    Editar
                                </button>
                                <button
                                    class="h-9 px-3 rounded-lg border text-sm hover:bg-slate-100 disabled:opacity-50 disabled:cursor-not-allowed"
                                    @click="openPromo(f)"
                                    :disabled="!canCreatePromo(f)"
                                    :title="!canCreatePromo(f) ? 'No se pueden crear promociones para vuelos pasados o completados' : ''"
                                >
                                    Promo
                                </button>
                                <button
                                    class="h-9 px-3 rounded-lg border text-sm hover:bg-slate-100 disabled:opacity-50 disabled:cursor-not-allowed"
                                    @click="openNews(f)"
                                    :disabled="!canCreateNews(f)"
                                    :title="!canCreateNews(f) ? 'No se pueden crear noticias para vuelos pasados' : ''"
                                >
                                    Noticia
                                </button>
                                <button
                                    class="h-9 px-3 rounded-lg border border-rose-300 text-rose-600 text-sm hover:bg-rose-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="!canCancelFlight(f)"
                                    :title="!canCancelFlight(f) ? 'Solo se pueden cancelar vuelos programados y futuros' : ''"
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
                    <label for="flight_scope" class="block text-sm font-medium text-gray-700 mb-1">
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
                
                <!-- Segunda fila: Origen y Destino -->
                                <!-- Segunda fila: Origen y Destino -->
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label for="flight_origin" class="block text-sm font-medium text-gray-700 mb-1">
                            Ciudad de Origen *
                        </label>
                        <select
                            id="flight_origin"
                            v-model="form.origin_id"
                            class="h-10 rounded-lg border px-3 w-full"
                        >
                            <option value="">Selecciona origen</option>
                            <option v-for="c in availableOriginCities" :key="c.id" :value="c.id">
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
                        <label for="flight_destination" class="block text-sm font-medium text-gray-700 mb-1">
                            Ciudad de Destino *
                        </label>
                        <select
                            id="flight_destination"
                            v-model="form.destination_id"
                            class="h-10 rounded-lg border px-3 w-full"
                        >
                            <option value="">Selecciona destino</option>
                            <option v-for="c in availableDestinationCities" :key="c.id" :value="c.id">
                                {{ c.name }}
                            </option>
                        </select>
                        <p class="text-xs text-slate-500 mt-1">
                            <span v-if="form.scope === 'national'">
                                Cualquier ciudad de Colombia (diferente al origen)
                            </span>
                            <span v-else>
                                Solo: Madrid, Londres, New York, Buenos Aires, Miami
                            </span>
                        </p>
                    </div>
                </div>
                
                <!-- Tercera fila: Otros campos -->
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label for="flight_departure" class="block text-sm font-medium text-gray-700 mb-1">
                            Fecha y Hora de Salida *
                        </label>
                        <input
                            id="flight_departure"
                            v-model="form.departure_at"
                            type="datetime-local"
                            class="h-10 rounded-lg border px-3 w-full"
                        />
                    </div>
                    <div>
                        <label for="flight_duration" class="block text-sm font-medium text-gray-700 mb-1">
                            Duración (minutos) *
                        </label>
                        <input
                            id="flight_duration"
                            v-model.number="form.duration_minutes"
                            type="number"
                            min="10"
                            max="1440"
                            pattern="[0-9]+"
                            title="Solo se permiten números"
                            class="h-10 rounded-lg border px-3 w-full"
                            placeholder="ej: 90"
                            @input="validateNumericInput($event)"
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
                            pattern="[0-9]+"
                            title="Solo se permiten números"
                            class="h-10 rounded-lg border px-3 w-full"
                            placeholder="ej: 150000"
                            @input="validateNumericInput($event)"
                        />
                    </div>
                    <div>
                        <label for="flight_capacity_first" class="block text-sm font-medium text-gray-700 mb-1">
                            Capacidad Primera Clase
                        </label>
                        <input
                            id="flight_capacity_first"
                            v-model.number="form.capacity_first"
                            type="number"
                            min="0"
                            max="500"
                            pattern="[0-9]+"
                            title="Solo se permiten números"
                            class="h-10 rounded-lg border px-3 w-full"
                            placeholder="ej: 12"
                            @input="validateNumericInput($event)"
                        />
                    </div>
                    <div>
                        <label for="flight_capacity_economy" class="block text-sm font-medium text-gray-700 mb-1">
                            Capacidad Clase Económica *
                        </label>
                        <input
                            id="flight_capacity_economy"
                            v-model.number="form.capacity_economy"
                            type="number"
                            min="1"
                            max="1000"
                            pattern="[0-9]+"
                            title="Solo se permiten números"
                            class="h-10 rounded-lg border px-3 w-full"
                            placeholder="ej: 180"
                            @input="validateNumericInput($event)"
                        />
                    </div>
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
                    <label for="promo_title" class="block text-sm font-medium text-gray-700 mb-1">
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
                    <label for="promo_discount" class="block text-sm font-medium text-gray-700 mb-1">
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
                    <label for="promo_start" class="block text-sm font-medium text-gray-700 mb-1">
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
                    <label for="promo_end" class="block text-sm font-medium text-gray-700 mb-1">
                        Fecha de Fin *
                    </label>
                    <input
                        id="promo_end"
                        v-model="promo.ends_at"
                        type="datetime-local"
                        :min="promo.starts_at"
                        :max="toLocalInput(new Date(currentFlight?.departure_at))"
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

        <!-- Modal Noticia -->
        <BaseModal v-model:open="newsOpen">
            <template #title
                >Nueva noticia —
                {{ currentFlight?.code || "General" }}</template
            >
            <div class="grid gap-3">
                <div>
                    <label for="news_title" class="block text-sm font-medium text-gray-700 mb-1">
                        Título de la Noticia *
                    </label>
                    <input
                        id="news_title"
                        v-model="news.title"
                        placeholder="ej: Nuevos destinos disponibles"
                        class="h-10 rounded-lg border px-3 w-full"
                    />
                </div>
                <div>
                    <label for="news_body" class="block text-sm font-medium text-gray-700 mb-1">
                        Contenido *
                    </label>
                    <textarea
                        id="news_body"
                        v-model="news.body"
                        rows="4"
                        placeholder="Escribe el contenido de la noticia..."
                        class="rounded-lg border px-3 py-2 w-full"
                    ></textarea>
                </div>
                <div>
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700"
                        ><input id="news_is_promotion" type="checkbox" v-model="news.is_promotion" /> Es
                        promoción</label
                    >
                </div>
                <div>
                    <label for="news_image" class="block text-sm font-medium text-gray-700 mb-1">
                        Imagen (opcional)
                    </label>
                    <input
                        id="news_image"
                        type="file"
                        @change="onNewsFile"
                        accept="image/*"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                    />
                </div>
            </div>
            <p v-if="newsError" class="mt-2 text-rose-600 text-sm">
                {{ newsError }}
            </p>
            <div class="mt-4 flex justify-end gap-2">
                <button
                    class="h-10 px-4 rounded-lg border"
                    @click="newsOpen = false"
                >
                    Cerrar
                </button>
                <button
                    class="h-10 px-4 rounded-lg bg-blue-600 text-white"
                    @click="saveNews"
                >
                    Publicar
                </button>
            </div>
        </BaseModal>
    </section>
</template>

<script setup>
import { reactive, ref, onMounted, computed, watch } from "vue";
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
const currentFlight = ref(null);

// Ciudades filtradas según el alcance del vuelo
const availableOriginCities = computed(() => {
    if (form.scope === 'national') {
        // Para vuelos nacionales: solo ciudades colombianas
        return cities.value.filter(city => city.scope === 'national');
    } else {
        // Para vuelos internacionales: solo ciudades colombianas como origen
        const allowedOrigins = ['Pereira', 'Bogotá', 'Medellín', 'Cali', 'Cartagena'];
        return cities.value.filter(city => 
            city.scope === 'national' && allowedOrigins.includes(city.name)
        );
    }
});

const availableDestinationCities = computed(() => {
    if (form.scope === 'national') {
        // Para vuelos nacionales: solo ciudades colombianas (excluyendo el origen)
        return cities.value.filter(city => 
            city.scope === 'national' && city.id !== form.origin_id
        );
    } else {
        // Para vuelos internacionales: solo destinos internacionales específicos
        const allowedDestinations = ['Madrid', 'Londres', 'New York', 'Buenos Aires', 'Miami'];
        return cities.value.filter(city => 
            city.scope === 'international' && allowedDestinations.includes(city.name)
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
    departure_at: "",
    duration_minutes: 90,
    price_per_seat: 0,
    capacity_first: 0,
    capacity_economy: 100,
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

// Estado para Noticias
const newsOpen = ref(false);
const newsError = ref("");
const news = reactive({ title: "", body: "", is_promotion: false, file: null });

// --- Watchers ---
// Limpiar origen y destino cuando cambie el tipo de vuelo
watch(() => form.scope, (newScope, oldScope) => {
    if (newScope !== oldScope) {
        form.origin_id = "";
        form.destination_id = "";
    }
});

// Limpiar destino cuando cambie el origen (para evitar seleccionar la misma ciudad en vuelos nacionales)
watch(() => form.origin_id, (newOrigin) => {
    if (form.scope === 'national' && form.destination_id === newOrigin) {
        form.destination_id = "";
    }
});

// --- Ciclo de Vida ---
onMounted(async () => {
    const { data } = await api.get("/cities");
    cities.value = data;
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
    return flight.status === 'scheduled' && !isFlightPast(flight);
}

// Verificar si se pueden crear promociones
function canCreatePromo(flight) {
    return flight.status === 'scheduled' && !isFlightPast(flight);
}

// Verificar si se pueden crear noticias
function canCreateNews(flight) {
    return !isFlightPast(flight);
}

// Verificar si se puede cancelar un vuelo
function canCancelFlight(flight) {
    return flight.status === 'scheduled' && !isFlightPast(flight);
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
    departure_at: "",
    duration_minutes: 90,
    price_per_seat: 0,
    capacity_first: 0,
    capacity_economy: 100,
};

function openCreate() {
    Object.assign(form, VUELO_VACIO);
    formErrors.value = [];
    editOpen.value = true;
}

function openEdit(f) {
    Object.assign(form, {
        id: f.id,
        scope: f.scope,
        origin_id: f.origin_id,
        destination_id: f.destination_id,
        departure_at: toLocalInput(f.departure_at),
        duration_minutes: f.duration_minutes,
        price_per_seat: f.price_per_seat,
        capacity_first: f.capacity_first,
        capacity_economy: f.capacity_economy,
    });
    formErrors.value = [];
    editOpen.value = true;
}

async function save() {
    saving.value = true;
    formErrors.value = [];

    try {
        // 💡 **AQUÍ ESTÁ LA CORRECCIÓN**
        if (!form.id) {
            // --- CREAR ---
            // Se construye el payload explícitamente para no incluir `id: null`
            const payload = {
                scope: form.scope,
                origin_id: form.origin_id,
                destination_id: form.destination_id,
                departure_at: new Date(form.departure_at).toISOString(),
                duration_minutes: form.duration_minutes,
                price_per_seat: form.price_per_seat,
                capacity_first: form.capacity_first,
                capacity_economy: form.capacity_economy,
            };
            await api.post("/admin/flights", payload, {
                headers: { Authorization: "Bearer " + auth.token },
            });
        } else {
            // --- ACTUALIZAR ---
            // Se envían solo los campos permitidos para actualización
            const payload = {
                price_per_seat: form.price_per_seat,
                departure_at: new Date(form.departure_at).toISOString(),
                origin_id: form.origin_id,
                destination_id: form.destination_id,
                duration_minutes: form.duration_minutes,
            };
            await api.put(`/admin/flights/${form.id}`, payload, {
                headers: { Authorization: "Bearer " + auth.token },
            });
        }
        editOpen.value = false;
        await reload();
    } catch (e) {
        const errs = e.response?.data?.errors || e.response?.data || e.message;
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

    if (f.status === 'completed' || flightDate < now) {
        alert('No se pueden crear promociones para vuelos que ya se han realizado.');
        return;
    }

    currentFlight.value = f;
    Object.assign(promo, {
        title: `Promo ${f.code}`,
        discount_percent: 10,
        starts_at: toLocalInput(new Date()),  // Fecha actual como inicio por defecto
        ends_at: toLocalInput(flightDate),    // Fecha del vuelo como fin por defecto
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
            errors.push("La fecha de inicio debe ser anterior a la fecha de fin");
        }
        if (endDate > flightDate) {
            errors.push("La promoción debe terminar antes de la fecha del vuelo");
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
        alert('Promoción creada exitosamente');
    } catch (e) {
        promoError.value = e.response?.data?.message || "Error al crear promo";
    }
}

// --- Lógica para Noticias ---
function openNews(f) {
    currentFlight.value = f || null;
    Object.assign(news, {
        title: "",
        body: "",
        is_promotion: false,
        file: null,
    });
    newsError.value = "";
    newsOpen.value = true;
}
function onNewsFile(ev) {
    news.file = ev.target.files?.[0] || null;
}
async function saveNews() {
    try {
        const fd = new FormData();
        fd.append("title", news.title);
        if (news.body) fd.append("body", news.body);
        if (currentFlight.value) fd.append("flight_id", currentFlight.value.id);
        fd.append("is_promotion", news.is_promotion ? "1" : "0");
        if (news.file) fd.append("image", news.file);
        await api.post("/news", fd, {
            headers: { 
                Authorization: "Bearer " + auth.token,
                'Content-Type': 'multipart/form-data'
            },
        });
        newsOpen.value = false;
    } catch (e) {
        // Mostrar errores de validación específicos si existen
        if (e.response?.data?.errors) {
            const errors = e.response.data.errors;
            const firstError = Object.values(errors)[0];
            newsError.value = Array.isArray(firstError) ? firstError[0] : firstError;
        } else {
            newsError.value = e.response?.data?.message || "Error al publicar noticia";
        }
    }
}

// Validation functions
function validateNumericInput(event) {
    // Only allow numbers
    const regex = /[^0-9]/g;
    event.target.value = event.target.value.replace(regex, '');
}
</script>
