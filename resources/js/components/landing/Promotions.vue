<template>
    <section id="promos" class="container">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold">Promociones</h2>
            <a href="#" class="text-sm text-blue-600 hover:underline"
                >Ver todas →</a
            >
        </div>
        <div class="grid gap-4 md:grid-cols-3">
            <div
                v-for="p in promos"
                :key="p.id"
                class="rounded-3xl p-6 border border-slate-200 bg-gradient-to-br from-blue-600/10 via-cyan-400/10 to-transparent hover:shadow-lg transition"
            >
                <h3 class="font-semibold">{{ p.title }}</h3>
                <p class="text-sm text-slate-600 mt-1">
                    {{ p.desc }}
                </p>
                <div
                    class="mt-4 text-3xl font-extrabold bg-clip-text text-transparent bg-gradient-to-tr from-blue-600 to-cyan-400"
                >
                    {{ p.off }}
                </div>
                <button
                    class="mt-4 h-10 rounded-xl border border-slate-300/70 px-4 hover:bg-slate-100"
                >
                    Detalles
                </button>
            </div>
        </div>
    </section>
    <section class="container mx-auto px-4 py-8 mt-12">
        <h2 class="text-3xl font-bold mb-6 text-gray-900 dark:text-white">Últimas Noticias</h2>
        
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            <p class="mt-4 text-gray-500">Cargando noticias...</p>
        </div>
        
        <!-- Empty State -->
        <div v-else-if="news.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
            </svg>
            <p class="mt-4 text-gray-500 dark:text-gray-400">No hay noticias recientes</p>
        </div>
        
        <!-- News Grid -->
        <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <div 
                v-for="item in news" 
                :key="item.id" 
                class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow dark:bg-gray-800 dark:border-gray-700"
            >
                <a href="#" @click.prevent>
                    <img 
                        v-if="item.image_path"
                        class="rounded-t-lg w-full h-48 object-cover" 
                        :src="getImageUrl(item.image_path)" 
                        :alt="item.title"
                    />
                    <div 
                        v-else 
                        class="rounded-t-lg w-full h-48 bg-gradient-to-br from-blue-500 to-cyan-400 flex items-center justify-center"
                    >
                        <svg class="w-16 h-16 text-white opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                        </svg>
                    </div>
                </a>
                <div class="p-5">
                    <a href="#" @click.prevent>
                        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                            {{ item.title }}
                        </h5>
                    </a>
                    <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">
                        {{ item.body || 'Sin descripción disponible' }}
                    </p>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ formatDate(item.created_at) }}
                        </span>
                        <span 
                            v-if="item.is_promotion" 
                            class="inline-flex items-center px-3 py-1 text-xs font-medium text-white bg-gradient-to-r from-blue-600 to-cyan-400 rounded-full"
                        >
                            Promoción
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { api, BASE_URL } from "../../lib/api";

const loading = ref(true);
const news = ref([]);

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("es-ES", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};

const getImageUrl = (imagePath) => {
    if (!imagePath) return null;
    // Si ya es una URL completa, devolverla tal cual
    if (imagePath.startsWith('http')) return imagePath;
    // Si no, construir la URL completa al storage
    return `${BASE_URL}/storage/${imagePath}`;
};

onMounted(async () => {
    try {
        const response = await api.get("/news");
        // La API devuelve datos paginados, así que accedemos a response.data.data
        news.value = response.data.data || [];
    } catch (error) {
        console.error("Error fetching news:", error);
    } finally {
        loading.value = false;
    }
});

const promos = [
    {
        id: 1,
        title: "Semana Caribe",
        desc: "Vuela al Caribe con tarifas especiales.",
        off: "-35%",
    },
    {
        id: 2,
        title: "Europa a tu alcance",
        desc: "Ciudades top con descuento.",
        off: "-20%",
    },
    {
        id: 3,
        title: "Escapadas nacionales",
        desc: "Fin de semana desde $120.000",
        off: "HOT",
    },
];
</script>
