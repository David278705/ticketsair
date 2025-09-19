<template>
  <div class="min-h-screen bg-white flex flex-col">
    <!-- Logo y búsqueda central -->
    <div :class="`flex flex-col items-center transition-all duration-300 ${
      hasSearched ? 'pt-6' : 'justify-center flex-1'
    }`">
      <!-- Logo de Google -->
      <div class="mb-8">
        <h1 class="text-6xl font-normal select-none">
          <span class="text-blue-500">G</span>
          <span class="text-red-500">o</span>
          <span class="text-yellow-500">o</span>
          <span class="text-blue-500">g</span>
          <span class="text-green-500">l</span>
          <span class="text-red-500">e</span>
        </h1>
      </div>
      
      <!-- Barra de búsqueda -->
      <div class="w-full max-w-xl px-4">
        <div class="relative group">
          <div class="flex items-center bg-white border border-gray-200 rounded-full shadow-sm hover:shadow-md group-focus-within:shadow-md transition-shadow duration-200">
            <Search class="w-4 h-4 text-gray-400 ml-4" />
            <input 
              v-model="searchQuery"
              @keyup.enter="search"
              type="text"
              class="w-full h-11 px-4 rounded-full focus:outline-none"
              placeholder=""
              autocomplete="off"
            >
            <div class="flex items-center pr-3">
              <button class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                <Mic class="w-4 h-4 text-gray-400" />
              </button>
              <button class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                <Camera class="w-4 h-4 text-gray-400" />
              </button>
            </div>
          </div>
        </div>
        
        <!-- Botones -->
        <div v-if="!hasSearched" class="flex justify-center gap-3 mt-8">
          <button 
            @click="search"
            class="px-5 py-2 bg-gray-50 hover:bg-gray-100 border border-gray-50 rounded text-sm text-gray-800 transition-colors"
          >
            Buscar con Google
          </button>
          <button class="px-5 py-2 bg-gray-50 hover:bg-gray-100 border border-gray-50 rounded text-sm text-gray-800 transition-colors">
            Voy a tener suerte
          </button>
        </div>
      </div>
    </div>

    <!-- Resultados de búsqueda -->
    <div v-if="hasSearched" class="max-w-2xl mx-auto px-6 flex-1">
      <!-- Información de resultados -->
      <div class="text-sm text-gray-600 mb-4 border-b pb-3">
        Cerca de 284,000,000 resultados (0.35 segundos)
      </div>
      
      <!-- Lista de resultados -->
      <div class="space-y-6">
        <div v-for="(result, i) in searchResults" :key="i" class="group">
          <div class="text-sm text-gray-600 mb-1">
            {{ result.url }}
          </div>
          <a 
            :href="result.url.includes('ticketsair') ? 'http://127.0.0.1:8000' : result.url"
            target="_blank"
            rel="noopener noreferrer"
            class="block"
          >
            <h3 class="text-xl text-blue-600 hover:underline mb-1 leading-tight">
              {{ result.title }}
            </h3>
            <p class="text-sm text-gray-600 leading-relaxed">
              {{ result.description }}
            </p>
          </a>
        </div>
      </div>

      <!-- Más resultados -->
      <div class="flex justify-center mt-10 mb-8">
        <div class="text-blue-600 text-2xl font-bold">
          Goooooogle
        </div>
      </div>
      
      <div class="flex justify-center gap-2 mb-8">
        <button 
          v-for="page in 10" 
          :key="page"
          :class="`w-10 h-10 rounded-full flex items-center justify-center text-blue-600 hover:bg-gray-100 transition-colors ${
            page === 1 ? 'bg-blue-600 text-white hover:bg-blue-700' : ''
          }`"
        >
          {{ page }}
        </button>
        <button class="px-4 py-2 text-blue-600 hover:bg-gray-100 rounded transition-colors">
          Siguiente
        </button>
      </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-100 mt-auto">
      <div class="px-6 py-3 border-b border-gray-200">
        <span class="text-gray-600 text-sm">Colombia</span>
      </div>
      <div class="px-6 py-3 flex flex-col sm:flex-row justify-between items-center text-sm text-gray-600">
        <div class="flex flex-wrap gap-6">
          <a href="#" class="hover:underline">Sobre Google</a>
          <a href="#" class="hover:underline">Publicidad</a>
          <a href="#" class="hover:underline">Negocios</a>
          <a href="#" class="hover:underline">Cómo funciona la Búsqueda</a>
        </div>
        <div class="flex flex-wrap gap-6 mt-4 sm:mt-0">
          <a href="#" class="hover:underline">Privacidad</a>
          <a href="#" class="hover:underline">Condiciones</a>
          <a href="#" class="hover:underline">Configuración</a>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Search, Mic, Camera } from 'lucide-vue-next';

const searchQuery = ref('');
const hasSearched = ref(false);

// Resultados simulados
const searchResults = ref([
  {
    title: 'TicketsAir - Vuelos al mejor precio',
    description: 'Encuentra los mejores precios en vuelos nacionales e internacionales. Compara aerolíneas y ahorra en tu próximo viaje.',
    url: 'https://ticketsair.com'
  },
  {
    title: 'Avianca - Vuelos nacionales e internacionales',
    description: 'Reserva tus vuelos con Avianca. Encuentra ofertas exclusivas y la mejor experiencia de viaje.',
    url: 'https://www.avianca.com'
  },
  {
    title: 'LATAM Airlines - Vuelos a todo el mundo',
    description: 'Compra tus pasajes aéreos al mejor precio. Viaja por Sudamérica y el mundo con LATAM Airlines.',
    url: 'https://www.latamairlines.com'
  },
  {
    title: 'Copa Airlines - Conectando las Américas',
    description: 'Reserva vuelos a más de 80 destinos en América con Copa Airlines. Mejores precios garantizados.',
    url: 'https://www.copaair.com'
  }
]);

const search = () => {
  if (searchQuery.value.trim()) {
    hasSearched.value = true;
  }
};
</script>

<style scoped>
/* Estilos adicionales si necesitas algo específico */
</style>