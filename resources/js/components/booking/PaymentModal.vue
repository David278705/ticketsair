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
                        enter-from="opacity-0 translate-y-2 scale-95"
                        enter-to="opacity-100 translate-y-0 scale-100"
                        leave="ease-in duration-150"
                        leave-from="opacity-100 translate-y-0 scale-100"
                        leave-to="opacity-0 translate-y-2 scale-95"
                    >
                        <DialogPanel
                            class="w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white shadow-xl transition-all"
                        >
                            <div class="max-h-[90vh] overflow-y-auto">
                                <!-- Header -->
                                <div
                                    class="bg-gradient-to-r from-blue-600 to-cyan-500 p-6 text-white"
                                >
                                    <DialogTitle
                                        class="text-2xl font-bold mb-1"
                                    >
                                        💳 Finalizar Pago
                                    </DialogTitle>
                                    <p class="text-blue-50 text-sm">
                                        Revisa los detalles de tu
                                        {{
                                            bookingInfo?.action === "purchase"
                                                ? "compra"
                                                : "reserva"
                                        }}
                                        antes de continuar
                                    </p>
                                </div>

                                <div class="p-6 space-y-6">
                                    <!-- Información del Vuelo -->
                                    <div
                                        v-if="bookingInfo?.flight"
                                        class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-xl p-5 border-2 border-blue-200"
                                    >
                                        <h3
                                            class="font-bold text-gray-900 mb-3 flex items-center gap-2"
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
                                            Detalles del Vuelo
                                        </h3>
                                        <div class="space-y-2 text-sm">
                                            <div
                                                class="flex justify-between items-center"
                                            >
                                                <span class="text-gray-600"
                                                    >Código de vuelo:</span
                                                >
                                                <span
                                                    class="font-semibold text-blue-900"
                                                    >{{
                                                        bookingInfo.flight.code
                                                    }}</span
                                                >
                                            </div>
                                            <div
                                                class="flex justify-between items-center"
                                            >
                                                <span class="text-gray-600"
                                                    >Ruta:</span
                                                >
                                                <span
                                                    class="font-semibold text-gray-900"
                                                >
                                                    {{
                                                        bookingInfo.flight
                                                            .origin?.code
                                                    }}
                                                    →
                                                    {{
                                                        bookingInfo.flight
                                                            .destination?.code
                                                    }}
                                                </span>
                                            </div>
                                            <div
                                                class="flex justify-between items-center"
                                            >
                                                <span class="text-gray-600"
                                                    >Clase:</span
                                                >
                                                <span
                                                    class="font-semibold text-gray-900 capitalize"
                                                >
                                                    {{
                                                        bookingInfo.class ===
                                                        "economy"
                                                            ? "Económica"
                                                            : "Primera Clase"
                                                    }}
                                                </span>
                                            </div>
                                            <div
                                                class="flex justify-between items-center"
                                            >
                                                <span class="text-gray-600"
                                                    >Pasajeros:</span
                                                >
                                                <span
                                                    class="font-semibold text-gray-900"
                                                >
                                                    {{
                                                        bookingInfo.passengers_count
                                                    }}
                                                    {{
                                                        bookingInfo.passengers_count ===
                                                        1
                                                            ? "persona"
                                                            : "personas"
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Desglose de Precios -->
                                    <div
                                        class="bg-gray-50 rounded-xl p-5 border border-gray-200"
                                    >
                                        <h3
                                            class="font-bold text-gray-900 mb-3 flex items-center gap-2"
                                        >
                                            <svg
                                                class="w-5 h-5 text-green-600"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                                                />
                                            </svg>
                                            Desglose de Precios
                                        </h3>
                                        <div class="space-y-2 text-sm">
                                            <div
                                                class="flex justify-between items-center"
                                            >
                                                <span class="text-gray-600">
                                                    Precio por persona ({{
                                                        bookingInfo?.class ===
                                                        "economy"
                                                            ? "Económica"
                                                            : "Primera Clase"
                                                    }})
                                                </span>
                                                <span
                                                    class="font-medium text-gray-900"
                                                >
                                                    ${{
                                                        formatPrice(
                                                            getPricePerPerson()
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                            <div
                                                class="flex justify-between items-center"
                                            >
                                                <span class="text-gray-600">
                                                    Cantidad de pasajeros
                                                </span>
                                                <span
                                                    class="font-medium text-gray-900"
                                                >
                                                    ×{{
                                                        bookingInfo?.passengers_count ||
                                                        1
                                                    }}
                                                </span>
                                            </div>
                                            <div
                                                v-if="hasDiscount()"
                                                class="flex justify-between items-center text-green-600"
                                            >
                                                <span class="font-medium">
                                                    Descuento aplicado
                                                </span>
                                                <span class="font-semibold">
                                                    -${{
                                                        formatPrice(
                                                            getDiscountAmount()
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                            <div
                                                class="border-t border-gray-300 pt-2 mt-2"
                                            ></div>
                                            <div
                                                class="flex justify-between items-center"
                                            >
                                                <span
                                                    class="text-lg font-bold text-gray-900"
                                                    >Total a pagar:</span
                                                >
                                                <span
                                                    class="text-2xl font-bold text-blue-600"
                                                >
                                                    ${{
                                                        formatPrice(totalAmount)
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tipo de Operación -->
                                    <div
                                        v-if="bookingInfo?.action"
                                        class="bg-gradient-to-r from-amber-50 to-orange-50 rounded-xl p-4 border border-amber-200"
                                    >
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0"
                                            >
                                                {{
                                                    bookingInfo.action ===
                                                    "purchase"
                                                        ? "💰"
                                                        : "📋"
                                                }}
                                            </div>
                                            <div class="flex-1">
                                                <h4
                                                    class="font-semibold text-gray-900 mb-1"
                                                >
                                                    {{
                                                        bookingInfo.action ===
                                                        "purchase"
                                                            ? "Compra Directa"
                                                            : "Reserva"
                                                    }}
                                                </h4>
                                                <p
                                                    class="text-xs text-gray-600"
                                                >
                                                    {{
                                                        bookingInfo.action ===
                                                        "purchase"
                                                            ? "Al confirmar el pago, se emitirá tu recibo de compra inmediatamente y lo recibirás por correo electrónico."
                                                            : "Esta es una reserva. Tendrás 24 horas para completar el pago y confirmar tu vuelo."
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Formulario de Tarjeta -->
                                    <div
                                        class="bg-white rounded-xl p-5 border-2 border-gray-200"
                                    >
                                        <h3
                                            class="font-bold text-gray-900 mb-4 flex items-center gap-2"
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
                                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                                                />
                                            </svg>
                                            Información de Pago
                                        </h3>
                                        <div class="space-y-4">
                                            <!-- Número de Tarjeta -->
                                            <div>
                                                <label
                                                    class="block text-sm font-medium mb-1.5"
                                                >
                                                    Número de Tarjeta
                                                </label>
                                                <div class="relative">
                                                    <input
                                                        v-model="cardNumber"
                                                        @input="
                                                            formatCardNumber
                                                        "
                                                        type="text"
                                                        placeholder="1234 5678 9012 3456"
                                                        maxlength="19"
                                                        class="w-full h-12 rounded-xl border border-slate-300 px-4 pr-12 bg-transparent focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all"
                                                        :class="{
                                                            'border-rose-500':
                                                                errors.cardNumber,
                                                        }"
                                                    />
                                                    <div
                                                        class="absolute right-3 top-1/2 -translate-y-1/2"
                                                    >
                                                        <component
                                                            :is="getCardIcon()"
                                                            class="w-8 h-8"
                                                        />
                                                    </div>
                                                </div>
                                                <p
                                                    v-if="errors.cardNumber"
                                                    class="text-xs text-rose-600 mt-1"
                                                >
                                                    {{ errors.cardNumber }}
                                                </p>
                                            </div>

                                            <!-- Titular -->
                                            <div>
                                                <label
                                                    class="block text-sm font-medium mb-1.5"
                                                >
                                                    Titular de la Tarjeta
                                                </label>
                                                <input
                                                    v-model="cardHolder"
                                                    @input="
                                                        cardHolder =
                                                            cardHolder.toUpperCase()
                                                    "
                                                    type="text"
                                                    placeholder="NOMBRE COMPLETO"
                                                    class="w-full h-12 rounded-xl border border-slate-300 px-4 bg-transparent focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all uppercase"
                                                    :class="{
                                                        'border-rose-500':
                                                            errors.cardHolder,
                                                    }"
                                                />
                                                <p
                                                    v-if="errors.cardHolder"
                                                    class="text-xs text-rose-600 mt-1"
                                                >
                                                    {{ errors.cardHolder }}
                                                </p>
                                            </div>

                                            <!-- Fecha y CVV -->
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label
                                                        class="block text-sm font-medium mb-1.5"
                                                    >
                                                        Fecha de Exp.
                                                    </label>
                                                    <input
                                                        v-model="expiryDate"
                                                        @input="
                                                            formatExpiryDate
                                                        "
                                                        type="text"
                                                        placeholder="MM/AA"
                                                        maxlength="5"
                                                        class="w-full h-12 rounded-xl border border-slate-300 px-4 bg-transparent focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all"
                                                        :class="{
                                                            'border-rose-500':
                                                                errors.expiryDate,
                                                        }"
                                                    />
                                                    <p
                                                        v-if="errors.expiryDate"
                                                        class="text-xs text-rose-600 mt-1"
                                                    >
                                                        {{ errors.expiryDate }}
                                                    </p>
                                                </div>
                                                <div>
                                                    <label
                                                        class="block text-sm font-medium mb-1.5"
                                                    >
                                                        CVV
                                                    </label>
                                                    <input
                                                        v-model="cvv"
                                                        type="text"
                                                        placeholder="123"
                                                        maxlength="4"
                                                        class="w-full h-12 rounded-xl border border-slate-300 px-4 bg-transparent focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all"
                                                        :class="{
                                                            'border-rose-500':
                                                                errors.cvv,
                                                        }"
                                                    />
                                                    <p
                                                        v-if="errors.cvv"
                                                        class="text-xs text-rose-600 mt-1"
                                                    >
                                                        {{ errors.cvv }}
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- Guardar Tarjeta -->
                                            <label
                                                class="flex items-center gap-2 cursor-pointer"
                                            >
                                                <input
                                                    v-model="saveCard"
                                                    type="checkbox"
                                                    class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                                />
                                                <span
                                                    class="text-sm text-slate-600"
                                                >
                                                    Guardar tarjeta para futuras
                                                    compras
                                                </span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Mensaje de error general -->
                                    <p
                                        v-if="generalError"
                                        class="text-sm text-rose-600 bg-rose-50 p-3 rounded-xl"
                                    >
                                        {{ generalError }}
                                    </p>

                                    <!-- Botones -->
                                    <div class="flex justify-end gap-3">
                                        <button
                                            class="h-11 rounded-xl border border-slate-300 px-5 hover:bg-slate-50 transition-colors"
                                            @click="close"
                                            :disabled="processing"
                                        >
                                            Cancelar
                                        </button>
                                        <button
                                            class="h-11 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 hover:from-blue-700 hover:to-blue-800 transition-all shadow-lg shadow-blue-500/30 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                            @click="processPayment"
                                            :disabled="processing"
                                        >
                                            <span v-if="!processing"
                                                >💳 Pagar ${{
                                                    totalAmount.toLocaleString()
                                                }}</span
                                            >
                                            <span
                                                v-else
                                                class="flex items-center gap-2"
                                            >
                                                <svg
                                                    class="animate-spin h-5 w-5"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <circle
                                                        class="opacity-25"
                                                        cx="12"
                                                        cy="12"
                                                        r="10"
                                                        stroke="currentColor"
                                                        stroke-width="4"
                                                    ></circle>
                                                    <path
                                                        class="opacity-75"
                                                        fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                                    ></path>
                                                </svg>
                                                Procesando...
                                            </span>
                                        </button>
                                    </div>

                                    <!-- Seguridad -->
                                    <div
                                        class="flex items-center justify-center gap-2 text-xs text-slate-500"
                                    >
                                        <svg
                                            class="w-4 h-4"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                        Pago seguro y encriptado (Simulación)
                                    </div>
                                </div>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import { ref, watch, h } from "vue";
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionRoot,
    TransitionChild,
} from "@headlessui/vue";

const props = defineProps({
    open: Boolean,
    totalAmount: {
        type: Number,
        required: true,
    },
    bookingInfo: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["update:open", "payment-success"]);

// Card Data
const cardNumber = ref("");
const cardHolder = ref("");
const expiryDate = ref("");
const cvv = ref("");
const saveCard = ref(false);
const processing = ref(false);

// Errors
const errors = ref({});
const generalError = ref("");

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            // Reset al abrir
            cardNumber.value = "";
            cardHolder.value = "";
            expiryDate.value = "";
            cvv.value = "";
            saveCard.value = false;
            errors.value = {};
            generalError.value = "";
            processing.value = false;
        }
    }
);

// Formatear número de tarjeta
function formatCardNumber(event) {
    let value = event.target.value.replace(/\s/g, "");
    value = value.replace(/\D/g, "");

    const formatted = value.match(/.{1,4}/g)?.join(" ") || value;
    cardNumber.value = formatted;
}

// Formatear fecha de expiración
function formatExpiryDate(event) {
    let value = event.target.value.replace(/\D/g, "");

    if (value.length >= 2) {
        value = value.slice(0, 2) + "/" + value.slice(2, 4);
    }

    expiryDate.value = value;
}

// Detectar tipo de tarjeta
function getCardType() {
    const number = cardNumber.value.replace(/\s/g, "");

    if (/^4/.test(number)) return "visa";
    if (/^5[1-5]/.test(number)) return "mastercard";
    if (/^3[47]/.test(number)) return "amex";

    return "unknown";
}

// Icono de tarjeta
function getCardIcon() {
    const type = getCardType();

    if (type === "visa") {
        return h("div", { class: "text-blue-600 font-bold text-sm" }, "VISA");
    }
    if (type === "mastercard") {
        return h("div", { class: "flex gap-0.5" }, [
            h("div", { class: "w-4 h-4 rounded-full bg-rose-500 opacity-80" }),
            h("div", {
                class: "w-4 h-4 rounded-full bg-amber-500 opacity-80 -ml-2",
            }),
        ]);
    }
    if (type === "amex") {
        return h("div", { class: "text-blue-700 font-bold text-xs" }, "AMEX");
    }

    return h("div", {
        class: "w-8 h-8 rounded border-2 border-dashed border-slate-300",
    });
}

// Formatear precio
function formatPrice(price) {
    return Number(price).toLocaleString("es-CO", {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    });
}

// Obtener precio por persona según clase
function getPricePerPerson() {
    if (!props.bookingInfo?.flight) return 0;

    const basePrice = props.bookingInfo.flight.price_per_seat;

    // Si es primera clase, el precio es el doble
    if (props.bookingInfo.class === "first") {
        return basePrice * 2;
    }

    return basePrice;
}

// Verificar si hay descuento
function hasDiscount() {
    if (!props.bookingInfo) return false;

    const pricePerPerson = getPricePerPerson();
    const passengersCount = props.bookingInfo.passengers_count || 1;
    const subtotal = pricePerPerson * passengersCount;

    return subtotal > props.totalAmount;
}

// Obtener monto del descuento
function getDiscountAmount() {
    if (!hasDiscount()) return 0;

    const pricePerPerson = getPricePerPerson();
    const passengersCount = props.bookingInfo.passengers_count || 1;
    const subtotal = pricePerPerson * passengersCount;

    return subtotal - props.totalAmount;
}

// Validar formulario
function validate() {
    errors.value = {};
    generalError.value = "";

    // Número de tarjeta
    const cleanNumber = cardNumber.value.replace(/\s/g, "");
    if (!cleanNumber) {
        errors.value.cardNumber = "El número de tarjeta es requerido";
    } else if (cleanNumber.length < 13 || cleanNumber.length > 19) {
        errors.value.cardNumber = "Número de tarjeta inválido";
    } else if (!/^\d+$/.test(cleanNumber)) {
        errors.value.cardNumber = "Solo números permitidos";
    }

    // Titular
    if (!cardHolder.value.trim()) {
        errors.value.cardHolder = "El titular es requerido";
    } else if (cardHolder.value.trim().length < 3) {
        errors.value.cardHolder = "Nombre muy corto";
    }

    // Fecha de expiración
    if (!expiryDate.value) {
        errors.value.expiryDate = "Fecha requerida";
    } else {
        const [month, year] = expiryDate.value.split("/");
        const currentYear = new Date().getFullYear() % 100;
        const currentMonth = new Date().getMonth() + 1;

        if (!month || !year) {
            errors.value.expiryDate = "Formato MM/AA";
        } else if (parseInt(month) < 1 || parseInt(month) > 12) {
            errors.value.expiryDate = "Mes inválido";
        } else if (
            parseInt(year) < currentYear ||
            (parseInt(year) === currentYear && parseInt(month) < currentMonth)
        ) {
            errors.value.expiryDate = "Tarjeta expirada";
        }
    }

    // CVV
    if (!cvv.value) {
        errors.value.cvv = "CVV requerido";
    } else if (cvv.value.length < 3 || cvv.value.length > 4) {
        errors.value.cvv = "CVV inválido";
    } else if (!/^\d+$/.test(cvv.value)) {
        errors.value.cvv = "Solo números";
    }

    return Object.keys(errors.value).length === 0;
}

// Procesar pago
async function processPayment() {
    if (!validate()) return;

    processing.value = true;

    try {
        // Simular procesamiento (2 segundos)
        await new Promise((resolve) => setTimeout(resolve, 2000));

        // Simular éxito/fallo aleatorio (95% éxito)
        if (Math.random() < 0.95) {
            const paymentData = {
                card_number: cardNumber.value.replace(/\s/g, ""),
                card_holder: cardHolder.value,
                expiry_date: expiryDate.value,
                cvv: cvv.value,
                card_type: getCardType(),
                save_card: saveCard.value,
                // Datos procesados
                last_four: cardNumber.value.replace(/\s/g, "").slice(-4),
                transaction_id: `TXN-${Date.now()}-${Math.random()
                    .toString(36)
                    .substr(2, 9)
                    .toUpperCase()}`,
            };

            emit("payment-success", paymentData);
            close();
        } else {
            generalError.value =
                "Pago rechazado. Por favor, verifica los datos de tu tarjeta o intenta con otra.";
        }
    } catch (error) {
        generalError.value =
            "Error al procesar el pago. Por favor, intenta nuevamente.";
    } finally {
        processing.value = false;
    }
}

function close() {
    if (!processing.value) {
        emit("update:open", false);
    }
}
</script>
