<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
    <div class="max-w-md w-full space-y-8">
      <div class="bg-white rounded-lg shadow-lg p-8">
        <div class="text-center mb-8">
          <div class="mx-auto h-12 w-12 bg-indigo-100 rounded-full flex items-center justify-center mb-4">
            <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-gray-900">Completa tu Registro</h2>
          <p class="mt-2 text-sm text-gray-600">
            Finaliza la configuración de tu cuenta de administrador
          </p>
        </div>

        <form @submit.prevent="completeRegistration" class="space-y-6">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label for="first_name" class="block text-sm font-medium text-gray-700">
                Nombre
              </label>
              <input
                id="first_name"
                name="first_name"
                type="text"
                required
                v-model="form.first_name"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                placeholder="Tu nombre"
              />
            </div>
            <div>
              <label for="last_name" class="block text-sm font-medium text-gray-700">
                Apellido
              </label>
              <input
                id="last_name"
                name="last_name"
                type="text"
                required
                v-model="form.last_name"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                placeholder="Tu apellido"
              />
            </div>
          </div>

          <div>
            <label for="dni" class="block text-sm font-medium text-gray-700">
              DNI
            </label>
            <input
              id="dni"
              name="dni"
              type="text"
              required
              v-model="form.dni"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              placeholder="Número de documento"
            />
          </div>

          <div>
            <label for="birth_date" class="block text-sm font-medium text-gray-700">
              Fecha de Nacimiento
            </label>
            <input
              id="birth_date"
              name="birth_date"
              type="date"
              required
              :min="minBirthDate"
              :max="maxBirthDate"
              v-model="form.birth_date"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            />
          </div>

          <div>
            <label for="gender" class="block text-sm font-medium text-gray-700">
              Género
            </label>
            <select
              id="gender"
              name="gender"
              v-model="form.gender"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            >
              <option value="">Selecciona una opción</option>
              <option value="M">Masculino</option>
              <option value="F">Femenino</option>
              <option value="X">Otro</option>
            </select>
          </div>

          <div>
            <label for="username" class="block text-sm font-medium text-gray-700">
              Nombre de Usuario (opcional)
            </label>
            <input
              id="username"
              name="username"
              type="text"
              v-model="form.username"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              placeholder="Nombre de usuario único"
            />
          </div>

          <div>
            <h3 class="text-sm font-medium text-gray-700 mb-4">Ubicación *</h3>
            <LocationSelector
              v-model="form.location"
              :errors="locationErrors"
              @change="onLocationChange"
            />
          </div>

          <div class="space-y-4">
            <div>
              <label for="password" class="block text-sm font-medium text-gray-700">
                Nueva Contraseña
              </label>
              <input
                id="password"
                name="password"
                type="password"
                required
                v-model="form.password"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                placeholder="Mínimo 8 caracteres"
                minlength="8"
              />
            </div>
            <div>
              <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                Confirmar Contraseña
              </label>
              <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                required
                v-model="form.password_confirmation"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                placeholder="Repite la contraseña"
              />
            </div>
          </div>

          <div v-if="errors.length" class="bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">
                  Errores de validación
                </h3>
                <div class="mt-2 text-sm text-red-700">
                  <ul class="list-disc pl-5 space-y-1">
                    <li v-for="error in errors" :key="error">{{ error }}</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <div>
            <button
              type="submit"
              :disabled="loading"
              class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ loading ? 'Guardando...' : 'Completar Registro' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../stores/auth'
import { api, getCsrfCookie } from '../lib/api'
import LocationSelector from '../components/LocationSelector.vue'

const router = useRouter()
const auth = useAuth()

const loading = ref(false)
const errors = ref([])
const locationErrors = ref({})

const form = reactive({
  first_name: '',
  last_name: '',
  dni: '',
  birth_date: '',
  gender: '',
  username: '',
  password: '',
  password_confirmation: '',
  location: {
    country: '',
    country_name: '',
    state: '',
    state_name: '',
    city: '',
    city_name: ''
  }
})

// Computed properties for birth date validation
const minBirthDate = computed(() => {
  const eightyYearsAgo = new Date();
  eightyYearsAgo.setFullYear(eightyYearsAgo.getFullYear() - 80);
  return eightyYearsAgo.toISOString().split('T')[0];
});

const maxBirthDate = computed(() => {
  const eighteenYearsAgo = new Date();
  eighteenYearsAgo.setFullYear(eighteenYearsAgo.getFullYear() - 18);
  return eighteenYearsAgo.toISOString().split('T')[0];
});

// Handle location changes
const onLocationChange = (locationData) => {
  // Clear location errors when user makes changes
  locationErrors.value = {}
}

const completeRegistration = async () => {
  loading.value = true
  errors.value = []

  try {
    // Get CSRF token before making the request
    await getCsrfCookie()
    
    const { data } = await api.post('/admin/complete-registration', form)

    // Actualizar datos del usuario en el store
    await auth.me()
    
    // Redirigir al inicio en lugar del dashboard de admin
    router.push('/')
  } catch (error) {
    console.error('Error detallado:', error)
    
    if (error.response?.data?.errors) {
      const responseErrors = error.response.data.errors
      
      // Separate location errors from general errors
      const locationErrorKeys = ['location.country', 'location.country_name', 'location.state', 'location.state_name', 'location.city', 'location.city_name']
      const generalErrors = []
      
      Object.entries(responseErrors).forEach(([key, messages]) => {
        if (locationErrorKeys.includes(key)) {
          const locationKey = key.replace('location.', '')
          locationErrors.value[locationKey] = messages[0]
        } else {
          generalErrors.push(...messages)
        }
      })
      
      errors.value = generalErrors
    } else if (error.response?.data?.message) {
      errors.value = [error.response.data.message]
    } else {
      errors.value = ['Error al completar el registro. Por favor, inténtalo de nuevo.']
    }
  } finally {
    loading.value = false
  }
}
</script>