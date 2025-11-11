<template>
    <section class="container mx-auto py-8 px-4">
        <header class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <h1 class="text-3xl font-bold text-slate-800">Gestión de Vuelos</h1>
            <div class="flex gap-2">
                <button
                    class="h-10 px-4 rounded-lg border bg-emerald-600 text-white hover:bg-emerald-700 transition-colors flex items-center gap-2"
                    @click="updateFlightStatuses()"
                    title="Actualizar estados de vuelos completados"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    Registrar vuelos
                </button>
                <button
                    class="h-10 px-4 rounded-lg border bg-white hover:bg-slate-50 transition-colors flex items-center gap-2"
                    @click="openCreate()"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"
                        />
                    </svg>
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
                            <div class="flex items-center gap-2">
                                <span>{{ f.code }}</span>
                                <span
                                    v-if="f.any_valid_promotion"
                                    class="px-2 py-0.5 rounded-full bg-gradient-to-r from-rose-500 to-pink-500 text-white text-xs font-bold"
                                    :title="`Promoción: ${f.any_valid_promotion.discount_percent}% de descuento`"
                                >
                                    -{{
                                        f.any_valid_promotion.discount_percent
                                    }}%
                                </span>
                            </div>
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
                            COP
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
                                    :class="
                                        f.any_valid_promotion
                                            ? 'border-green-500 text-green-700 bg-green-50'
                                            : ''
                                    "
                                    @click="openPromo(f)"
                                    :disabled="!canCreatePromo(f)"
                                    :title="
                                        !canCreatePromo(f)
                                            ? 'No se pueden crear promociones para vuelos pasados o completados'
                                            : f.any_valid_promotion
                                            ? 'Editar promoción existente'
                                            : 'Crear nueva promoción'
                                    "
                                >
                                    {{
                                        f.any_valid_promotion
                                            ? "Editar Promo"
                                            : "Crear Promo"
                                    }}
                                </button>
                                <button
                                    v-if="f.has_sales"
                                    class="h-9 px-3 rounded-lg border border-rose-300 text-rose-600 text-sm hover:bg-rose-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="!canCancelFlight(f)"
                                    :title="
                                        !canCancelFlight(f)
                                            ? 'Solo se pueden cancelar vuelos programados y futuros'
                                            : 'Este vuelo tiene ventas, se cancelará y reembolsará a los clientes'
                                    "
                                    @click="cancelFlight(f)"
                                >
                                    Cancelar
                                </button>
                                <button
                                    v-else
                                    class="h-9 px-3 rounded-lg border border-red-500 bg-red-500 text-white text-sm hover:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="!canDeleteFlight(f)"
                                    :title="'Este vuelo no tiene ventas, se eliminará permanentemente'"
                                    @click="deleteFlight(f)"
                                >
                                    Eliminar
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
                form.id ? "Editar Vuelo" : "Nuevo Vuelo"
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
                            Vuelos dentro de Colombia
                        </span>
                        <span v-else> Vuelos desde Colombia al exterior </span>
                    </p>
                </div>

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
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Duración Calculada
                        </label>
                        <div
                            class="h-10 rounded-lg border border-blue-200 bg-blue-50 px-3 flex items-center text-blue-900 font-semibold"
                        >
                            <template v-if="form.duration_minutes">
                                {{ form.duration_minutes }} minutos
                                <span class="ml-2 text-xs text-blue-600">
                                    ({{ formatDuration(form.duration_minutes) }})
                                </span>
                            </template>
                            <span v-else class="text-slate-500 text-sm">
                                Selecciona origen y destino
                            </span>
                        </div>
                        <p class="text-xs text-blue-600 mt-1">
                            Calculado automáticamente según distancia y
                            velocidad (Nacional: 850 km/h, Internacional: 900 km/h)
                        </p>
                    </div>
                    <div>
                        <label
                            for="flight_price"
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Precio Clase Económica (COP) *
                        </label>
                        <div class="relative">
                            <span
                                class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium"
                            >
                                $
                            </span>
                            <input
                                id="flight_price"
                                :value="formatPriceDisplay(form.price_per_seat)"
                                @input="updatePrice($event, 'price_per_seat')"
                                type="text"
                                class="h-10 rounded-lg border px-3 pl-7 w-full"
                                placeholder="150.000"
                            />
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            Precio base por asiento en clase económica
                        </p>
                    </div>
                    <div>
                        <label
                            for="flight_price_first"
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Precio Primera Clase (COP) *
                        </label>
                        <div class="relative">
                            <span
                                class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium"
                            >
                                $
                            </span>
                            <input
                                id="flight_price_first"
                                :value="
                                    formatPriceDisplay(form.first_class_price)
                                "
                                @input="
                                    updatePrice($event, 'first_class_price')
                                "
                                type="text"
                                class="h-10 rounded-lg border px-3 pl-7 w-full"
                                placeholder="300.000"
                            />
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            Precio por asiento en primera clase (generalmente 2x
                            económica)
                        </p>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Capacidad Primera Clase
                        </label>
                        <div
                            class="h-10 rounded-lg border border-blue-200 bg-blue-50 px-3 flex items-center text-blue-900 font-semibold"
                        >
                            {{ getDefaultCapacities().first }} asientos
                        </div>
                        <p class="text-xs text-blue-600 mt-1">
                            Asientos {{ form.scope === 'national' ? '1-25' : '1-50' }}
                        </p>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Capacidad Clase Económica
                        </label>
                        <div
                            class="h-10 rounded-lg border border-blue-200 bg-blue-50 px-3 flex items-center text-blue-900 font-semibold"
                        >
                            {{ getDefaultCapacities().economy }} asientos
                        </div>
                        <p class="text-xs text-blue-600 mt-1">
                            Asientos {{ form.scope === 'national' ? '26-150' : '51-250' }}
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
            <template #title>
                <div class="flex items-center gap-3">
                    <span
                        v-if="promo.id"
                        class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-sm font-medium"
                    >
                        Editando promoción
                    </span>
                    <span
                        v-else
                        class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-sm font-medium"
                    >
                        Nueva promoción
                    </span>
                    <span class="text-slate-500">—</span>
                    <span class="font-mono text-blue-600">{{
                        currentFlight?.code
                    }}</span>
                </div>
            </template>

            <!-- Información del vuelo -->
            <div
                class="mb-4 p-3 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200"
            >
                <div class="flex items-center gap-2 text-sm">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5 text-blue-600"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"
                        />
                    </svg>
                    <span class="font-semibold text-slate-700"
                        >{{ currentFlight?.origin?.name }} →
                        {{ currentFlight?.destination?.name }}</span
                    >
                    <span class="text-slate-500">•</span>
                    <span class="text-slate-600">{{
                        fmt(currentFlight?.departure_at)
                    }}</span>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label
                        for="promo_title"
                        class="block text-sm font-medium text-gray-700 mb-1"
                    >
                        Título de la Promoción *
                    </label>
                    <input
                        id="promo_title"
                        v-model="promo.title"
                        placeholder="ej: ¡Descuento especial de temporada!"
                        class="h-10 rounded-lg border px-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>
                <div>
                    <label
                        for="promo_discount"
                        class="block text-sm font-medium text-gray-700 mb-1"
                    >
                        Porcentaje de Descuento *
                    </label>
                    <div class="relative">
                        <input
                            id="promo_discount"
                            v-model.number="promo.discount_percent"
                            type="number"
                            min="1"
                            max="90"
                            pattern="[0-9]+"
                            title="Solo se permiten números"
                            class="h-10 rounded-lg border px-3 pr-8 w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="25"
                            @input="validateNumericInput($event)"
                        />
                        <span
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium"
                            >%</span
                        >
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Entre 1% y 90% de descuento
                    </p>
                </div>
                <div>
                    <label
                        class="flex items-center gap-2 h-10 px-4 rounded-lg border cursor-pointer hover:bg-slate-50 transition-colors"
                        :class="{
                            'bg-green-50 border-green-500 text-green-700':
                                promo.is_active,
                            'bg-slate-50 border-slate-300 text-slate-500':
                                !promo.is_active,
                        }"
                    >
                        <input
                            type="checkbox"
                            v-model="promo.is_active"
                            class="w-4 h-4"
                        />
                        <span class="font-medium">
                            {{
                                promo.is_active
                                    ? "✓ Promoción activa"
                                    : "✗ Promoción inactiva"
                            }}
                        </span>
                    </label>
                    <p class="text-xs text-slate-500 mt-1">
                        {{
                            promo.is_active
                                ? "La promoción estará visible para los clientes"
                                : "La promoción estará oculta y no aplicará descuentos"
                        }}
                    </p>
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
                        :min="promo.id ? null : toLocalInput(new Date())"
                        :max="promo.ends_at"
                        class="h-10 rounded-lg border px-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required
                    />
                    <p
                        v-if="promo.id && isPromoPast(promo.starts_at)"
                        class="text-xs text-amber-600 mt-1"
                    >
                        ⚠️ Fecha de inicio en el pasado (no se puede cambiar)
                    </p>
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
                        class="h-10 rounded-lg border px-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required
                    />
                    <p class="text-xs text-slate-500 mt-1">
                        Debe ser antes del vuelo
                    </p>
                </div>
            </div>

            <!-- Estado de la promoción (si está editando) -->
            <div
                v-if="promo.id"
                class="mt-4 p-3 rounded-lg"
                :class="{
                    'bg-green-50 border border-green-200': isPromoActive(promo),
                    'bg-amber-50 border border-amber-200': isPromoFuture(promo),
                    'bg-slate-50 border border-slate-200':
                        isPromoExpired(promo) || !promo.is_active,
                }"
            >
                <p class="text-sm font-medium">
                    <span v-if="!promo.is_active" class="text-slate-700">
                        🔒 Estado: Promoción inactiva (no visible para clientes)
                    </span>
                    <span
                        v-else-if="isPromoActive(promo)"
                        class="text-green-700"
                    >
                        ✅ Estado: Promoción activa y visible
                    </span>
                    <span
                        v-else-if="isPromoFuture(promo)"
                        class="text-amber-700"
                    >
                        ⏳ Estado: Programada para
                        {{ formatDate(promo.starts_at) }}
                    </span>
                    <span v-else class="text-slate-700">
                        ⏹️ Estado: Promoción expirada
                    </span>
                </p>
            </div>

            <p
                v-if="promoError"
                class="mt-4 p-3 text-rose-700 text-sm bg-rose-50 border border-rose-200 rounded-lg"
            >
                ⚠️ {{ promoError }}
            </p>

            <div class="mt-6 flex justify-between gap-3">
                <button
                    v-if="promo.id"
                    class="h-10 px-4 rounded-lg border border-red-500 text-red-600 hover:bg-red-50 transition-colors font-medium"
                    @click="deletePromo"
                >
                    🗑️ Eliminar promoción
                </button>
                <div class="flex gap-3 ml-auto">
                    <button
                        class="h-10 px-4 rounded-lg border hover:bg-slate-50 transition-colors"
                        @click="promoOpen = false"
                    >
                        Cancelar
                    </button>
                    <button
                        class="h-10 px-6 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50"
                        @click="savePromo"
                        :disabled="savingPromo"
                    >
                        {{
                            savingPromo
                                ? "Guardando..."
                                : promo.id
                                ? "Actualizar"
                                : "Crear"
                        }}
                        promoción
                    </button>
                </div>
            </div>
        </BaseModal>
    </section>
</template>

<script setup>
import { reactive, ref, onMounted, computed, watch, nextTick } from "vue";
import { api } from "../../lib/api";
import { useAuth } from "../../stores/auth";
import { useSweetAlert } from "../../composables/useSweetAlert";
import Swal from "sweetalert2";
import BaseModal from "../../components/ui/BaseModal.vue";

const auth = useAuth();
const {
    success,
    error: showError,
    warning,
    confirm: showConfirm,
    info,
} = useSweetAlert();

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
const list = ref({ data: [], meta: null });
const cities = ref([]);

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
    departure_at: "",
    duration_minutes: 90,
    price_per_seat: 0,
    first_class_price: 0,
    image: null,
});

// Estado para Promociones
const promoOpen = ref(false);
const promoError = ref("");
const savingPromo = ref(false);
const promo = reactive({
    id: null,
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

// Calcular duración automáticamente cuando se selecciona origen y destino
watch(
    [() => form.origin_id, () => form.destination_id, () => form.scope],
    () => {
        calculateFlightDuration();
    }
);

// Función para calcular la duración del vuelo
function calculateFlightDuration() {
    if (!form.origin_id || !form.destination_id) return;

    const originCity = cities.value.find((c) => c.id === form.origin_id);
    
    // Velocidad según el scope
    const speed = form.scope === 'international' ? 900 : 850;

    if (originCity && originCity.distances) {
        const distanceKm = originCity.distances[form.destination_id];
        if (distanceKm && speed) {
            // Calcular tiempo en horas y convertir a minutos
            const hours = distanceKm / speed;
            const minutes = Math.round(hours * 60);
            // Agregar 15 minutos de buffer (taxi, despegue, aterrizaje)
            form.duration_minutes = minutes + 15;
        }
    }
}

// Función helper para obtener las capacidades por defecto según el scope
function getDefaultCapacities() {
    if (form.scope === 'international') {
        return {
            first: 50,
            economy: 200,
            total: 250
        };
    }
    return {
        first: 25,
        economy: 125,
        total: 150
    };
}

// --- Ciclo de Vida ---
onMounted(async () => {
    const citiesResponse = await api.get("/cities");
    cities.value = citiesResponse.data;

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

// Verificar si se puede eliminar un vuelo (solo si no tiene ventas)
function canDeleteFlight(flight) {
    // Por ahora permitimos intentar eliminar cualquier vuelo
    // El backend verificará si tiene ventas
    return true;
}

function toLocalInput(dt) {
    const d = new Date(dt);
    d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
    return d.toISOString().slice(0, 16);
}

// Formatear duración en minutos a formato legible
function formatDuration(minutes) {
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return `${hours}h ${mins}min`;
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
    first_class_price: 0,
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
        origin_id: f.origin_id,
        destination_id: f.destination_id,
        departure_at: toLocalInput(f.departure_at),
        duration_minutes: f.duration_minutes,
        price_per_seat: f.price_per_seat,
        first_class_price: f.first_class_price || f.price_per_seat * 2,
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
            errors.push("La ciudad de origen es requerida.");
        }

        if (!form.destination_id) {
            errors.push("La ciudad de destino es requerida.");
        }

        if (
            form.origin_id &&
            form.destination_id &&
            form.origin_id === form.destination_id
        ) {
            errors.push("La ciudad de destino debe ser diferente al origen.");
        }

        if (!form.departure_at) {
            errors.push("La fecha y hora de salida es requerida.");
        } else {
            // Validar que sea una fecha válida
            const departureDate = new Date(form.departure_at);
            if (isNaN(departureDate.getTime())) {
                errors.push("La fecha de salida no es válida.");
            } else if (departureDate <= new Date()) {
                errors.push(
                    "La fecha de salida debe ser posterior a la fecha actual."
                );
            }
        }

        if (!form.price_per_seat || form.price_per_seat <= 0) {
            errors.push(
                "El precio por asiento es requerido y debe ser mayor a 0."
            );
        }

        if (!form.first_class_price || form.first_class_price <= 0) {
            errors.push(
                "El precio de primera clase es requerido y debe ser mayor a 0."
            );
        }

        if (!form.duration_minutes || form.duration_minutes < 10) {
            errors.push(
                "La duración del vuelo debe ser de al menos 10 minutos."
            );
        }

        if (!form.id && !form.image) {
            errors.push("La imagen del vuelo es requerida.");
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
                showError(
                    "No se puede editar",
                    "No se puede editar un vuelo cuya fecha de salida ya pasó."
                );
            } else if (errorMessage) {
                showError("Error de validación", errorMessage);
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
    const confirmed = await showConfirm(
        "¿Cancelar vuelo?",
        `¿Seguro que quieres cancelar el vuelo ${f.code}? Esta acción no se puede deshacer.`,
        "Sí, cancelar",
        "No"
    );

    if (!confirmed) return;

    try {
        await api.post(
            `/admin/flights/${f.id}/cancel`,
            {},
            { headers: { Authorization: "Bearer " + auth.token } }
        );
        await reload();
        await success(
            "Vuelo cancelado",
            "El vuelo ha sido cancelado exitosamente."
        );
    } catch (error) {
        showError(
            "Error al cancelar el vuelo",
            error.response?.data?.message || error.message
        );
        console.error(error);
    }
}

async function deleteFlight(f) {
    const confirmed = await showConfirm(
        "¿ELIMINAR PERMANENTEMENTE?",
        `¿Seguro que quieres ELIMINAR PERMANENTEMENTE el vuelo ${f.code}? Esta acción no se puede deshacer y eliminará todos los datos asociados (asientos, noticias, promociones).`,
        "Sí, eliminar",
        "Cancelar"
    );

    if (!confirmed) return;

    try {
        await api.delete(`/admin/flights/${f.id}`, {
            headers: { Authorization: "Bearer " + auth.token },
        });
        await reload();
        await success(
            "Vuelo eliminado",
            "El vuelo ha sido eliminado correctamente."
        );
    } catch (error) {
        const errorMsg =
            error.response?.data?.error || "Error al eliminar el vuelo.";
        showError("Error al eliminar", errorMsg);
        console.error(error);
    }
}

// --- Actualizar Estados de Vuelos ---
async function updateFlightStatuses() {
    try {
        const { data } = await api.post(
            "/admin/flights/update-statuses",
            {},
            { headers: { Authorization: "Bearer " + auth.token } }
        );

        if (data.success) {
            // Recargar la lista de vuelos
            await reload();

            // Construir el mensaje HTML con los detalles
            let html = `<p class="mb-4">${data.message}</p>`;

            if (data.flights && data.flights.length > 0) {
                html += `
                    <div class="text-left bg-slate-50 rounded-lg p-4 max-h-96 overflow-y-auto">
                        <p class="font-semibold mb-3 text-slate-700">Vuelos actualizados:</p>
                        <ul class="space-y-3">
                `;

                data.flights.forEach((flight) => {
                    html += `
                        <li class="border-b border-slate-200 pb-2 last:border-0">
                            <div class="font-mono text-blue-600 font-semibold">${flight.code}</div>
                            <div class="text-sm text-slate-600">${flight.route}</div>
                            <div class="text-xs text-slate-500 mt-1">
                                <span class="inline-flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/>
                                    </svg>
                                    Salida: ${flight.departure}
                                </span>
                                <span class="ml-3 inline-flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" transform="rotate(180 10 10)"/>
                                    </svg>
                                    Llegada: ${flight.arrival}
                                </span>
                            </div>
                        </li>
                    `;
                });

                html += `
                        </ul>
                    </div>
                `;
            }

            // Mostrar SweetAlert con los detalles
            await Swal.fire({
                icon: data.updated_count > 0 ? "success" : "info",
                title:
                    data.updated_count > 0
                        ? "Estados actualizados"
                        : "Sin cambios",
                html: html,
                confirmButtonText: "Entendido",
                confirmButtonColor:
                    data.updated_count > 0 ? "#10b981" : "#3b82f6",
                width: "600px",
            });
        }
    } catch (error) {
        showError(
            "Error al actualizar estados",
            error.response?.data?.message || error.message
        );
        console.error(error);
    }
}

// --- Lógica para Promociones ---
async function openPromo(f) {
    // Validar si el vuelo ya se realizó
    const flightDate = new Date(f.departure_at);
    const now = new Date();

    if (f.status === "completed" || flightDate < now) {
        warning(
            "No disponible",
            "No se pueden crear promociones para vuelos que ya se han realizado."
        );
        return;
    }

    currentFlight.value = f;

    try {
        // Verificar si ya existe una promoción válida (incluyendo futuras)
        const { data } = await api.get(`/flights/${f.id}/promotions`, {
            headers: { Authorization: "Bearer " + auth.token },
        });

        const validPromo = data.valid_promotion;

        if (validPromo) {
            // Cargar promoción existente (puede estar activa o futura)
            Object.assign(promo, {
                id: validPromo.id,
                title: validPromo.title || `Promo ${f.code}`,
                discount_percent: validPromo.discount_percent,
                starts_at: toLocalInput(new Date(validPromo.starts_at)),
                ends_at: toLocalInput(new Date(validPromo.ends_at)),
                is_active: validPromo.is_active,
            });

            const now = new Date();
            const startsAt = new Date(validPromo.starts_at);

            if (startsAt > now) {
                await info(
                    "Promoción programada",
                    `Este vuelo tiene una promoción programada con ${
                        validPromo.discount_percent
                    }% de descuento que iniciará el ${startsAt.toLocaleDateString(
                        "es-ES"
                    )}. Puedes editarla a continuación.`
                );
            } else {
                await info(
                    "Promoción activa",
                    `Este vuelo ya tiene una promoción activa con ${validPromo.discount_percent}% de descuento. Puedes editarla a continuación.`
                );
            }
        } else {
            // Nueva promoción
            Object.assign(promo, {
                id: null,
                title: `Promo ${f.code}`,
                discount_percent: 10,
                starts_at: toLocalInput(new Date()),
                ends_at: toLocalInput(flightDate),
                is_active: true,
            });
        }
    } catch (error) {
        console.error("Error al verificar promociones:", error);
        // Si falla la verificación, continuar con nueva promoción
        Object.assign(promo, {
            id: null,
            title: `Promo ${f.code}`,
            discount_percent: 10,
            starts_at: toLocalInput(new Date()),
            ends_at: toLocalInput(flightDate),
            is_active: true,
        });
    }

    promoError.value = "";
    promoOpen.value = true;
}

async function savePromo() {
    savingPromo.value = true;
    promoError.value = "";

    try {
        const startDate = new Date(promo.starts_at);
        const endDate = new Date(promo.ends_at);
        const flightDate = new Date(currentFlight.value.departure_at);
        const now = new Date();

        // Validaciones
        const errors = [];

        // Solo validar fecha de inicio si es una nueva promoción o si se está modificando la fecha
        if (!promo.id && startDate < now) {
            errors.push(
                "La fecha de inicio no puede ser en el pasado para una nueva promoción"
            );
        }

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

        if (!promo.title || promo.title.trim() === "") {
            errors.push("El título es requerido");
        }

        if (
            !promo.discount_percent ||
            promo.discount_percent < 1 ||
            promo.discount_percent > 90
        ) {
            errors.push("El descuento debe estar entre 1% y 90%");
        }

        if (errors.length > 0) {
            promoError.value = errors.join(". ");
            savingPromo.value = false;
            return;
        }

        const payload = {
            title: promo.title.trim(),
            discount_percent: Number(promo.discount_percent),
            starts_at: startDate.toISOString(),
            ends_at: endDate.toISOString(),
            is_active: !!promo.is_active,
            description: promo.title.trim(),
        };

        const response = await api.post(
            `/flights/${currentFlight.value.id}/promotions`,
            payload,
            {
                headers: { Authorization: "Bearer " + auth.token },
            }
        );

        promoOpen.value = false;

        // Verificar si fue una actualización o creación
        if (response.data.updated) {
            await success(
                "✅ Promoción actualizada",
                `La promoción "${
                    promo.title
                }" ha sido actualizada exitosamente.${
                    !promo.is_active
                        ? " La promoción está inactiva y no será visible para los clientes."
                        : ""
                }`
            );
        } else {
            await success(
                "✅ Promoción creada",
                `La promoción "${promo.title}" ha sido creada exitosamente con ${promo.discount_percent}% de descuento.`
            );
        }

        // Recargar vuelos para mostrar la promoción actualizada
        await reload();
    } catch (e) {
        promoError.value =
            e.response?.data?.message || "Error al guardar la promoción";
        console.error(e);
    } finally {
        savingPromo.value = false;
    }
}

// Función para eliminar una promoción
async function deletePromo() {
    const confirmed = await showConfirm(
        "¿Eliminar promoción?",
        `¿Estás seguro de que quieres eliminar la promoción "${promo.title}"? También se eliminará la noticia asociada.`,
        "Sí, eliminar",
        "Cancelar"
    );

    if (!confirmed) return;

    try {
        await api.delete(`/promotions/${promo.id}`, {
            headers: { Authorization: "Bearer " + auth.token },
        });

        promoOpen.value = false;

        await success(
            "Promoción eliminada",
            "La promoción y su noticia asociada han sido eliminadas correctamente."
        );

        // Recargar vuelos
        await reload();
    } catch (error) {
        showError(
            "Error al eliminar",
            error.response?.data?.message || "Error al eliminar la promoción"
        );
        console.error(error);
    }
}

// Funciones de ayuda para el estado de las promociones
function isPromoActive(promotion) {
    if (!promotion.is_active) return false;
    const now = new Date();
    const start = new Date(promotion.starts_at);
    const end = new Date(promotion.ends_at);
    return start <= now && now <= end;
}

function isPromoFuture(promotion) {
    if (!promotion.is_active) return false;
    const now = new Date();
    const start = new Date(promotion.starts_at);
    return start > now;
}

function isPromoExpired(promotion) {
    const now = new Date();
    const end = new Date(promotion.ends_at);
    return end < now;
}

function isPromoPast(dateString) {
    return new Date(dateString) < new Date();
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleString("es-CO", {
        dateStyle: "medium",
        timeStyle: "short",
    });
}

// Validation functions
function validateNumericInput(event) {
    // Only allow numbers
    const regex = /[^0-9]/g;
    event.target.value = event.target.value.replace(regex, "");
}

// Funciones para formatear precios con puntuación
function formatPriceDisplay(value) {
    if (!value || value === 0) return "";
    return Number(value).toLocaleString("es-CO");
}

function updatePrice(event, field) {
    // Remover todo excepto números
    const numericValue = event.target.value.replace(/[^\d]/g, "");
    form[field] = numericValue ? parseInt(numericValue) : 0;
}
</script>
