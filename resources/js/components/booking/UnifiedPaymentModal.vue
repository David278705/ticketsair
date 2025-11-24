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
                            class="w-full max-w-3xl transform overflow-hidden rounded-2xl bg-white shadow-xl transition-all"
                        >
                            <div class="max-h-[90vh] overflow-y-auto">
                                <!-- Header -->
                                <div class="bg-gradient-to-r from-blue-600 to-cyan-500 p-6 text-white">
                                    <DialogTitle class="text-2xl font-bold mb-1">
                                        Finalizar Pago
                                    </DialogTitle>
                                    <p class="text-blue-50 text-sm">
                                        Revisa los detalles de tu {{ bookingInfo?.action === 'purchase' ? 'compra' : 'reserva' }} antes de continuar
                                    </p>
                                </div>

                                <div class="p-6 space-y-6">
                                    <!-- Información del Vuelo -->
                                    <div v-if="bookingInfo?.flight" class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-xl p-5 border-2 border-blue-200">
                                        <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                            </svg>
                                            Detalles del Vuelo
                                        </h3>
                                        <div class="grid grid-cols-2 gap-3 text-sm">
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Código:</span>
                                                <span class="font-semibold text-blue-900">{{ bookingInfo.flight.code }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Ruta:</span>
                                                <span class="font-semibold">{{ bookingInfo.flight.origin?.code }} → {{ bookingInfo.flight.destination?.code }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Clase:</span>
                                                <span class="font-semibold capitalize">{{ bookingInfo.class === 'economy' ? 'Económica' : 'Primera Clase' }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Pasajeros:</span>
                                                <span class="font-semibold">{{ bookingInfo.passengers_count }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Resumen de Precio -->
                                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                                        <div class="flex justify-between items-center mb-3">
                                            <h3 class="font-bold text-gray-900">Total a Pagar</h3>
                                            <span class="text-3xl font-bold text-blue-600">${{ formatMoney(totalAmount) }}</span>
                                        </div>
                                        <div v-if="walletBalance > 0" class="text-sm text-gray-600 flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            Saldo disponible en billetera: <span class="font-semibold text-green-600">${{ formatMoney(walletBalance) }}</span>
                                        </div>
                                    </div>

                                    <!-- Selección de Método de Pago -->
                                    <div class="space-y-4">
                                        <h3 class="font-bold text-gray-900">Método de Pago</h3>

                                        <!-- Opción: Pagar con Billetera -->
                                        <div 
                                            v-if="walletBalance >= totalAmount"
                                            @click="paymentMethod = 'wallet'"
                                            class="border-2 rounded-xl p-4 cursor-pointer transition-all"
                                            :class="paymentMethod === 'wallet' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300'"
                                        >
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <p class="font-semibold text-gray-900">Billetera Digital</p>
                                                        <p class="text-sm text-gray-600">Saldo: ${{ formatMoney(walletBalance) }}</p>
                                                    </div>
                                                </div>
                                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center" :class="paymentMethod === 'wallet' ? 'border-blue-500 bg-blue-500' : 'border-gray-300'">
                                                    <svg v-if="paymentMethod === 'wallet'" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Mensaje si no hay saldo suficiente -->
                                        <div v-else-if="walletBalance > 0" class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                                            <div class="flex items-start gap-3">
                                                <svg class="w-5 h-5 text-amber-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                                <div class="flex-1">
                                                    <p class="text-sm font-medium text-amber-900">Saldo insuficiente</p>
                                                    <p class="text-xs text-amber-700 mt-1">
                                                        Necesitas ${{ formatMoney(totalAmount - walletBalance) }} adicionales. Recarga tu billetera desde tu perfil o paga con tarjeta.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Opción: Tarjetas Guardadas -->
                                        <div v-if="savedCards.length > 0" class="space-y-3">
                                            <p class="text-sm font-medium text-gray-700">Tarjetas Guardadas</p>
                                            <div
                                                v-for="card in savedCards"
                                                :key="card.id"
                                                @click="selectSavedCard(card)"
                                                class="border-2 rounded-xl p-4 cursor-pointer transition-all"
                                                :class="paymentMethod === 'saved_card' && selectedCardId === card.id ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300'"
                                            >
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-gray-700 to-gray-900 flex items-center justify-center">
                                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <div class="flex items-center gap-2">
                                                                <p class="font-semibold text-gray-900">{{ card.card_type?.toUpperCase() || 'Tarjeta' }} •••• {{ card.last4 }}</p>
                                                                <span v-if="card.is_default" class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded">Predeterminada</span>
                                                            </div>
                                                            <p class="text-sm text-gray-600">Vence: {{ card.exp_month }}/{{ card.exp_year }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center" :class="paymentMethod === 'saved_card' && selectedCardId === card.id ? 'border-blue-500 bg-blue-500' : 'border-gray-300'">
                                                        <svg v-if="paymentMethod === 'saved_card' && selectedCardId === card.id" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Opción: Nueva Tarjeta -->
                                        <div
                                            @click="paymentMethod = 'new_card'"
                                            class="border-2 rounded-xl p-4 cursor-pointer transition-all"
                                            :class="paymentMethod === 'new_card' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300'"
                                        >
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-12 h-12 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center">
                                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <p class="font-semibold text-gray-900">Agregar Nueva Tarjeta</p>
                                                        <p class="text-sm text-gray-600">Paga con una tarjeta diferente</p>
                                                    </div>
                                                </div>
                                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center" :class="paymentMethod === 'new_card' ? 'border-blue-500 bg-blue-500' : 'border-gray-300'">
                                                    <svg v-if="paymentMethod === 'new_card'" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Formulario de Nueva Tarjeta -->
                                    <div v-if="paymentMethod === 'new_card'" class="bg-white rounded-xl p-5 border-2 border-blue-200 space-y-4">
                                        <h4 class="font-semibold text-gray-900">Datos de la Tarjeta</h4>
                                        
                                        <div>
                                            <label class="block text-sm font-medium mb-1.5">Número de Tarjeta</label>
                                            <input
                                                v-model="newCard.card_number"
                                                @input="formatCardNumber"
                                                type="text"
                                                placeholder="1234 5678 9012 3456"
                                                maxlength="19"
                                                class="w-full h-12 rounded-xl border border-gray-300 px-4 focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                                :class="{ 'border-red-500': errors.card_number }"
                                            />
                                            <p v-if="errors.card_number" class="text-xs text-red-600 mt-1">{{ errors.card_number }}</p>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium mb-1.5">Titular de la Tarjeta</label>
                                            <input
                                                v-model="newCard.card_holder_name"
                                                @input="newCard.card_holder_name = newCard.card_holder_name.toUpperCase()"
                                                type="text"
                                                placeholder="NOMBRE COMPLETO"
                                                class="w-full h-12 rounded-xl border border-gray-300 px-4 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 uppercase"
                                                :class="{ 'border-red-500': errors.card_holder_name }"
                                            />
                                            <p v-if="errors.card_holder_name" class="text-xs text-red-600 mt-1">{{ errors.card_holder_name }}</p>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium mb-1.5">Tipo de Tarjeta</label>
                                            <select
                                                v-model="newCard.card_type"
                                                class="w-full h-12 rounded-xl border border-gray-300 px-4 focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                                :class="{ 'border-red-500': errors.card_type }"
                                            >
                                                <option value="">Selecciona el tipo</option>
                                                <option value="credit">Crédito</option>
                                                <option value="debit">Débito</option>
                                            </select>
                                            <p v-if="errors.card_type" class="text-xs text-red-600 mt-1">{{ errors.card_type }}</p>
                                        </div>

                                        <div class="grid grid-cols-3 gap-3">
                                            <div>
                                                <label class="block text-sm font-medium mb-1.5">Mes</label>
                                                <select
                                                    v-model="newCard.expiry_month"
                                                    class="w-full h-12 rounded-xl border border-gray-300 px-4 focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                                    :class="{ 'border-red-500': errors.expiry_month }"
                                                >
                                                    <option value="">MM</option>
                                                    <option v-for="month in 12" :key="month" :value="String(month).padStart(2, '0')">
                                                        {{ String(month).padStart(2, '0') }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium mb-1.5">Año</label>
                                                <select
                                                    v-model="newCard.expiry_year"
                                                    class="w-full h-12 rounded-xl border border-gray-300 px-4 focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                                    :class="{ 'border-red-500': errors.expiry_year }"
                                                >
                                                    <option value="">AAAA</option>
                                                    <option v-for="year in 10" :key="year" :value="new Date().getFullYear() + year - 1">
                                                        {{ new Date().getFullYear() + year - 1 }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium mb-1.5">CVV</label>
                                                <input
                                                    v-model="newCard.cvv"
                                                    type="text"
                                                    placeholder="123"
                                                    maxlength="4"
                                                    class="w-full h-12 rounded-xl border border-gray-300 px-4 focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                                    :class="{ 'border-red-500': errors.cvv }"
                                                />
                                            </div>
                                        </div>

                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input
                                                v-model="newCard.save_card"
                                                type="checkbox"
                                                class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                            />
                                            <span class="text-sm text-gray-600">Guardar tarjeta para futuras compras</span>
                                        </label>
                                    </div>

                                    <!-- Error General -->
                                    <div v-if="generalError" class="bg-red-50 border border-red-200 rounded-xl p-4">
                                        <div class="flex gap-3">
                                            <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                            </svg>
                                            <div>
                                                <p class="text-sm font-medium text-red-800">Error al procesar el pago</p>
                                                <p class="text-sm text-red-600 mt-1">{{ generalError }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Botones -->
                                    <div class="flex justify-end gap-3 pt-4">
                                        <button
                                            @click="close"
                                            :disabled="processing"
                                            class="h-11 rounded-xl border border-gray-300 px-5 hover:bg-gray-50 transition-colors disabled:opacity-50"
                                        >
                                            Cancelar
                                        </button>
                                        <button
                                            @click="processPayment"
                                            :disabled="processing || !paymentMethod"
                                            class="h-11 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 hover:from-blue-700 hover:to-blue-800 transition-all shadow-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                        >
                                            <svg v-if="processing" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span>{{ processing ? 'Procesando...' : `Pagar $${formatMoney(totalAmount)}` }}</span>
                                        </button>
                                    </div>

                                    <!-- Seguridad -->
                                    <div class="flex items-center justify-center gap-2 text-xs text-gray-500 pt-2">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
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
import { ref, watch, onMounted } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { api } from '../../lib/api'
import { useAuth } from '../../stores/auth'
import Swal from 'sweetalert2'

const auth = useAuth()

const props = defineProps({
    open: Boolean,
    totalAmount: {
        type: Number,
        required: true
    },
    bookingInfo: {
        type: Object,
        default: null
    }
})

const emit = defineEmits(['update:open', 'payment-success'])

// State
const paymentMethod = ref('')
const selectedCardId = ref(null)
const savedCards = ref([])
const walletBalance = ref(0)
const processing = ref(false)
const errors = ref({})
const generalError = ref('')

// Nueva tarjeta
const newCard = ref({
    card_number: '',
    card_holder_name: '',
    card_type: '',
    expiry_month: '',
    expiry_year: '',
    cvv: '',
    save_card: false
})

// Watch para cargar datos cuando se abre el modal
watch(() => props.open, async (isOpen) => {
    if (isOpen) {
        resetForm()
        await loadPaymentData()
    }
})

// Cargar datos de pago
const loadPaymentData = async () => {
    try {
        // Cargar tarjetas guardadas
        const cardsResponse = await api.get('/payment-methods')
        savedCards.value = cardsResponse.data.cards || []

        // Cargar saldo de billetera
        const walletResponse = await api.get('/wallet')
        walletBalance.value = parseFloat(walletResponse.data.balance) || 0

        // Preseleccionar método de pago
        if (walletBalance.value >= props.totalAmount) {
            paymentMethod.value = 'wallet'
        } else if (savedCards.value.length > 0) {
            const defaultCard = savedCards.value.find(c => c.is_default)
            if (defaultCard) {
                selectSavedCard(defaultCard)
            }
        } else {
            paymentMethod.value = 'new_card'
        }
    } catch (error) {
        console.error('Error loading payment data:', error)
    }
}

// Seleccionar tarjeta guardada
const selectSavedCard = (card) => {
    paymentMethod.value = 'saved_card'
    selectedCardId.value = card.id
}

// Formatear número de tarjeta
const formatCardNumber = (event) => {
    let value = event.target.value.replace(/\s/g, '')
    let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value
    newCard.value.card_number = formattedValue
}

// Validar formulario de nueva tarjeta
const validateNewCard = () => {
    errors.value = {}
    const cleanNumber = newCard.value.card_number.replace(/\s/g, '')

    if (!cleanNumber) {
        errors.value.card_number = 'Número de tarjeta requerido'
    } else if (cleanNumber.length < 13 || cleanNumber.length > 19) {
        errors.value.card_number = 'Número de tarjeta inválido'
    }

    if (!newCard.value.card_holder_name.trim()) {
        errors.value.card_holder_name = 'Nombre del titular requerido'
    }

    if (!newCard.value.card_type) {
        errors.value.card_type = 'Tipo de tarjeta requerido'
    }

    if (!newCard.value.expiry_month) {
        errors.value.expiry_month = 'Mes requerido'
    }

    if (!newCard.value.expiry_year) {
        errors.value.expiry_year = 'Año requerido'
    }

    if (!newCard.value.cvv || newCard.value.cvv.length < 3) {
        errors.value.cvv = 'CVV inválido'
    }

    return Object.keys(errors.value).length === 0
}

// Procesar pago
const processPayment = async () => {
    generalError.value = ''
    processing.value = true

    try {
        let paymentData = {}

        if (paymentMethod.value === 'wallet') {
            // Verificar saldo suficiente antes de procesar
            if (walletBalance.value < props.totalAmount) {
                generalError.value = `Saldo insuficiente. Tienes ${formatMoney(walletBalance.value)} pero necesitas ${formatMoney(props.totalAmount)}`
                processing.value = false
                return
            }
            
            // Pago con billetera
            paymentData = {
                method: 'wallet',
                amount: props.totalAmount,
                currency: 'COP'
            }
        } else if (paymentMethod.value === 'saved_card') {
            // Pago con tarjeta guardada
            const card = savedCards.value.find(c => c.id === selectedCardId.value)
            if (!card) {
                generalError.value = 'Tarjeta no encontrada'
                processing.value = false
                return
            }
            paymentData = {
                method: 'saved_card',
                card_id: card.id,
                card_type: card.card_type,
                card_holder: card.card_holder_name,
                last_four: card.last4,
                expiry_date: `${card.exp_month}/${card.exp_year}`,
                transaction_id: 'TXN' + Date.now() + Math.random().toString(36).substr(2, 9).toUpperCase(),
                save_card: false
            }
        } else if (paymentMethod.value === 'new_card') {
            // Pago con nueva tarjeta
            if (!validateNewCard()) {
                processing.value = false
                return
            }

            paymentData = {
                method: 'new_card',
                card_number: newCard.value.card_number.replace(/\s/g, ''),
                card_holder: newCard.value.card_holder_name,
                card_type: newCard.value.card_type,
                expiry_date: `${newCard.value.expiry_month}/${newCard.value.expiry_year}`,
                cvv: newCard.value.cvv,
                last_four: newCard.value.card_number.replace(/\s/g, '').slice(-4),
                transaction_id: 'TXN' + Date.now() + Math.random().toString(36).substr(2, 9).toUpperCase(),
                save_card: newCard.value.save_card
            }
        }

        // Simular delay de procesamiento
        await new Promise(resolve => setTimeout(resolve, 1500))

        // Emitir evento con los datos de pago al componente padre
        emit('payment-success', paymentData)
        
        // Cerrar modal
        close()
    } catch (error) {
        // Solo capturar errores críticos del modal (no errores de backend)
        console.error('Error en el modal de pago:', error)
        generalError.value = 'Ocurrió un error inesperado. Por favor intenta nuevamente.'
    } finally {
        processing.value = false
    }
}

// Resetear formulario
const resetForm = () => {
    paymentMethod.value = ''
    selectedCardId.value = null
    newCard.value = {
        card_number: '',
        card_holder_name: '',
        card_type: '',
        expiry_month: '',
        expiry_year: '',
        cvv: '',
        save_card: false
    }
    errors.value = {}
    generalError.value = ''
    processing.value = false
}

// Cerrar modal
const close = () => {
    if (!processing.value) {
        resetForm() // Limpiar formulario y errores al cerrar
        emit('update:open', false)
    }
}

// Formatear dinero
const formatMoney = (amount) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount || 0)
}

onMounted(() => {
    if (props.open) {
        loadPaymentData()
    }
})
</script>
```
