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
                    {{
                        loading.countries
                            ? "Cargando países..."
                            : "Selecciona un país"
                    }}
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
                    :value="state.id"
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
                    No se encontraron regiones. Puedes buscar directamente por
                    ciudad:
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
                            <div
                                v-if="city.adminName"
                                class="text-sm text-slate-600"
                            >
                                {{ city.adminName }}
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selector de Ciudad -->
        <div v-if="selectedState" class="mb-4">
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
                    {{
                        loading.cities
                            ? "Cargando ciudades..."
                            : "Selecciona una ciudad"
                    }}
                </option>
                <option v-for="city in cities" :key="city.id" :value="city.id">
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

        <!-- Información de ubicación existente (si hay datos pero no están cargando) -->
        <div
            v-else-if="
                modelValue &&
                (modelValue.country_name ||
                    modelValue.state_name ||
                    modelValue.city_name)
            "
            class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg"
        >
            <p class="text-sm text-blue-700">
                <span class="font-medium">📍 Ubicación actual:</span>
            </p>
            <p class="text-sm text-blue-600 mt-1">
                <span v-if="modelValue.city_name"
                    >{{ modelValue.city_name }},
                </span>
                <span v-if="modelValue.state_name"
                    >{{ modelValue.state_name }},
                </span>
                <span v-if="modelValue.country_name">{{
                    modelValue.country_name
                }}</span>
            </p>
            <p
                v-if="loading.countries || loading.states"
                class="text-xs text-blue-500 mt-1"
            >
                Cargando opciones de edición...
            </p>
        </div>

        <!-- Estado de carga -->
        <div
            v-if="loading.countries"
            class="flex items-center justify-center py-4"
        >
            <div
                class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"
            ></div>
            <span class="ml-2 text-slate-600"
                >Cargando datos de ubicación...</span
            >
        </div>
    </div>
</template>

<script>
import { ref, reactive, computed, onMounted, watch, nextTick } from "vue";
import locationService from "@/services/locationService";

export default {
    name: "LocationSelector",
    props: {
        modelValue: {
            type: Object,
            default: () => ({
                country: "",
                state: "",
                city: "",
            }),
        },
        errors: {
            type: Object,
            default: () => ({}),
        },
        required: {
            type: Boolean,
            default: true,
        },
    },
    emits: ["update:modelValue", "change"],
    setup(props, { emit }) {
        // Estados reactivos
        const selectedCountry = ref(props.modelValue?.country || "");
        const selectedState = ref(props.modelValue?.state || "");
        const selectedCity = ref(props.modelValue?.city || "");

        const countries = ref([]);
        const states = ref([]);
        const cities = ref([]);
        const searchedCities = ref([]);
        const citySearchQuery = ref("");

        // Flag para evitar reinicialización en cambios internos
        const isInternalChange = ref(false);

        const loading = reactive({
            countries: false,
            states: false,
            cities: false,
        });

        // Computadas
        const hasValidSelection = computed(() => {
            return (
                selectedCountry.value &&
                (selectedState.value || selectedCity.value)
            );
        });

        // Métodos
        const loadCountries = async () => {
            loading.countries = true;
            try {
                countries.value = await locationService.getCountries();
            } catch (error) {
                console.error("Error loading countries:", error);
            } finally {
                loading.countries = false;
            }
        };

        const loadStates = async (countryCode) => {
            if (!countryCode) return;

            loading.states = true;
            states.value = [];
            cities.value = [];
            selectedState.value = "";
            selectedCity.value = "";

            try {
                states.value = await locationService.getStates(countryCode);
            } catch (error) {
                console.error("Error loading states:", error);
            } finally {
                loading.states = false;
            }
        };

        const loadCities = async (selectedStateObj, countryCode) => {
            if (!selectedStateObj) {
                return;
            }

            loading.cities = true;
            cities.value = [];
            selectedCity.value = "";

            try {
                const countryName = countries.value.find(
                    (c) => c.code === countryCode
                )?.name;
                const stateName =
                    selectedStateObj.originalName || selectedStateObj.name;

                if (countryName && stateName) {
                    cities.value = await locationService.getCities(
                        countryName,
                        stateName
                    );
                }
            } catch (error) {
                console.error("LocationSelector: Error loading cities:", error);
            } finally {
                loading.cities = false;
            }
        };

        const searchCities = async (query, countryCode) => {
            if (!query || query.length < 2) {
                searchedCities.value = [];
                return;
            }

            try {
                searchedCities.value = await locationService.searchCities(
                    query,
                    countryCode
                );
            } catch (error) {
                console.error("Error searching cities:", error);
            }
        };

        // Debounce para búsqueda de ciudades
        let searchTimeout = null;
        const debouncedCitySearch = () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchCities(citySearchQuery.value, selectedCountry.value);
            }, 300);
        };

        // Manejadores de eventos
        const onCountryChange = () => {
            loadStates(selectedCountry.value);
            emitChange();
        };

        const onStateChange = () => {
            if (selectedState.value) {
                const state = states.value.find(
                    (s) => s.id === selectedState.value
                );

                if (state) {
                    loadCities(state, selectedCountry.value);
                }
            }
            emitChange();
        };

        const onCityChange = () => {
            emitChange();
        };

        const selectSearchedCity = (city) => {
            selectedCity.value = city.id.toString();
            searchedCities.value = [];
            citySearchQuery.value = city.name;
            emitChange();
        };

        const emitChange = () => {
            // Marcar como cambio interno para evitar reinicialización
            isInternalChange.value = true;

            // Obtener nombres completos para el backend
            const country = countries.value.find(
                (c) => c.code === selectedCountry.value
            );
            const state = states.value.find(
                (s) => s.id === selectedState.value
            );
            const city =
                cities.value.find((c) => c.id === selectedCity.value) ||
                searchedCities.value.find((c) => c.id === selectedCity.value);

            const value = {
                country: selectedCountry.value,
                country_name: country ? country.name : "",
                state: selectedState.value,
                state_name: state ? state.name : "",
                city: city ? city.name : "",
                city_name: city ? city.name : "",
            };

            emit("update:modelValue", value);
            emit("change", value);

            // Resetear flag después de un tick
            nextTick(() => {
                isInternalChange.value = false;
            });
        };

        // Métodos auxiliares
        const getStateSelectText = () => {
            if (loading.states) return "Cargando regiones...";
            if (!states.value.length)
                return "Regiones no disponibles - puedes buscar ciudades directamente";
            return "Selecciona una región";
        };

        const getLocationSummary = () => {
            const parts = [];

            if (selectedCity.value) {
                const city =
                    cities.value.find(
                        (c) => c.id.toString() === selectedCity.value
                    ) ||
                    searchedCities.value.find(
                        (c) => c.id.toString() === selectedCity.value
                    );
                if (city) parts.push(city.name);
            }

            if (selectedState.value) {
                const state = states.value.find(
                    (s) => s.id === selectedState.value
                );
                if (state) parts.push(state.name);
            }

            if (selectedCountry.value) {
                const country = countries.value.find(
                    (c) => c.code === selectedCountry.value
                );
                if (country) parts.push(country.name);
            }

            return parts.join(", ");
        };

        const formatPopulation = (population) => {
            if (population >= 1000000) {
                return (population / 1000000).toFixed(1) + "M";
            }
            if (population >= 1000) {
                return (population / 1000).toFixed(0) + "K";
            }
            return population.toString();
        };

        // Función para inicializar los datos seleccionados
        const initializeSelection = async () => {
            if (selectedCountry.value) {
                await loadStates(selectedCountry.value);

                if (selectedState.value) {
                    // Buscar el estado por ID o por código para compatibilidad
                    let state = states.value.find(
                        (s) => s.id === selectedState.value
                    );
                    if (!state) {
                        state = states.value.find(
                            (s) => s.code === selectedState.value
                        );
                        // Si encontramos por código, actualizar el valor seleccionado al ID
                        if (state) {
                            selectedState.value = state.id;
                        }
                    }
                    if (state) {
                        await loadCities(state, selectedCountry.value);

                        // Si hay una ciudad seleccionada después de cargar las ciudades
                        if (selectedCity.value) {
                            const city = cities.value.find(
                                (c) => c.id === selectedCity.value
                            );
                            if (!city) {
                                // Si no se encuentra por ID, buscar por nombre si está disponible
                                const cityName = props.modelValue?.city_name;
                                if (cityName) {
                                    const cityByName = cities.value.find(
                                        (c) => c.name === cityName
                                    );
                                    if (cityByName) {
                                        selectedCity.value = cityByName.id;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        };

        // Watchers
        watch(
            () => props.modelValue,
            async (newValue) => {
                // Solo procesar si no es un cambio interno (del usuario)
                if (isInternalChange.value) {
                    return;
                }

                if (newValue && typeof newValue === "object") {
                    selectedCountry.value = newValue.country || "";
                    selectedState.value = newValue.state || "";
                    selectedCity.value = newValue.city || "";

                    // Inicializar la selección cuando cambien los datos
                    if (countries.value.length > 0) {
                        await initializeSelection();
                    }
                }
            },
            { deep: true, immediate: true }
        );

        // Inicialización
        onMounted(async () => {
            await loadCountries();

            // Después de cargar países, inicializar la selección
            await initializeSelection();
        });

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
            formatPopulation,
        };
    },
};
</script>

<style scoped>
/* Estilos específicos si es necesario */
</style>
