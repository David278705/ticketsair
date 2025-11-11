<template>
    <div class="payment-method-selector">
        <h3 class="text-lg font-bold text-gray-800 mb-4">
            💳 Método de Pago
        </h3>

        <!-- Opciones de pago -->
        <div class="space-y-3">
            <!-- Opción: Saldo del Wallet -->
            <label
                class="flex items-start gap-3 p-4 rounded-lg border-2 cursor-pointer transition-all"
                :class="{
                    'border-blue-500 bg-blue-50': selectedMethod === 'wallet',
                    'border-gray-200 hover:border-gray-300 hover:bg-gray-50':
                        selectedMethod !== 'wallet',
                }"
            >
                <input
                    type="radio"
                    name="payment_method"
                    value="wallet"
                    v-model="selectedMethod"
                    class="mt-1"
                />
                <div class="flex-1">
                    <div class="flex justify-between items-center">
                        <div class="font-semibold text-gray-800">
                            💰 Saldo disponible
                        </div>
                        <div class="text-lg font-bold text-blue-600">
                            ${{ formatMoney(walletBalance) }}
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mt-1">
                        Usa tu saldo para pagar esta compra
                    </p>
                    <p
                        v-if="selectedMethod === 'wallet' && walletBalance < totalAmount"
                        class="text-xs text-red-600 mt-2 bg-red-50 p-2 rounded"
                    >
                        ⚠️ Saldo insuficiente. Saldo faltante: ${{
                            formatMoney(totalAmount - walletBalance)
                        }}
                    </p>
                </div>
            </label>

            <!-- Opción: Tarjeta guardada -->
            <div
                v-for="card in savedCards"
                :key="card.id"
            >
                <label
                    class="flex items-start gap-3 p-4 rounded-lg border-2 cursor-pointer transition-all"
                    :class="{
                        'border-blue-500 bg-blue-50':
                            selectedMethod === `card_${card.id}`,
                        'border-gray-200 hover:border-gray-300 hover:bg-gray-50':
                            selectedMethod !== `card_${card.id}`,
                        'opacity-50 cursor-not-allowed': card.is_expired,
                    }"
                >
                    <input
                        type="radio"
                        name="payment_method"
                        :value="`card_${card.id}`"
                        v-model="selectedMethod"
                        :disabled="card.is_expired"
                        class="mt-1"
                    />
                    <div class="flex-1">
                        <div class="flex justify-between items-center">
                            <div class="font-semibold text-gray-800 flex items-center gap-2">
                                <span>{{ getCardIcon(card.brand) }}</span>
                                <span>{{ card.masked_number }}</span>
                                <span
                                    v-if="card.is_default"
                                    class="px-2 py-1 bg-blue-500 text-white text-xs rounded-full"
                                >
                                    Predeterminada
                                </span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ card.holder_name }} •
                            {{
                                card.card_type === "credit"
                                    ? "Crédito"
                                    : "Débito"
                            }}
                            • Vence {{ card.exp_month }}/{{ card.exp_year }}
                        </p>
                        <p
                            v-if="card.is_expired"
                            class="text-xs text-red-600 mt-1"
                        >
                            ⚠️ Tarjeta expirada
                        </p>
                    </div>
                </label>
            </div>

            <!-- Opción: Nueva tarjeta -->
            <label
                class="flex items-start gap-3 p-4 rounded-lg border-2 cursor-pointer transition-all"
                :class="{
                    'border-blue-500 bg-blue-50': selectedMethod === 'new_card',
                    'border-gray-200 hover:border-gray-300 hover:bg-gray-50':
                        selectedMethod !== 'new_card',
                }"
            >
                <input
                    type="radio"
                    name="payment_method"
                    value="new_card"
                    v-model="selectedMethod"
                    class="mt-1"
                />
                <div class="flex-1">
                    <div class="font-semibold text-gray-800">
                        ➕ Agregar nueva tarjeta
                    </div>
                    <p class="text-sm text-gray-600 mt-1">
                        Paga con una nueva tarjeta de crédito o débito
                    </p>
                </div>
            </label>
        </div>

        <!-- Formulario de nueva tarjeta (si se selecciona) -->
        <div
            v-if="selectedMethod === 'new_card'"
            class="mt-4 p-4 bg-gray-50 rounded-lg space-y-3"
        >
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Tipo de tarjeta *</label
                >
                <select
                    v-model="newCard.card_type"
                    required
                    class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">Seleccionar...</option>
                    <option value="credit">Tarjeta de Crédito</option>
                    <option value="debit">Tarjeta de Débito</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Nombre del titular *</label
                >
                <input
                    v-model="newCard.holder_name"
                    type="text"
                    required
                    class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Número de tarjeta *</label
                >
                <input
                    v-model="newCard.card_number"
                    type="text"
                    required
                    maxlength="16"
                    placeholder="1234567890123456"
                    @input="validateNumericInput"
                    class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-blue-500 font-mono"
                />
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Mes *</label
                    >
                    <input
                        v-model="newCard.exp_month"
                        type="text"
                        required
                        maxlength="2"
                        placeholder="MM"
                        @input="validateNumericInput"
                        class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-blue-500"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Año *</label
                    >
                    <input
                        v-model="newCard.exp_year"
                        type="text"
                        required
                        maxlength="4"
                        placeholder="AAAA"
                        @input="validateNumericInput"
                        class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-blue-500"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >CVV *</label
                    >
                    <input
                        v-model="newCard.cvv"
                        type="text"
                        required
                        maxlength="4"
                        placeholder="123"
                        @input="validateNumericInput"
                        class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-blue-500"
                    />
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input
                    v-model="newCard.save_card"
                    type="checkbox"
                    id="save_card"
                    class="w-4 h-4"
                />
                <label for="save_card" class="text-sm text-gray-700"
                    >Guardar esta tarjeta para futuras compras</label
                >
            </div>
        </div>

        <!-- Resumen del pago -->
        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
            <div class="flex justify-between text-sm text-gray-600 mb-2">
                <span>Subtotal:</span>
                <span>${{ formatMoney(totalAmount) }}</span>
            </div>
            <div class="flex justify-between text-lg font-bold text-gray-900">
                <span>Total a pagar:</span>
                <span>${{ formatMoney(totalAmount) }}</span>
            </div>
        </div>

        <!-- Validación -->
        <p v-if="validationError" class="mt-4 text-red-600 text-sm bg-red-50 p-3 rounded-lg">
            {{ validationError }}
        </p>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { api } from "../../lib/api";
import { useAuth } from "../../stores/auth";

const props = defineProps({
    totalAmount: {
        type: Number,
        required: true,
    },
    modelValue: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["update:modelValue", "valid", "invalid"]);

const auth = useAuth();

// Estado
const walletBalance = ref(0);
const savedCards = ref([]);
const selectedMethod = ref("");
const validationError = ref("");

// Nueva tarjeta
const newCard = ref({
    card_type: "",
    holder_name: "",
    card_number: "",
    exp_month: "",
    exp_year: "",
    cvv: "",
    save_card: false,
});

// Cargar datos
onMounted(async () => {
    await loadPaymentMethods();
    
    // Seleccionar método predeterminado
    if (savedCards.value.length > 0) {
        const defaultCard = savedCards.value.find((c) => c.is_default);
        if (defaultCard) {
            selectedMethod.value = `card_${defaultCard.id}`;
        }
    }
});

async function loadPaymentMethods() {
    try {
        // Cargar tarjetas
        const cardsRes = await api.get("/payment-methods", {
            headers: { Authorization: `Bearer ${auth.token}` },
        });
        savedCards.value = cardsRes.data.cards;

        // Cargar saldo
        const walletRes = await api.get("/wallet", {
            headers: { Authorization: `Bearer ${auth.token}` },
        });
        walletBalance.value = parseFloat(walletRes.data.balance);
    } catch (err) {
        console.error("Error al cargar métodos de pago:", err);
    }
}

// Validar cuando cambie la selección
watch([selectedMethod, newCard], validate, { deep: true });

function validate() {
    validationError.value = "";
    
    if (!selectedMethod.value) {
        validationError.value = "Por favor selecciona un método de pago";
        emit("invalid");
        return false;
    }

    if (selectedMethod.value === "wallet") {
        if (walletBalance.value < props.totalAmount) {
            validationError.value = "Saldo insuficiente";
            emit("invalid");
            return false;
        }
    }

    if (selectedMethod.value === "new_card") {
        if (!newCard.value.card_type || !newCard.value.holder_name || 
            !newCard.value.card_number || !newCard.value.exp_month || 
            !newCard.value.exp_year || !newCard.value.cvv) {
            validationError.value = "Por favor completa todos los campos de la tarjeta";
            emit("invalid");
            return false;
        }

        if (newCard.value.card_number.length !== 16) {
            validationError.value = "El número de tarjeta debe tener 16 dígitos";
            emit("invalid");
            return false;
        }
    }

    // Emitir método de pago seleccionado
    const paymentMethod = {
        type: selectedMethod.value === "wallet" ? "wallet" : 
              selectedMethod.value === "new_card" ? "new_card" : "saved_card",
        cardId: selectedMethod.value.startsWith("card_") 
            ? selectedMethod.value.replace("card_", "") 
            : null,
        newCardData: selectedMethod.value === "new_card" ? newCard.value : null,
    };

    emit("update:modelValue", paymentMethod);
    emit("valid");
    return true;
}

function validateNumericInput(event) {
    event.target.value = event.target.value.replace(/\D/g, "");
}

function formatMoney(amount) {
    return Number(amount).toLocaleString("es-CO");
}

function getCardIcon(brand) {
    const icons = {
        visa: "💳",
        mastercard: "💳",
        amex: "💳",
        discover: "💳",
        unknown: "💳",
    };
    return icons[brand] || "💳";
}

// Exponer método de validación
defineExpose({ validate });
</script>

<style scoped>
.payment-method-selector {
    @apply w-full;
}
</style>
