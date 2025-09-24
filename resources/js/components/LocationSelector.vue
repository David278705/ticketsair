<template>
    <div class="location-selector">
        <!-- Selector de País -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-2">
                País *
            </label>
            <select
                v-model="selectedCountry"
                @change="onCountryChange"
                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :class="{ 'border-red-500': errors.country }"
                :disabled="loading.countries"
            >
                <option value="">
                    {{ loading.countries ? 'Cargando países...' : 'Selecciona un país' }}
                </option>
                <option
                    v-for="country in countries"
                    :key="country.code"
                    :value="country.code"
                >
                    {{ country.name }}
                </option>
            </select>
            <p v-if="errors.country" class="mt-1 text-sm text-red-600">
                {{ errors.country }}
            </p>
        </div>

        <!-- Selector de Estado/Región -->
        <div v-if="selectedCountry" class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-2">
                Estado/Región *
            </label>
            <select
                v-model="selectedState"
                @change="onStateChange"
                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :class="{ 'border-red-500': errors.state }"
                :disabled="loading.states || !states.length"
            >
                <option value="">
                    {{ getStateSelectText() }}
                </option>
                <option
                    v-for="state in states"
                    :key="state.id"
                    :value="state.code"
                >
                    {{ state.name }}
                </option>
            </select>
            <p v-if="errors.state" class="mt-1 text-sm text-red-600">
                {{ errors.state }}
            </p>
            
            <!-- Búsqueda de ciudad alternativa si no hay estados -->
            <div v-if="!loading.states && !states.length" class="mt-2">
                <p class="text-sm text-slate-600 mb-2">
                    No se encontraron regiones. Puedes buscar directamente por ciudad:
                </p>
                <div class="relative">
                    <input
                        v-model="citySearchQuery"
                        @input="debouncedCitySearch"
                        type="text"
                        placeholder="Buscar ciudad..."
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                    
                    <!-- Resultados de búsqueda -->
                    <div
                        v-if="searchedCities.length"
                        class="absolute z-10 w-full mt-1 bg-white border border-slate-300 rounded-lg shadow-lg max-h-48 overflow-y-auto"
                    >
                        <button
                            v-for="city in searchedCities"
                            :key="city.id"
                            @click="selectSearchedCity(city)"
                            class="w-full text-left px-3 py-2 hover:bg-slate-100 border-b border-slate-100 last:border-b-0"
                        >
                            <div class="font-medium">{{ city.name }}</div>
                            <div v-if="city.adminName" class="text-sm text-slate-600">
                                {{ city.adminName }}
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selector de Ciudad -->
        <div v-if="selectedState && cities.length" class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-2">
                Ciudad *
            </label>
            <select
                v-model="selectedCity"
                @change="onCityChange"
                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :class="{ 'border-red-500': errors.city }"
                :disabled="loading.cities"
            >
                <option value="">
                    {{ loading.cities ? 'Cargando ciudades...' : 'Selecciona una ciudad' }}
                </option>
                <option
                    v-for="city in cities"
                    :key="city.id"
                    :value="city.id"
                >
                    {{ city.name }}
                    <span v-if="city.population" class="text-slate-500">
                        ({{ formatPopulation(city.population) }} hab.)
                    </span>
                </option>
            </select>
            <p v-if="errors.city" class="mt-1 text-sm text-red-600">
                {{ errors.city }}
            </p>
        </div>

        <!-- Resumen de selección -->
        <div v-if="hasValidSelection" class="mt-4 p-3 bg-slate-50 rounded-lg">
            <p class="text-sm text-slate-700">
                <span class="font-medium">Ubicación seleccionada:</span>
            </p>
            <p class="text-sm text-slate-600 mt-1">
                {{ getLocationSummary() }}
            </p>
        </div>

        <!-- Estado de carga -->
        <div v-if="loading.countries" class="flex items-center justify-center py-4">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
            <span class="ml-2 text-slate-600">Cargando datos de ubicación...</span>
        </div>

        <!-- Información sobre fuente de datos -->
        <div v-if="selectedCountry && !loading.countries" class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded text-xs text-blue-700">
            <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
            Los datos se obtienen de fuentes actualizadas. Si no encuentras tu ubicación, puedes buscarla directamente.
        </div>
    </div>
</template>

<script>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import locationService from '@/services/locationService'

export default {
    name: 'LocationSelector',
    props: {
        modelValue: {
            type: Object,
            default: () => ({
                country: '',
                state: '',
                city: ''
            })
        },
        errors: {
            type: Object,
            default: () => ({})
        },
        required: {
            type: Boolean,
            default: true
        }
    },
    emits: ['update:modelValue', 'change'],
    setup(props, { emit }) {
        // Estados reactivos
        const selectedCountry = ref(props.modelValue?.country || '')
        const selectedState = ref(props.modelValue?.state || '')
        const selectedCity = ref(props.modelValue?.city || '')
        
        const countries = ref([])
        const states = ref([])
        const cities = ref([])
        const searchedCities = ref([])
        const citySearchQuery = ref('')
        
        const loading = reactive({
            countries: false,
            states: false,
            cities: false
        })

        // Computadas
        const hasValidSelection = computed(() => {
            return selectedCountry.value && (selectedState.value || selectedCity.value)
        })

        // Métodos
        const loadCountries = async () => {
            loading.countries = true
            try {
                countries.value = await locationService.getCountries()
            } catch (error) {
                console.error('Error loading countries:', error)
            } finally {
                loading.countries = false
            }
        }

        const loadStates = async (countryCode) => {
            if (!countryCode) return
            
            loading.states = true
            states.value = []
            cities.value = []
            selectedState.value = ''
            selectedCity.value = ''
            
            try {
                states.value = await locationService.getStates(countryCode)
            } catch (error) {
                console.error('Error loading states:', error)
            } finally {
                loading.states = false
            }
        }

        const loadCities = async (stateId, countryCode) => {
            if (!stateId) return
            
            loading.cities = true
            cities.value = []
            selectedCity.value = ''
            
            try {
                cities.value = await locationService.getCities(stateId, countryCode)
            } catch (error) {
                console.error('Error loading cities:', error)
            } finally {
                loading.cities = false
            }
        }

        const searchCities = async (query, countryCode) => {
            if (!query || query.length < 2) {
                searchedCities.value = []
                return
            }
            
            try {
                searchedCities.value = await locationService.searchCities(query, countryCode)
            } catch (error) {
                console.error('Error searching cities:', error)
            }
        }

        // Debounce para búsqueda de ciudades
        let searchTimeout = null
        const debouncedCitySearch = () => {
            clearTimeout(searchTimeout)
            searchTimeout = setTimeout(() => {
                searchCities(citySearchQuery.value, selectedCountry.value)
            }, 300)
        }

        // Manejadores de eventos
        const onCountryChange = () => {
            loadStates(selectedCountry.value)
            emitChange()
        }

        const onStateChange = () => {
            if (selectedState.value) {
                const state = states.value.find(s => s.code === selectedState.value)
                if (state) {
                    loadCities(state.id, selectedCountry.value)
                }
            }
            emitChange()
        }

        const onCityChange = () => {
            emitChange()
        }

        const selectSearchedCity = (city) => {
            selectedCity.value = city.id.toString()
            searchedCities.value = []
            citySearchQuery.value = city.name
            emitChange()
        }

        const emitChange = () => {
            const value = {
                country: selectedCountry.value,
                state: selectedState.value,
                city: selectedCity.value
            }
            
            emit('update:modelValue', value)
            emit('change', value)
        }

        // Métodos auxiliares
        const getStateSelectText = () => {
            if (loading.states) return 'Cargando regiones...'
            if (!states.value.length) return 'Regiones no disponibles - puedes buscar ciudades directamente'
            return 'Selecciona una región'
        }

        const getLocationSummary = () => {
            const parts = []
            
            if (selectedCity.value) {
                const city = cities.value.find(c => c.id.toString() === selectedCity.value) ||
                           searchedCities.value.find(c => c.id.toString() === selectedCity.value)
                if (city) parts.push(city.name)
            }
            
            if (selectedState.value) {
                const state = states.value.find(s => s.code === selectedState.value)
                if (state) parts.push(state.name)
            }
            
            if (selectedCountry.value) {
                const country = countries.value.find(c => c.code === selectedCountry.value)
                if (country) parts.push(country.name)
            }
            
            return parts.join(', ')
        }

        const formatPopulation = (population) => {
            if (population >= 1000000) {
                return (population / 1000000).toFixed(1) + 'M'
            }
            if (population >= 1000) {
                return (population / 1000).toFixed(0) + 'K'
            }
            return population.toString()
        }

        // Watchers
        watch(() => props.modelValue, (newValue) => {
            if (newValue) {
                selectedCountry.value = newValue.country || ''
                selectedState.value = newValue.state || ''
                selectedCity.value = newValue.city || ''
            }
        }, { deep: true })

        // Inicialización
        onMounted(async () => {
            await loadCountries()
            
            // Si ya hay valores seleccionados, cargar los datos correspondientes
            if (selectedCountry.value) {
                await loadStates(selectedCountry.value)
                
                if (selectedState.value) {
                    const state = states.value.find(s => s.code === selectedState.value)
                    if (state) {
                        await loadCities(state.id, selectedCountry.value)
                    }
                }
            }
        })

        return {
            // Estados
            selectedCountry,
            selectedState,
            selectedCity,
            countries,
            states,
            cities,
            searchedCities,
            citySearchQuery,
            loading,
            
            // Computadas
            hasValidSelection,
            
            // Métodos
            onCountryChange,
            onStateChange,
            onCityChange,
            selectSearchedCity,
            debouncedCitySearch,
            getStateSelectText,
            getLocationSummary,
            formatPopulation
        }
    }
}
</script>

<style scoped>
.location-selector {
    /* Estilos específicos si es necesario */
}
</style>