<template>
  <div class="space-y-6">
    <!-- Wallet Balance Card -->
    <div class="bg-white shadow rounded-lg">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h2 class="text-lg font-semibold text-gray-900">Saldo de Billetera</h2>
            <p class="mt-1 text-sm text-gray-600">Gestiona tu saldo y realiza recargas</p>
          </div>
          <div class="text-left md:text-right">
            <p class="text-xl md:text-2xl font-bold text-indigo-600 break-words">{{ formatMoney(walletBalance) }}</p>
            <p class="text-sm text-gray-500">Saldo disponible</p>
          </div>
        </div>
      </div>
      
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Total Income Card -->
          <div class="bg-green-50 rounded-lg p-4">
            <div class="flex items-start gap-3">
              <div class="flex-shrink-0">
                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-green-900">Ingresos Totales</p>
                <p class="text-base md:text-lg font-semibold text-green-700 break-words">{{ formatMoney(statistics.totalIncome) }}</p>
              </div>
            </div>
          </div>

          <!-- Total Expenses Card -->
          <div class="bg-red-50 rounded-lg p-4">
            <div class="flex items-start gap-3">
              <div class="flex-shrink-0">
                <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                </svg>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-red-900">Gastos Totales</p>
                <p class="text-base md:text-lg font-semibold text-red-700 break-words">{{ formatMoney(statistics.totalExpenses) }}</p>
              </div>
            </div>
          </div>

          <!-- Total Transactions Card -->
          <div class="bg-blue-50 rounded-lg p-4">
            <div class="flex items-start gap-3">
              <div class="flex-shrink-0">
                <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-blue-900">Transacciones</p>
                <p class="text-lg md:text-xl font-bold text-blue-700">{{ statistics.totalTransactions }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Recharge Button -->
        <div class="mt-6 flex justify-end">
          <button
            @click="showRechargeModal = true"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Recargar Saldo
          </button>
        </div>
      </div>
    </div>

    <!-- Payment Methods Section -->
    <div class="bg-white shadow rounded-lg">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-lg font-semibold text-gray-900">Métodos de Pago</h2>
            <p class="mt-1 text-sm text-gray-600">Administra tus tarjetas guardadas</p>
          </div>
          <button
            @click="showCardModal = true"
            class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Agregar Tarjeta
          </button>
        </div>
      </div>

      <div class="p-6">
        <!-- Cards List -->
        <div v-if="cards.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div
            v-for="card in cards"
            :key="card.id"
            class="border-2 rounded-lg p-4 hover:border-indigo-500 transition-colors"
            :class="card.is_default ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200'"
          >
            <div class="flex items-start justify-between gap-3">
              <div class="flex items-start gap-3 flex-1 min-w-0">
                <!-- Card Icon -->
                <div class="flex-shrink-0">
                  <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                  </svg>
                </div>
                
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 flex-wrap">
                    <p class="text-sm font-medium text-gray-900 uppercase">{{ card.card_type || 'Tarjeta' }}</p>
                    <span v-if="card.is_default" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800 whitespace-nowrap">
                      Predeterminada
                    </span>
                  </div>
                  <p class="text-sm text-gray-600 font-mono">•••• •••• •••• {{ card.last_four }}</p>
                  <p class="text-xs text-gray-500">Vence: {{ card.expiry_month }}/{{ card.expiry_year }}</p>
                </div>
              </div>

              <!-- Card Actions -->
              <div class="flex items-center gap-2 flex-shrink-0">
                <button
                  v-if="!card.is_default"
                  @click="setDefaultCard(card.id)"
                  class="text-indigo-600 hover:text-indigo-700"
                  title="Establecer como predeterminada"
                >
                  <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </button>
                
                <button
                  @click="deleteCard(card.id)"
                  class="text-red-600 hover:text-red-700"
                  title="Eliminar tarjeta"
                >
                  <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-8">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
          </svg>
          <p class="mt-2 text-sm text-gray-600">No tienes tarjetas guardadas</p>
          <button
            @click="showCardModal = true"
            class="mt-4 inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
          >
            Agregar primera tarjeta
          </button>
        </div>
      </div>
    </div>

    <!-- Transaction History -->
    <div class="bg-white shadow rounded-lg">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Historial de Transacciones</h2>
        <p class="mt-1 text-sm text-gray-600">Últimas operaciones en tu billetera</p>
      </div>

      <div class="p-6">
        <!-- Transaction Filters -->
        <div class="mb-4 flex flex-wrap gap-2">
          <button
            v-for="type in ['all', 'recharge', 'payment', 'refund', 'bonus']"
            :key="type"
            @click="filterType = type"
            :class="filterType === type ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
            class="px-3 py-1 rounded-md text-sm font-medium transition-colors"
          >
            {{ getFilterLabel(type) }}
          </button>
        </div>

        <!-- Transactions List -->
        <div v-if="filteredTransactions.length > 0" class="space-y-3">
          <div
            v-for="transaction in filteredTransactions"
            :key="transaction.id"
            class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50 transition-colors"
          >
            <div class="flex items-center gap-4 flex-1 min-w-0">
              <div
                class="flex-shrink-0 h-10 w-10 rounded-full flex items-center justify-center"
                :class="getTransactionIconClass(transaction.type)"
              >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path v-if="transaction.type === 'recharge' || transaction.type === 'refund' || transaction.type === 'bonus'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                </svg>
              </div>
              
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ transaction.description }}</p>
                <p class="text-xs text-gray-500">{{ formatDate(transaction.created_at) }}</p>
              </div>
            </div>

            <div class="text-right flex-shrink-0 ml-4">
              <p
                class="text-sm font-bold whitespace-nowrap"
                :class="['payment', 'purchase', 'adjustment'].includes(transaction.type) ? 'text-red-600' : 'text-green-600'"
              >
                {{ ['payment', 'purchase', 'adjustment'].includes(transaction.type) ? '-' : '+' }}{{ formatMoney(transaction.amount) }}
              </p>
              <span
                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium whitespace-nowrap"
                :class="getTransactionTypeClass(transaction.type)"
              >
                {{ getTransactionTypeLabel(transaction.type) }}
              </span>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-8">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
          <p class="mt-2 text-sm text-gray-600">No hay transacciones</p>
        </div>

        <!-- Load More Button -->
        <div v-if="hasMoreTransactions" class="mt-4 text-center">
          <button
            @click="loadMoreTransactions"
            :disabled="loadingTransactions"
            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
          >
            <svg v-if="loadingTransactions" class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ loadingTransactions ? 'Cargando...' : 'Cargar más' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Add Card Modal -->
    <BaseModal :show="showCardModal" @close="showCardModal = false" title="Agregar Tarjeta">
      <form @submit.prevent="submitCard" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Número de Tarjeta</label>
          <input
            v-model="cardForm.card_number"
            type="text"
            maxlength="19"
            placeholder="1234 5678 9012 3456"
            required
            @input="formatCardNumber"
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Nombre del Titular</label>
          <input
            v-model="cardForm.card_holder_name"
            type="text"
            placeholder="NOMBRE APELLIDO"
            required
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Tipo de Tarjeta</label>
          <select
            v-model="cardForm.card_type"
            required
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option value="">Selecciona el tipo</option>
            <option value="credit">Crédito</option>
            <option value="debit">Débito</option>
          </select>
        </div>

        <div class="grid grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Mes</label>
            <select
              v-model="cardForm.expiry_month"
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option value="">MM</option>
              <option v-for="month in 12" :key="month" :value="String(month).padStart(2, '0')">
                {{ String(month).padStart(2, '0') }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Año</label>
            <select
              v-model="cardForm.expiry_year"
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option value="">AAAA</option>
              <option v-for="year in 10" :key="year" :value="new Date().getFullYear() + year - 1">
                {{ new Date().getFullYear() + year - 1 }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">CVV</label>
            <input
              v-model="cardForm.cvv"
              type="text"
              maxlength="4"
              placeholder="123"
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
            />
          </div>
        </div>

        <div class="flex items-center">
          <input
            v-model="cardForm.is_default"
            type="checkbox"
            id="is_default"
            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
          />
          <label for="is_default" class="ml-2 block text-sm text-gray-700">
            Establecer como tarjeta predeterminada
          </label>
        </div>

        <div class="flex justify-end space-x-3 pt-4">
          <button
            type="button"
            @click="showCardModal = false"
            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="loadingCard"
            class="inline-flex justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
          >
            <svg v-if="loadingCard" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ loadingCard ? 'Guardando...' : 'Guardar Tarjeta' }}
          </button>
        </div>
      </form>
    </BaseModal>

    <!-- Recharge Modal -->
    <BaseModal :show="showRechargeModal" @close="showRechargeModal = false" title="Recargar Saldo">
      <form @submit.prevent="submitRecharge" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Monto a Recargar</label>
          <div class="mt-1 relative rounded-md shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <span class="text-gray-500 sm:text-sm">$</span>
            </div>
            <input
              v-model="rechargeForm.amount"
              type="number"
              step="1000"
              min="1000"
              placeholder="0"
              required
              class="block w-full pl-7 pr-12 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
            />
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
              <span class="text-gray-500 sm:text-sm">COP</span>
            </div>
          </div>
          <p class="mt-1 text-xs text-gray-500">Monto mínimo: $1,000 COP - Máximo: $100,000,000 COP</p>
        </div>

        <!-- Quick amounts -->
        <div class="grid grid-cols-4 gap-2">
          <button
            v-for="amount in [10000, 50000, 100000, 500000]"
            :key="amount"
            type="button"
            @click="rechargeForm.amount = amount"
            class="px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            ${{ amount.toLocaleString('es-CO') }}
          </button>
        </div>

        <div v-if="cards.length > 0">
          <label class="block text-sm font-medium text-gray-700">Método de Pago</label>
          <select
            v-model="rechargeForm.card_id"
            required
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option value="">Seleccionar tarjeta</option>
            <option v-for="card in cards" :key="card.id" :value="card.id">
              {{ card.card_type || 'Tarjeta' }} •••• {{ card.last_four }}
            </option>
          </select>
        </div>

        <div v-else class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
          <div class="flex">
            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <p class="ml-3 text-sm text-yellow-700">
              Necesitas agregar una tarjeta para recargar saldo.
            </p>
          </div>
        </div>

        <div class="flex justify-end space-x-3 pt-4">
          <button
            type="button"
            @click="showRechargeModal = false"
            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="loadingRecharge || cards.length === 0"
            class="inline-flex justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
          >
            <svg v-if="loadingRecharge" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ loadingRecharge ? 'Procesando...' : 'Recargar' }}
          </button>
        </div>
      </form>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { api } from '../../lib/api'
import { useAuth } from '../../stores/auth'
import BaseModal from '../ui/BaseModal.vue'
import Swal from 'sweetalert2'

const auth = useAuth()

// State
const cards = ref([])
const transactions = ref([])
const statistics = ref({
  totalIncome: 0,
  totalExpenses: 0,
  totalTransactions: 0
})
const walletBalance = ref(0)
const filterType = ref('all')
const currentPage = ref(1)
const hasMoreTransactions = ref(false)

// Modals
const showCardModal = ref(false)
const showRechargeModal = ref(false)

// Loading states
const loadingCard = ref(false)
const loadingRecharge = ref(false)
const loadingTransactions = ref(false)

// Forms
const cardForm = ref({
  card_number: '',
  card_holder_name: '',
  card_type: '',
  expiry_month: '',
  expiry_year: '',
  cvv: '',
  is_default: false
})

const rechargeForm = ref({
  amount: '',
  card_id: ''
})

// Computed
const filteredTransactions = computed(() => {
  if (filterType.value === 'all') return transactions.value
  return transactions.value.filter(t => t.type === filterType.value)
})

// Methods
const loadCards = async () => {
  try {
    const { data } = await api.get('/payment-methods')
    cards.value = data.cards || []
  } catch (error) {
    console.error('Error loading cards:', error)
  }
}

const loadWalletData = async () => {
  try {
    const { data } = await api.get('/wallet')
    walletBalance.value = parseFloat(data.balance) || 0
  } catch (error) {
    console.error('Error loading wallet:', error)
  }
}

const loadStatistics = async () => {
  try {
    const { data } = await api.get('/wallet/statistics')
    statistics.value = data
  } catch (error) {
    console.error('Error loading statistics:', error)
  }
}

const loadTransactions = async (page = 1) => {
  loadingTransactions.value = true
  try {
    const { data } = await api.get(`/wallet/transactions?page=${page}`)
    if (page === 1) {
      transactions.value = data.data || []
    } else {
      transactions.value.push(...(data.data || []))
    }
    currentPage.value = page
    hasMoreTransactions.value = data.next_page_url !== null
  } catch (error) {
    console.error('Error loading transactions:', error)
  } finally {
    loadingTransactions.value = false
  }
}

const loadMoreTransactions = () => {
  loadTransactions(currentPage.value + 1)
}

const submitCard = async () => {
  loadingCard.value = true
  try {
    await api.post('/payment-methods', {
      ...cardForm.value,
      card_number: cardForm.value.card_number.replace(/\s/g, '')
    })
    
    await Swal.fire({
      icon: 'success',
      title: 'Tarjeta agregada',
      text: 'La tarjeta se ha guardado exitosamente',
      timer: 2000,
      showConfirmButton: false
    })
    
    showCardModal.value = false
    resetCardForm()
    await loadCards()
  } catch (error) {
    await Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.response?.data?.message || 'No se pudo guardar la tarjeta'
    })
  } finally {
    loadingCard.value = false
  }
}

const submitRecharge = async () => {
  loadingRecharge.value = true
  try {
    await api.post('/wallet/recharge', rechargeForm.value)
    
    await Swal.fire({
      icon: 'success',
      title: 'Recarga exitosa',
      text: `Se ha recargado ${formatMoney(rechargeForm.value.amount)} a tu billetera`,
      timer: 2000,
      showConfirmButton: false
    })
    
    showRechargeModal.value = false
    resetRechargeForm()
    await Promise.all([loadWalletData(), loadStatistics(), loadTransactions(1)])
  } catch (error) {
    await Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.response?.data?.message || 'No se pudo realizar la recarga'
    })
  } finally {
    loadingRecharge.value = false
  }
}

const setDefaultCard = async (cardId) => {
  try {
    await api.post(`/payment-methods/${cardId}/set-default`)
    
    await Swal.fire({
      icon: 'success',
      title: 'Tarjeta actualizada',
      text: 'La tarjeta se estableció como predeterminada',
      timer: 2000,
      showConfirmButton: false
    })
    
    await loadCards()
  } catch (error) {
    await Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.response?.data?.message || 'No se pudo actualizar la tarjeta'
    })
  }
}

const deleteCard = async (cardId) => {
  const result = await Swal.fire({
    title: '¿Eliminar tarjeta?',
    text: 'Esta acción no se puede deshacer',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  })

  if (result.isConfirmed) {
    try {
      await api.delete(`/payment-methods/${cardId}`)
      
      await Swal.fire({
        icon: 'success',
        title: 'Tarjeta eliminada',
        text: 'La tarjeta se ha eliminado exitosamente',
        timer: 2000,
        showConfirmButton: false
      })
      
      await loadCards()
    } catch (error) {
      await Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.message || 'No se pudo eliminar la tarjeta'
      })
    }
  }
}

const formatCardNumber = (event) => {
  let value = event.target.value.replace(/\s/g, '')
  let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value
  cardForm.value.card_number = formattedValue
}

const resetCardForm = () => {
  cardForm.value = {
    card_number: '',
    card_holder_name: '',
    card_type: '',
    expiry_month: '',
    expiry_year: '',
    cvv: '',
    is_default: false
  }
}

const resetRechargeForm = () => {
  rechargeForm.value = {
    amount: '',
    card_id: ''
  }
}

// Utility functions
const formatMoney = (amount) => {
  if (!amount && amount !== 0) return '$0'
  // Formato colombiano: $1.234.567 (punto como separador de miles)
  const formatted = new Intl.NumberFormat('es-CO', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount)
  return '$' + formatted
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getFilterLabel = (type) => {
  const labels = {
    all: 'Todas',
    recharge: 'Recargas',
    payment: 'Pagos',
    refund: 'Reembolsos',
    bonus: 'Bonificaciones'
  }
  return labels[type] || type
}

const getTransactionTypeLabel = (type) => {
  const labels = {
    recharge: 'Recarga',
    payment: 'Pago',
    purchase: 'Compra',
    refund: 'Reembolso',
    bonus: 'Bonificación',
    adjustment: 'Ajuste'
  }
  return labels[type] || type
}

const getTransactionTypeClass = (type) => {
  const classes = {
    recharge: 'bg-blue-100 text-blue-800',
    payment: 'bg-red-100 text-red-800',
    purchase: 'bg-orange-100 text-orange-800',
    refund: 'bg-green-100 text-green-800',
    bonus: 'bg-purple-100 text-purple-800',
    adjustment: 'bg-yellow-100 text-yellow-800'
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

const getTransactionIconClass = (type) => {
  const classes = {
    recharge: 'bg-blue-100 text-blue-600',
    payment: 'bg-red-100 text-red-600',
    purchase: 'bg-orange-100 text-orange-600',
    refund: 'bg-green-100 text-green-600',
    bonus: 'bg-purple-100 text-purple-600',
    adjustment: 'bg-yellow-100 text-yellow-600'
  }
  return classes[type] || 'bg-gray-100 text-gray-600'
}

// Initialize
onMounted(async () => {
  await Promise.all([
    loadCards(),
    loadWalletData(),
    loadStatistics(),
    loadTransactions(1)
  ])
})
</script>
