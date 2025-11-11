<template>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">
            💳 Gestión Financiera
        </h1>

        <!-- Saldo del Wallet -->
        <div
            class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl shadow-xl p-8 text-white mb-8"
        >
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-blue-100 text-sm mb-2">Saldo disponible</p>
                    <h2 class="text-5xl font-bold">
                        ${{ formatMoney(walletBalance) }}
                    </h2>
                    <p class="text-blue-100 text-sm mt-2">COP (Pesos colombianos)</p>
                </div>
                <button
                    @click="openRechargeModal"
                    class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-blue-50 transition-colors shadow-lg"
                >
                    ➕ Recargar saldo
                </button>
            </div>
        </div>

        <!-- Tarjetas y Transacciones -->
        <div class="grid lg:grid-cols-2 gap-8">
            <!-- Sección de Tarjetas -->
            <div>
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">
                        💳 Mis Tarjetas
                    </h2>
                    <button
                        @click="openAddCardModal"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700 transition-colors"
                    >
                        ➕ Agregar tarjeta
                    </button>
                </div>

                <div v-if="loadingCards" class="text-center py-8">
                    <div
                        class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"
                    ></div>
                </div>

                <div v-else-if="cards.length === 0" class="text-center py-12 bg-gray-50 rounded-lg">
                    <p class="text-gray-500 mb-4">No tienes tarjetas guardadas</p>
                    <button
                        @click="openAddCardModal"
                        class="text-blue-600 hover:text-blue-700 font-medium"
                    >
                        Agregar tu primera tarjeta →
                    </button>
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="card in cards"
                        :key="card.id"
                        class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow"
                        :class="{
                            'border-2 border-blue-500': card.is_default,
                        }"
                    >
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-3">
                                    <span class="text-2xl">{{
                                        getCardIcon(card.brand)
                                    }}</span>
                                    <div>
                                        <p class="font-semibold text-gray-800">
                                            {{ card.holder_name }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            {{ card.masked_number }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4 text-sm">
                                    <span
                                        class="px-3 py-1 rounded-full font-medium"
                                        :class="{
                                            'bg-blue-100 text-blue-700':
                                                card.card_type === 'credit',
                                            'bg-green-100 text-green-700':
                                                card.card_type === 'debit',
                                        }"
                                    >
                                        {{
                                            card.card_type === "credit"
                                                ? "Crédito"
                                                : "Débito"
                                        }}
                                    </span>
                                    <span class="text-gray-600"
                                        >Vence: {{ card.exp_month }}/{{
                                            card.exp_year
                                        }}</span
                                    >
                                    <span
                                        v-if="card.is_expired"
                                        class="px-3 py-1 bg-red-100 text-red-700 rounded-full font-medium"
                                    >
                                        Expirada
                                    </span>
                                    <span
                                        v-if="card.is_default"
                                        class="px-3 py-1 bg-blue-500 text-white rounded-full font-medium"
                                    >
                                        ⭐ Predeterminada
                                    </span>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <button
                                    v-if="!card.is_default && !card.is_expired"
                                    @click="setDefaultCard(card.id)"
                                    class="px-3 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                    title="Marcar como predeterminada"
                                >
                                    ⭐
                                </button>
                                <button
                                    @click="deleteCard(card.id)"
                                    class="px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Eliminar tarjeta"
                                >
                                    🗑️
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección de Transacciones -->
            <div>
                <h2 class="text-2xl font-bold text-gray-800 mb-6">
                    📊 Transacciones Recientes
                </h2>

                <div v-if="loadingTransactions" class="text-center py-8">
                    <div
                        class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"
                    ></div>
                </div>

                <div
                    v-else-if="transactions.length === 0"
                    class="text-center py-12 bg-gray-50 rounded-lg"
                >
                    <p class="text-gray-500">No hay transacciones aún</p>
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="tx in transactions"
                        :key="tx.id"
                        class="bg-white rounded-lg shadow p-4 hover:shadow-md transition-shadow"
                    >
                        <div class="flex justify-between items-start">
                            <div class="flex items-start gap-3">
                                <span class="text-2xl">{{
                                    getTransactionIcon(tx.type)
                                }}</span>
                                <div>
                                    <p class="font-semibold text-gray-800">
                                        {{ getTransactionLabel(tx.type) }}
                                    </p>
                                    <p
                                        v-if="tx.description"
                                        class="text-sm text-gray-600"
                                    >
                                        {{ tx.description }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ formatDate(tx.created_at) }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p
                                    class="font-bold"
                                    :class="{
                                        'text-green-600':
                                            tx.type === 'recharge' ||
                                            tx.type === 'refund',
                                        'text-red-600':
                                            tx.type === 'purchase' ||
                                            tx.type === 'adjustment',
                                    }"
                                >
                                    {{
                                        tx.type === "recharge" ||
                                        tx.type === "refund"
                                            ? "+"
                                            : "-"
                                    }}${{ formatMoney(tx.amount) }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    Saldo: ${{ formatMoney(tx.balance_after) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <button
                    v-if="transactions.length > 0"
                    @click="loadMoreTransactions"
                    class="w-full mt-4 py-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors font-medium"
                >
                    Ver todas las transacciones →
                </button>
            </div>
        </div>

        <!-- Modal: Agregar Tarjeta -->
        <BaseModal v-model:open="showAddCardModal">
            <template #title>💳 Agregar Nueva Tarjeta</template>

            <form @submit.prevent="submitCard" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Tipo de tarjeta *</label
                    >
                    <select
                        v-model="cardForm.card_type"
                        required
                        class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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
                        v-model="cardForm.holder_name"
                        type="text"
                        required
                        placeholder="Como aparece en la tarjeta"
                        class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Número de tarjeta *</label
                    >
                    <input
                        v-model="cardForm.card_number"
                        type="text"
                        required
                        maxlength="16"
                        placeholder="1234567890123456"
                        @input="validateNumericInput"
                        class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono"
                    />
                    <p class="text-xs text-gray-500 mt-1">16 dígitos sin espacios</p>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Mes *</label
                        >
                        <input
                            v-model="cardForm.exp_month"
                            type="text"
                            required
                            maxlength="2"
                            placeholder="MM"
                            @input="validateNumericInput"
                            class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Año *</label
                        >
                        <input
                            v-model="cardForm.exp_year"
                            type="text"
                            required
                            maxlength="4"
                            placeholder="AAAA"
                            @input="validateNumericInput"
                            class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >CVV *</label
                        >
                        <input
                            v-model="cardForm.cvv"
                            type="text"
                            required
                            maxlength="4"
                            placeholder="123"
                            @input="validateNumericInput"
                            class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input
                        v-model="cardForm.is_default"
                        type="checkbox"
                        id="is_default"
                        class="w-4 h-4"
                    />
                    <label for="is_default" class="text-sm text-gray-700"
                        >Establecer como tarjeta predeterminada</label
                    >
                </div>

                <p v-if="cardErrors.length > 0" class="text-red-600 text-sm bg-red-50 p-3 rounded-lg">
                    <span v-for="(error, index) in cardErrors" :key="index">
                        {{ error }}<br/>
                    </span>
                </p>

                <div class="flex gap-3 justify-end pt-4">
                    <button
                        type="button"
                        @click="showAddCardModal = false"
                        class="px-6 py-2 rounded-lg border hover:bg-gray-50 transition-colors"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        :disabled="submittingCard"
                        class="px-6 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50"
                    >
                        {{ submittingCard ? "Guardando..." : "Guardar tarjeta" }}
                    </button>
                </div>
            </form>
        </BaseModal>

        <!-- Modal: Recargar Saldo -->
        <BaseModal v-model:open="showRechargeModal">
            <template #title>💰 Recargar Saldo</template>

            <form @submit.prevent="submitRecharge" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Monto a recargar *</label
                    >
                    <div class="relative">
                        <span
                            class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-medium"
                            >$</span
                        >
                        <input
                            v-model="rechargeForm.amount"
                            type="text"
                            required
                            placeholder="100000"
                            @input="formatRechargeAmount"
                            class="w-full pl-8 pr-4 py-3 rounded-lg border focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg font-semibold"
                        />
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        Mínimo: $10,000 - Máximo: $10,000,000 COP
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Método de pago *</label
                    >
                    <select
                        v-model="rechargeForm.payment_method"
                        required
                        class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Seleccionar...</option>
                        <option value="credit_card">Tarjeta de Crédito</option>
                        <option value="debit_card">Tarjeta de Débito</option>
                        <option value="bank_transfer">Transferencia Bancaria</option>
                        <option value="cash">Efectivo (PSE)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Descripción (opcional)</label
                    >
                    <textarea
                        v-model="rechargeForm.description"
                        rows="3"
                        placeholder="Ej: Recarga para compra de tiquetes"
                        class="w-full px-4 py-2 rounded-lg border focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    ></textarea>
                </div>

                <div class="bg-blue-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-700 mb-2">
                        <strong>Simulación de pago:</strong>
                    </p>
                    <p class="text-xs text-gray-600">
                        Este es un sistema simulado. No se realizarán cargos reales a tu tarjeta o cuenta bancaria.
                    </p>
                </div>

                <p v-if="rechargeError" class="text-red-600 text-sm bg-red-50 p-3 rounded-lg">
                    {{ rechargeError }}
                </p>

                <div class="flex gap-3 justify-end pt-4">
                    <button
                        type="button"
                        @click="showRechargeModal = false"
                        class="px-6 py-2 rounded-lg border hover:bg-gray-50 transition-colors"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        :disabled="submittingRecharge"
                        class="px-6 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50"
                    >
                        {{
                            submittingRecharge
                                ? "Procesando..."
                                : "Recargar saldo"
                        }}
                    </button>
                </div>
            </form>
        </BaseModal>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { api } from "../../lib/api";
import { useAuth } from "../../stores/auth";
import { useSweetAlert } from "../../composables/useSweetAlert";
import BaseModal from "../../components/ui/BaseModal.vue";

const auth = useAuth();
const { success, error: showError, confirm } = useSweetAlert();

// Estado
const walletBalance = ref(0);
const cards = ref([]);
const transactions = ref([]);
const loadingCards = ref(false);
const loadingTransactions = ref(false);

// Modales
const showAddCardModal = ref(false);
const showRechargeModal = ref(false);

// Formulario de tarjeta
const cardForm = ref({
    card_type: "",
    holder_name: "",
    card_number: "",
    exp_month: "",
    exp_year: "",
    cvv: "",
    is_default: false,
});
const cardErrors = ref([]);
const submittingCard = ref(false);

// Formulario de recarga
const rechargeForm = ref({
    amount: "",
    payment_method: "",
    description: "",
});
const rechargeError = ref("");
const submittingRecharge = ref(false);

// Cargar datos iniciales
onMounted(async () => {
    await Promise.all([loadCards(), loadWalletData()]);
});

// Cargar tarjetas
async function loadCards() {
    loadingCards.value = true;
    try {
        const { data } = await api.get("/payment-methods", {
            headers: { Authorization: `Bearer ${auth.token}` },
        });
        cards.value = data.cards;
    } catch (err) {
        console.error("Error al cargar tarjetas:", err);
    } finally {
        loadingCards.value = false;
    }
}

// Cargar datos del wallet
async function loadWalletData() {
    loadingTransactions.value = true;
    try {
        const { data } = await api.get("/wallet", {
            headers: { Authorization: `Bearer ${auth.token}` },
        });
        walletBalance.value = parseFloat(data.balance);
        transactions.value = data.transactions.data || [];
    } catch (err) {
        console.error("Error al cargar wallet:", err);
    } finally {
        loadingTransactions.value = false;
    }
}

// Abrir modal de agregar tarjeta
function openAddCardModal() {
    cardForm.value = {
        card_type: "",
        holder_name: "",
        card_number: "",
        exp_month: "",
        exp_year: "",
        cvv: "",
        is_default: false,
    };
    cardErrors.value = [];
    showAddCardModal.value = true;
}

// Validar solo números
function validateNumericInput(event) {
    event.target.value = event.target.value.replace(/\D/g, "");
}

// Enviar formulario de tarjeta
async function submitCard() {
    submittingCard.value = true;
    cardErrors.value = [];

    try {
        await api.post("/payment-methods", cardForm.value, {
            headers: { Authorization: `Bearer ${auth.token}` },
        });

        showAddCardModal.value = false;
        await success("Tarjeta agregada", "Tu tarjeta ha sido guardada exitosamente");
        await loadCards();
    } catch (err) {
        const errors = err.response?.data?.errors;
        if (errors) {
            cardErrors.value = Object.values(errors).flat();
        } else {
            cardErrors.value = [err.response?.data?.message || "Error al guardar la tarjeta"];
        }
    } finally {
        submittingCard.value = false;
    }
}

// Establecer tarjeta como predeterminada
async function setDefaultCard(cardId) {
    try {
        await api.post(`/payment-methods/${cardId}/set-default`, {}, {
            headers: { Authorization: `Bearer ${auth.token}` },
        });
        await success("Tarjeta actualizada", "La tarjeta ha sido establecida como predeterminada");
        await loadCards();
    } catch (err) {
        showError("Error", err.response?.data?.error || "Error al actualizar la tarjeta");
    }
}

// Eliminar tarjeta
async function deleteCard(cardId) {
    const confirmed = await confirm(
        "¿Eliminar tarjeta?",
        "¿Estás seguro de que deseas eliminar esta tarjeta?",
        "Sí, eliminar",
        "Cancelar"
    );

    if (!confirmed) return;

    try {
        await api.delete(`/payment-methods/${cardId}`, {
            headers: { Authorization: `Bearer ${auth.token}` },
        });
        await success("Tarjeta eliminada", "La tarjeta ha sido eliminada exitosamente");
        await loadCards();
    } catch (err) {
        showError("Error", err.response?.data?.error || "Error al eliminar la tarjeta");
    }
}

// Abrir modal de recarga
function openRechargeModal() {
    rechargeForm.value = {
        amount: "",
        payment_method: "",
        description: "",
    };
    rechargeError.value = "";
    showRechargeModal.value = true;
}

// Formatear monto de recarga
function formatRechargeAmount(event) {
    // Solo números
    let value = event.target.value.replace(/\D/g, "");
    rechargeForm.value.amount = value;
}

// Enviar formulario de recarga
async function submitRecharge() {
    submittingRecharge.value = true;
    rechargeError.value = "";

    try {
        const payload = {
            ...rechargeForm.value,
            amount: parseInt(rechargeForm.value.amount),
        };

        await api.post("/wallet/recharge", payload, {
            headers: { Authorization: `Bearer ${auth.token}` },
        });

        showRechargeModal.value = false;
        await success(
            "Recarga exitosa",
            `Se han agregado $${formatMoney(payload.amount)} a tu saldo`
        );
        await loadWalletData();
    } catch (err) {
        const errors = err.response?.data?.errors;
        if (errors) {
            rechargeError.value = Object.values(errors).flat().join(". ");
        } else {
            rechargeError.value = err.response?.data?.message || "Error al procesar la recarga";
        }
    } finally {
        submittingRecharge.value = false;
    }
}

// Helpers
function formatMoney(amount) {
    return Number(amount).toLocaleString("es-CO");
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleString("es-CO", {
        dateStyle: "medium",
        timeStyle: "short",
    });
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

function getTransactionIcon(type) {
    const icons = {
        recharge: "💰",
        purchase: "✈️",
        refund: "↩️",
        adjustment: "⚙️",
    };
    return icons[type] || "📝";
}

function getTransactionLabel(type) {
    const labels = {
        recharge: "Recarga de saldo",
        purchase: "Compra de tiquetes",
        refund: "Reembolso",
        adjustment: "Ajuste",
    };
    return labels[type] || type;
}

function loadMoreTransactions() {
    // TODO: Implementar paginación completa
    window.location.href = "/client/wallet/transactions";
}
</script>
