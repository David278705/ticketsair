<template>
    <div class="container mx-auto px-4 py-8 space-y-12">
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-12">
            <div
                class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"
            ></div>
            <p class="mt-4 text-gray-500">Cargando noticias...</p>
        </div>

        <template v-else>
            <!-- SECCIÓN 1: PROMOCIONES -->
            <section
                v-if="promotions.length > 0"
                class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-2xl p-6 border-2 border-blue-200 shadow-sm"
            >
                <div class="flex items-center justify-between mb-6">
                    <h2
                        class="text-3xl font-bold text-gray-900 flex items-center gap-2"
                    >
                        Promociones Activas
                    </h2>
                    <span class="text-sm text-gray-500"
                        >{{ promotions.length }} disponibles</span
                    >
                </div>

                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="item in promotions"
                        :key="'promo-' + item.id"
                        class="bg-gradient-to-br from-blue-50 to-cyan-50 border-2 border-blue-200 rounded-xl shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1"
                    >
                        <!-- Imagen -->
                        <div class="relative">
                            <img
                                v-if="item.image_path"
                                class="rounded-t-xl w-full h-48 object-cover"
                                :src="getImageUrl(item.image_path)"
                                :alt="item.title"
                            />
                            <div
                                v-else
                                class="rounded-t-xl w-full h-48 bg-gradient-to-br from-blue-500 via-cyan-400 to-blue-600 flex items-center justify-center"
                            >
                                <svg
                                    class="w-20 h-20 text-white opacity-40"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"
                                    />
                                </svg>
                            </div>

                            <!-- Badge de Descuento -->
                            <div
                                v-if="item.promotion"
                                class="absolute top-3 right-3 bg-gradient-to-r from-rose-500 to-pink-500 text-white px-4 py-2 rounded-full font-bold shadow-lg transform rotate-3"
                            >
                                -{{ item.promotion.discount_percent }}%
                            </div>
                        </div>

                        <!-- Contenido -->
                        <div class="p-5">
                            <h5
                                class="mb-2 text-xl font-bold tracking-tight text-gray-900"
                            >
                                {{ item.title }}
                            </h5>

                            <!-- Info del Vuelo MEJORADA -->
                            <div
                                v-if="item.flight"
                                class="mb-3 p-4 bg-white/90 rounded-xl border-2 border-blue-200 shadow-sm"
                            >
                                <!-- Header con código y tipo -->
                                <div
                                    class="flex items-center justify-between mb-3"
                                >
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center"
                                        >
                                            <svg
                                                class="w-5 h-5 text-blue-600"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"
                                                />
                                            </svg>
                                        </div>
                                        <span
                                            class="font-bold text-blue-900 text-base"
                                            >{{ item.flight.code }}</span
                                        >
                                    </div>
                                    <span
                                        class="text-xs px-2 py-1 rounded-full font-medium"
                                        :class="
                                            item.flight.scope ===
                                            'international'
                                                ? 'bg-purple-100 text-purple-700'
                                                : 'bg-green-100 text-green-700'
                                        "
                                    >
                                        {{
                                            item.flight.scope ===
                                            "international"
                                                ? "🌍 Internacional"
                                                : "🇨🇴 Nacional"
                                        }}
                                    </span>
                                </div>

                                <!-- Ruta visualizada -->
                                <div
                                    class="flex items-center justify-between mb-3 px-2"
                                >
                                    <div class="text-center flex-1">
                                        <p
                                            class="text-2xl font-black text-blue-600"
                                        >
                                            {{ item.flight.origin?.code }}
                                        </p>
                                        <p
                                            class="text-xs text-gray-500 mt-1 line-clamp-1"
                                        >
                                            {{ item.flight.origin?.name }}
                                        </p>
                                    </div>
                                    <div
                                        class="flex-1 flex items-center justify-center"
                                    >
                                        <div
                                            class="relative w-full max-w-[80px]"
                                        >
                                            <div
                                                class="h-0.5 bg-gradient-to-r from-blue-400 to-cyan-400 w-full"
                                            ></div>
                                            <svg
                                                class="absolute -top-2 right-0 w-5 h-5 text-cyan-500"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="text-center flex-1">
                                        <p
                                            class="text-2xl font-black text-cyan-600"
                                        >
                                            {{ item.flight.destination?.code }}
                                        </p>
                                        <p
                                            class="text-xs text-gray-500 mt-1 line-clamp-1"
                                        >
                                            {{ item.flight.destination?.name }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Precios -->
                                <div
                                    class="flex items-center justify-between pt-3 border-t border-blue-100"
                                >
                                    <div class="flex items-baseline gap-2">
                                        <span
                                            class="text-xs text-gray-500 font-medium"
                                            >Desde</span
                                        >
                                        <span
                                            v-if="item.promotion"
                                            class="text-sm line-through text-gray-400"
                                        >
                                            {{
                                                formatPrice(
                                                    item.flight.price_per_seat
                                                )
                                            }}
                                        </span>
                                        <span
                                            class="text-xl font-black"
                                            :class="
                                                item.promotion
                                                    ? 'text-green-600'
                                                    : 'text-blue-600'
                                            "
                                        >
                                            {{
                                                formatPrice(
                                                    calculateDiscountedPrice(
                                                        item.flight
                                                            .price_per_seat,
                                                        item.promotion
                                                            ?.discount_percent
                                                    )
                                                )
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <p class="mb-4 text-sm text-gray-700 line-clamp-2">
                                {{ item.body || "Sin descripción disponible" }}
                            </p>

                            <!-- Botones de Acción -->
                            <div v-if="item.flight" class="flex gap-2">
                                <button
                                    @click="
                                        openBookingModal(
                                            item.flight,
                                            'reservation'
                                        )
                                    "
                                    class="flex-1 h-10 rounded-lg border-2 border-blue-600 text-blue-600 font-semibold hover:bg-blue-50 transition-colors"
                                >
                                    Reservar
                                </button>
                                <button
                                    @click="
                                        openBookingModal(
                                            item.flight,
                                            'purchase'
                                        )
                                    "
                                    class="flex-1 h-10 rounded-lg bg-gradient-to-r from-blue-600 to-cyan-500 text-white font-semibold hover:from-blue-700 hover:to-cyan-600 transition-all shadow-md"
                                >
                                    Comprar
                                </button>
                            </div>

                            <!-- Fecha -->
                            <div class="mt-3 text-xs text-gray-500 text-center">
                                {{ formatDate(item.created_at) }}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Empty State Promociones -->
            <section v-else class="text-center py-8">
                <p class="text-gray-500">
                    No hay promociones activas en este momento
                </p>
            </section>

            <!-- SECCIÓN 2: NOTICIAS GENERALES -->
            <section
                v-if="regularNews.length > 0"
                class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm"
            >
                <div class="flex items-center justify-between mb-6">
                    <h2
                        class="text-3xl font-bold text-gray-900 flex items-center gap-2"
                    >
                        Últimas Noticias
                    </h2>
                    <span class="text-sm text-gray-500"
                        >{{ regularNews.length }} publicadas</span
                    >
                </div>

                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="item in regularNews"
                        :key="'news-' + item.id"
                        class="bg-white border border-slate-200 rounded-xl shadow-sm hover:shadow-md transition-all duration-300 transform hover:-translate-y-0.5 overflow-hidden"
                    >
                        <!-- Imagen -->
                        <div class="relative">
                            <img
                                v-if="item.image_path"
                                class="w-full h-48 object-cover"
                                :src="getImageUrl(item.image_path)"
                                :alt="item.title"
                            />
                            <div
                                v-else
                                class="w-full h-48 bg-gradient-to-br from-slate-100 via-gray-100 to-slate-200 flex items-center justify-center"
                            >
                                <svg
                                    class="w-16 h-16 text-slate-400 opacity-60"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"
                                    />
                                </svg>
                            </div>

                            <!-- Fecha Badge -->
                            <div
                                class="absolute top-3 left-3 bg-white/95 backdrop-blur-sm px-3 py-1.5 rounded-lg shadow-sm border border-slate-200"
                            >
                                <span
                                    class="text-xs font-medium text-slate-600"
                                >
                                    {{ formatDate(item.created_at) }}
                                </span>
                            </div>
                        </div>

                        <!-- Contenido -->
                        <div class="p-5">
                            <h5
                                class="mb-3 text-lg font-bold tracking-tight text-gray-900 line-clamp-2"
                            >
                                {{ item.title }}
                            </h5>

                            <!-- Info del Vuelo (si existe) -->
                            <div
                                v-if="item.flight"
                                class="mb-3 p-3 bg-gradient-to-br from-slate-50 to-gray-50 rounded-lg border border-slate-200"
                            >
                                <div
                                    class="flex items-center justify-between mb-2"
                                >
                                    <div class="flex items-center gap-1.5">
                                        <div
                                            class="w-6 h-6 bg-slate-100 rounded-full flex items-center justify-center"
                                        >
                                            <svg
                                                class="w-3.5 h-3.5 text-slate-600"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"
                                                />
                                            </svg>
                                        </div>
                                        <span
                                            class="font-semibold text-slate-700 text-sm"
                                            >{{ item.flight.code }}</span
                                        >
                                    </div>
                                    <span
                                        class="text-slate-600 font-medium text-xs px-2 py-1 bg-white rounded-md"
                                        :class="
                                            item.flight.scope ===
                                            'international'
                                                ? 'border border-purple-200'
                                                : 'border border-green-200'
                                        "
                                    >
                                        {{
                                            item.flight.scope ===
                                            "international"
                                                ? "🌍"
                                                : "🇨🇴"
                                        }}
                                    </span>
                                </div>

                                <!-- Ruta simplificada -->
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 flex-1">
                                        <span
                                            class="text-sm font-bold text-slate-700"
                                        >
                                            {{ item.flight.origin?.code }}
                                        </span>
                                        <svg
                                            class="w-4 h-4 text-slate-400"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                        <span
                                            class="text-sm font-bold text-slate-700"
                                        >
                                            {{ item.flight.destination?.code }}
                                        </span>
                                    </div>
                                </div>

                                <div
                                    class="flex items-center justify-between mt-2 pt-2 border-t border-slate-200"
                                >
                                    <span class="text-xs text-slate-500"
                                        >Desde</span
                                    >
                                    <div class="flex items-center gap-2">
                                        <span
                                            v-if="item.flight.active_promotion"
                                            class="text-xs line-through text-slate-400"
                                        >
                                            {{
                                                formatPrice(
                                                    item.flight.price_per_seat
                                                )
                                            }}
                                        </span>
                                        <span
                                            class="text-base font-bold"
                                            :class="
                                                item.flight.active_promotion
                                                    ? 'text-green-600'
                                                    : 'text-slate-700'
                                            "
                                        >
                                            {{
                                                formatPrice(
                                                    calculateDiscountedPrice(
                                                        item.flight
                                                            .price_per_seat,
                                                        item.flight
                                                            .active_promotion
                                                            ?.discount_percent
                                                    )
                                                )
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <p class="mb-3 text-sm text-gray-700 line-clamp-3">
                                {{ item.body || "Sin descripción disponible" }}
                            </p>

                            <!-- Botones si hay vuelo -->
                            <div v-if="item.flight" class="flex gap-2 mb-3">
                                <button
                                    @click="
                                        openBookingModal(
                                            item.flight,
                                            'reservation'
                                        )
                                    "
                                    class="flex-1 h-9 text-sm rounded-lg border border-blue-600 text-blue-600 font-medium hover:bg-blue-50 transition-colors"
                                >
                                    Reservar
                                </button>
                                <button
                                    @click="
                                        openBookingModal(
                                            item.flight,
                                            'purchase'
                                        )
                                    "
                                    class="flex-1 h-9 text-sm rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 transition-colors"
                                >
                                    Comprar
                                </button>
                            </div>

                            <!-- Fecha -->
                            <div class="text-xs text-gray-500 text-center">
                                {{ formatDate(item.created_at) }}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Empty State si no hay nada -->
            <section
                v-if="promotions.length === 0 && regularNews.length === 0"
                class="text-center py-12"
            >
                <svg
                    class="mx-auto h-16 w-16 text-gray-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"
                    />
                </svg>
                <p class="mt-4 text-gray-500">No hay noticias disponibles</p>
            </section>
        </template>

        <!-- Modal de Información del Vuelo + Número de Pasajeros -->
        <FlightInfoModal
            v-model:open="flightInfoOpen"
            :flight="selectedFlight"
            :action-type="selectedAction"
            @submit="onFlightInfoSubmit"
        />

        <!-- Modal de Pasajeros con selección de clase individual -->
        <PassengersModal
            v-model:open="passengersOpen"
            :passengers-count="passengersCount"
            :base-price-per-seat="discountedBasePricePerSeat"
            :first-class-price="discountedFirstClassPrice"
            @submit="onPassengersSubmit"
        />

        <!-- Modal de Pago -->
        <PaymentModal
            v-model:open="paymentOpen"
            :total-amount="pendingBooking?.total_amount || 0"
            :booking-info="pendingBooking"
            @payment-success="onPaymentSuccess"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { api, BASE_URL } from "../../lib/api";
import { useAuth } from "../../stores/auth";
import { useUi } from "../../stores/ui";
import { useCurrency } from "../../composables/useCurrency";
import { useSweetAlert } from "../../composables/useSweetAlert";
import FlightInfoModal from "../booking/FlightInfoModal.vue";
import PassengersModal from "../booking/PassengersModal.vue";
import PaymentModal from "../booking/PaymentModal.vue";

const auth = useAuth();
const ui = useUi();
const { formatPrice: formatPriceCurrency } = useCurrency();
const { success, error: showError } = useSweetAlert();

const loading = ref(true);
const allNews = ref([]);

// Estados del flujo de reserva/compra
const flightInfoOpen = ref(false);
const passengersOpen = ref(false);
const paymentOpen = ref(false);
const selectedFlight = ref(null);
const selectedAction = ref(null); // 'reservation' | 'purchase'
const passengersCount = ref(1); // Número de pasajeros
const pendingPassengers = ref([]);
const pendingBooking = ref(null);
const actionLoading = ref(false);

// Precios con descuento aplicado para el modal de pasajeros
const discountedBasePricePerSeat = computed(() => {
    if (!selectedFlight.value) return 0;
    const basePrice = parseFloat(selectedFlight.value.price_per_seat) || 0;
    if (selectedFlight.value.active_promotion) {
        const discount = selectedFlight.value.active_promotion.discount_percent;
        return basePrice * (1 - discount / 100);
    }
    return basePrice;
});

const discountedFirstClassPrice = computed(() => {
    if (!selectedFlight.value) return 0;
    const firstPrice =
        parseFloat(selectedFlight.value.first_class_price) ||
        parseFloat(selectedFlight.value.price_per_seat) * 2 ||
        0;
    if (selectedFlight.value.active_promotion) {
        const discount = selectedFlight.value.active_promotion.discount_percent;
        return firstPrice * (1 - discount / 100);
    }
    return firstPrice;
});

// Separar promociones y noticias
const promotions = computed(() =>
    allNews.value.filter((n) => n.is_promotion && n.flight)
);

const regularNews = computed(() =>
    allNews.value.filter((n) => !n.is_promotion)
);

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("es-ES", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};

const formatPrice = (price) => {
    return formatPriceCurrency(Number(price));
};

const calculateDiscountedPrice = (price, discount) => {
    if (!discount) return price;
    return price * (1 - discount / 100);
};

const getImageUrl = (imagePath) => {
    if (!imagePath) return null;
    if (imagePath.startsWith("http")) return imagePath;
    return `${BASE_URL}/storage/${imagePath}`;
};

// Abrir modal de booking
const openBookingModal = (flight, actionType) => {
    if (!auth.user) {
        ui.openAuth("login");
        return;
    }

    selectedFlight.value = flight;
    selectedAction.value = actionType;
    flightInfoOpen.value = true;
};

// Continuar desde modal de info del vuelo
const onFlightInfoSubmit = ({ passengersCount: count }) => {
    passengersCount.value = count;
    flightInfoOpen.value = false;
    passengersOpen.value = true;
};

// Recibir pasajeros y continuar
const onPassengersSubmit = async (passengers) => {
    pendingPassengers.value = passengers;
    passengersOpen.value = false;

    // Calcular total basado en las clases individuales de cada pasajero
    // usando precios ya con descuento aplicado
    let totalAmount = 0;

    // Sumar el precio de cada pasajero según su clase
    passengers.forEach((p) => {
        if (p.flight_class === "first") {
            totalAmount += discountedFirstClassPrice.value;
        } else {
            totalAmount += discountedBasePricePerSeat.value;
        }
    });

    pendingBooking.value = {
        total_amount: totalAmount,
        passengers_count: passengers.length,
        passengers: passengers,
        flight: selectedFlight.value,
        action: selectedAction.value,
    };

    // Si es reserva, procesar directamente sin pago
    if (selectedAction.value === "reservation") {
        await processBooking(null);
    } else {
        // Si es compra, abrir modal de pago
        paymentOpen.value = true;
    }
};

// Procesar reserva o compra
const processBooking = async (paymentData) => {
    actionLoading.value = true;

    try {
        // Enviar cada pasajero con su clase individual
        const payload = {
            flight_id: selectedFlight.value.id,
            type: selectedAction.value,
            passengers: pendingPassengers.value.map((p) => ({
                dni: p.dni,
                first_name: p.first_name,
                last_name: p.last_name,
                birth_date: p.birth_date,
                gender: p.gender,
                email: p.email,
                class: p.flight_class,
            })),
        };

        // Solo agregar payment si es compra
        if (paymentData) {
            payload.payment = paymentData;
        }

        const response = await api.post("/bookings", payload, {
            headers: { Authorization: "Bearer " + auth.token },
        });

        const booking = response.data;
        const isPurchase = selectedAction.value === "purchase";

        const message = isPurchase
            ? `Código de Reserva: ${booking.reservation_code}\n\nRevisa tu correo para más detalles.`
            : `Código de Reserva: ${booking.reservation_code}\n\nTienes 24 horas para completar tu compra. Recibirás un recordatorio por correo.`;

        await success(
            isPurchase
                ? "¡Compra realizada exitosamente!"
                : "¡Reserva creada exitosamente!",
            message
        );

        setTimeout(() => {
            window.location.href = "/mis-viajes";
        }, 2000);
    } catch (e) {
        const errorMessage =
            e.response?.data?.message ||
            e.response?.data?.error ||
            "No se pudo procesar la solicitud.";
        showError("Error al procesar", errorMessage);
    } finally {
        actionLoading.value = false;
        paymentOpen.value = false;
    }
};

// Procesar pago
const onPaymentSuccess = async (paymentData) => {
    await processBooking(paymentData);
};

onMounted(async () => {
    try {
        const response = await api.get("/news");
        allNews.value = response.data.data || [];
    } catch (error) {
        console.error("Error fetching news:", error);
    } finally {
        loading.value = false;
    }
});
</script>
