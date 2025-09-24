<template>
  <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
          <h1 class="text-2xl font-bold text-gray-900">Mi Perfil</h1>
          <p class="mt-1 text-sm text-gray-600">Administra tu información personal y configuraciones de cuenta</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sidebar -->
        <div class="lg:col-span-1">
          <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center space-x-3">
              <div class="flex-shrink-0">
                <div class="h-16 w-16 bg-indigo-100 rounded-full flex items-center justify-center">
                  <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-lg font-semibold text-gray-900 truncate">{{ user?.name || 'Usuario' }}</p>
                <p class="text-sm text-gray-500 truncate">{{ user?.email }}</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="roleClass">
                  {{ roleLabel }}
                </span>
              </div>
            </div>
          </div>

          <!-- Navigation -->
          <div class="mt-6 bg-white shadow rounded-lg">
            <nav class="space-y-1">
              <button
                @click="activeTab = 'personal'"
                :class="activeTab === 'personal' ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                class="w-full flex items-center px-3 py-2 text-sm font-medium border-l-4 transition-colors text-left"
              >
                <svg class="mr-3 flex-shrink-0 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Información Personal
              </button>
              <button
                @click="activeTab = 'security'"
                :class="activeTab === 'security' ? 'bg-indigo-50 border-indigo-500 text-indigo-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                class="w-full flex items-center px-3 py-2 text-sm font-medium border-l-4 transition-colors text-left"
              >
                <svg class="mr-3 flex-shrink-0 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                Seguridad
              </button>
            </nav>
          </div>
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-2">
          <!-- Personal Information Tab -->
          <div v-if="activeTab === 'personal'" class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Información Personal</h2>
              <p class="mt-1 text-sm text-gray-600">Actualiza tu información personal y de contacto</p>
            </div>

            <form @submit.prevent="updatePersonalInfo" class="p-6 space-y-6">
              <!-- Name Fields -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label for="first_name" class="block text-sm font-medium text-gray-700">
                    Nombre *
                  </label>
                  <input
                    id="first_name"
                    v-model="personalForm.first_name"
                    type="text"
                    required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  />
                </div>
                <div>
                  <label for="last_name" class="block text-sm font-medium text-gray-700">
                    Apellido *
                  </label>
                  <input
                    id="last_name"
                    v-model="personalForm.last_name"
                    type="text"
                    required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  />
                </div>
              </div>

              <!-- Email (readonly) -->
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700">
                  Correo Electrónico
                </label>
                <input
                  id="email"
                  :value="user?.email"
                  type="email"
                  readonly
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-500 cursor-not-allowed sm:text-sm"
                />
                <p class="mt-1 text-xs text-gray-500">El correo electrónico no se puede modificar</p>
              </div>

              <!-- DNI -->
              <div>
                <label for="dni" class="block text-sm font-medium text-gray-700">
                  Documento de Identidad *
                </label>
                <input
                  id="dni"
                  v-model="personalForm.dni"
                  type="text"
                  required
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>

              <!-- Birth Date and Gender -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label for="birth_date" class="block text-sm font-medium text-gray-700">
                    Fecha de Nacimiento *
                  </label>
                  <input
                    id="birth_date"
                    v-model="personalForm.birth_date"
                    type="date"
                    required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  />
                </div>
                <div>
                  <label for="gender" class="block text-sm font-medium text-gray-700">
                    Género
                  </label>
                  <select
                    id="gender"
                    v-model="personalForm.gender"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  >
                    <option value="">Selecciona una opción</option>
                    <option value="M">Masculino</option>
                    <option value="F">Femenino</option>
                    <option value="X">Otro</option>
                  </select>
                </div>
              </div>

              <!-- Username and Billing Address -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label for="username" class="block text-sm font-medium text-gray-700">
                    Nombre de Usuario
                  </label>
                  <input
                    id="username"
                    v-model="personalForm.username"
                    type="text"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  />
                </div>
                <div>
                  <label for="news_opt_in" class="flex items-center space-x-3 text-sm font-medium text-gray-700">
                    <input
                      id="news_opt_in"
                      v-model="personalForm.news_opt_in"
                      type="checkbox"
                      class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                    />
                    <span>Recibir noticias y promociones</span>
                  </label>
                </div>
              </div>

              <!-- Billing Address -->
              <div>
                <label for="billing_address" class="block text-sm font-medium text-gray-700">
                  Dirección de Facturación
                </label>
                <textarea
                  id="billing_address"
                  v-model="personalForm.billing_address"
                  rows="3"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  placeholder="Ingresa tu dirección completa..."
                />
              </div>

              <!-- Location Selector -->
              <div>
                <h3 class="text-sm font-medium text-gray-700 mb-4">Ubicación</h3>
                <LocationSelector
                  v-model="personalForm.location"
                  :errors="locationErrors"
                  @change="onLocationChange"
                />
              </div>

              <!-- Error Messages -->
              <div v-if="personalErrors.length" class="bg-red-50 border border-red-200 rounded-md p-4">
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
                        <li v-for="error in personalErrors" :key="error">{{ error }}</li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Success Message -->
              <div v-if="personalSuccess" class="bg-green-50 border border-green-200 rounded-md p-4">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <div class="ml-3">
                    <p class="text-sm text-green-800">{{ personalSuccess }}</p>
                  </div>
                </div>
              </div>

              <!-- Submit Button -->
              <div class="flex justify-end">
                <button
                  type="submit"
                  :disabled="personalLoading"
                  class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg v-if="personalLoading" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ personalLoading ? 'Guardando...' : 'Guardar Cambios' }}
                </button>
              </div>
            </form>
          </div>

          <!-- Security Tab -->
          <div v-if="activeTab === 'security'" class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Seguridad de la Cuenta</h2>
              <p class="mt-1 text-sm text-gray-600">Cambia tu contraseña para mantener tu cuenta segura</p>
            </div>

            <form @submit.prevent="updatePassword" class="p-6 space-y-6">
              <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700">
                  Contraseña Actual *
                </label>
                <input
                  id="current_password"
                  v-model="securityForm.current_password"
                  type="password"
                  required
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  placeholder="Ingresa tu contraseña actual"
                />
              </div>

              <div>
                <label for="new_password" class="block text-sm font-medium text-gray-700">
                  Nueva Contraseña *
                </label>
                <input
                  id="new_password"
                  v-model="securityForm.new_password"
                  type="password"
                  required
                  minlength="8"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  placeholder="Mínimo 8 caracteres"
                />
              </div>

              <div>
                <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700">
                  Confirmar Nueva Contraseña *
                </label>
                <input
                  id="new_password_confirmation"
                  v-model="securityForm.new_password_confirmation"
                  type="password"
                  required
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  placeholder="Repite la nueva contraseña"
                />
              </div>

              <!-- Password Requirements -->
              <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">
                      Requisitos de contraseña
                    </h3>
                    <div class="mt-2 text-sm text-blue-700">
                      <ul class="list-disc pl-5 space-y-1">
                        <li>Mínimo 8 caracteres</li>
                        <li>Se recomienda incluir números y símbolos</li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Error Messages -->
              <div v-if="securityErrors.length" class="bg-red-50 border border-red-200 rounded-md p-4">
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
                        <li v-for="error in securityErrors" :key="error">{{ error }}</li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Success Message -->
              <div v-if="securitySuccess" class="bg-green-50 border border-green-200 rounded-md p-4">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <div class="ml-3">
                    <p class="text-sm text-green-800">{{ securitySuccess }}</p>
                  </div>
                </div>
              </div>

              <!-- Submit Button -->
              <div class="flex justify-end">
                <button
                  type="submit"
                  :disabled="securityLoading"
                  class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg v-if="securityLoading" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ securityLoading ? 'Cambiando...' : 'Cambiar Contraseña' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useAuth } from '../stores/auth'
import { api, getCsrfCookie } from '../lib/api'
import LocationSelector from '../components/LocationSelector.vue'

const auth = useAuth()

const activeTab = ref('personal')
const personalLoading = ref(false)
const securityLoading = ref(false)
const personalErrors = ref([])
const securityErrors = ref([])
const locationErrors = ref({})
const personalSuccess = ref('')
const securitySuccess = ref('')

const user = computed(() => auth.user)

// Reactive forms
const personalForm = reactive({
  first_name: '',
  last_name: '',
  dni: '',
  birth_date: '',
  gender: '',
  username: '',
  billing_address: '',
  news_opt_in: false,
  location: {
    country: '',
    country_name: '',
    state: '',
    state_name: '',
    city: '',
    city_name: ''
  }
})

const securityForm = reactive({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
})

// Computed properties for user info
const roleLabel = computed(() => {
  const role = user.value?.role?.name
  switch (role) {
    case 'root': return 'Administrador Root'
    case 'admin': return 'Administrador'
    case 'client': return 'Cliente'
    default: return 'Usuario'
  }
})

const roleClass = computed(() => {
  const role = user.value?.role?.name
  switch (role) {
    case 'root': return 'bg-red-100 text-red-800'
    case 'admin': return 'bg-blue-100 text-blue-800'
    case 'client': return 'bg-green-100 text-green-800'
    default: return 'bg-gray-100 text-gray-800'
  }
})

// Load user data into form
const loadUserData = () => {
  if (user.value) {
    personalForm.first_name = user.value.first_name || ''
    personalForm.last_name = user.value.last_name || ''
    personalForm.dni = user.value.dni || ''
    personalForm.birth_date = user.value.birth_date ? user.value.birth_date.split('T')[0] : ''
    personalForm.gender = user.value.gender || ''
    personalForm.username = user.value.username || ''
    personalForm.billing_address = user.value.billing_address || ''
    personalForm.news_opt_in = user.value.news_opt_in || false
    
    // Load location data
    personalForm.location = {
      country: user.value.country_code || '',
      country_name: user.value.country_name || '',
      state: user.value.state_code || '',
      state_name: user.value.state_name || '',
      city: user.value.city_id || '',
      city_name: user.value.city_name || ''
    }
  }
}

// Handle location changes
const onLocationChange = (locationData) => {
  // Clear location errors when user makes changes
  locationErrors.value = {}
}

// Update personal information
const updatePersonalInfo = async () => {
  personalLoading.value = true
  personalErrors.value = []
  locationErrors.value = {}
  personalSuccess.value = ''

  try {
    await getCsrfCookie()
    
    const { data } = await api.put('/profile', personalForm)
    
    // Update user data in store
    await auth.me()
    
    personalSuccess.value = 'Información personal actualizada exitosamente.'
    
    // Clear success message after 5 seconds
    setTimeout(() => {
      personalSuccess.value = ''
    }, 5000)
  } catch (error) {
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors
      
      // Separate location errors from general errors
      const locationErrorKeys = ['location.country', 'location.country_name', 'location.state', 'location.state_name', 'location.city', 'location.city_name']
      const generalErrors = []
      
      Object.entries(errors).forEach(([key, messages]) => {
        if (locationErrorKeys.includes(key)) {
          const locationKey = key.replace('location.', '')
          locationErrors.value[locationKey] = messages[0]
        } else {
          generalErrors.push(...messages)
        }
      })
      
      personalErrors.value = generalErrors
    } else if (error.response?.data?.message) {
      personalErrors.value = [error.response.data.message]
    } else {
      personalErrors.value = ['Error al actualizar la información. Por favor, inténtalo de nuevo.']
    }
  } finally {
    personalLoading.value = false
  }
}

// Update password
const updatePassword = async () => {
  securityLoading.value = true
  securityErrors.value = []
  securitySuccess.value = ''

  try {
    await getCsrfCookie()
    
    await api.put('/profile/password', securityForm)
    
    // Clear form
    securityForm.current_password = ''
    securityForm.new_password = ''
    securityForm.new_password_confirmation = ''
    
    securitySuccess.value = 'Contraseña actualizada exitosamente.'
    
    // Clear success message after 5 seconds
    setTimeout(() => {
      securitySuccess.value = ''
    }, 5000)
  } catch (error) {
    if (error.response?.data?.errors) {
      securityErrors.value = Object.values(error.response.data.errors).flat()
    } else if (error.response?.data?.message) {
      securityErrors.value = [error.response.data.message]
    } else {
      securityErrors.value = ['Error al cambiar la contraseña. Por favor, inténtalo de nuevo.']
    }
  } finally {
    securityLoading.value = false
  }
}

// Clear messages when switching tabs
const clearMessages = () => {
  personalErrors.value = []
  securityErrors.value = []
  personalSuccess.value = ''
  securitySuccess.value = ''
}

// Watch for tab changes
import { watch } from 'vue'
watch(activeTab, clearMessages)

onMounted(() => {
  loadUserData()
})
</script>