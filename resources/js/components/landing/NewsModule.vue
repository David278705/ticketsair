<template>
    <section class="container mx-auto px-4">
        <h2 class="text-2xl font-bold mb-4">Últimas Noticias</h2>
        <div class="bg-white rounded-lg shadow-md p-4">
            <div v-if="loading" class="text-center py-4">
                <span class="text-gray-500">Cargando noticias...</span>
            </div>
            <div v-else-if="news.length === 0" class="text-center py-4">
                <span class="text-gray-500">No hay noticias recientes</span>
            </div>
            <ul v-else class="space-y-4">
                <li v-for="item in news" 
                    :key="item.id" 
                    class="border-b last:border-0 pb-4 last:pb-0">
                    <div class="flex gap-4">
                        <img v-if="item.image_path" 
                             :src="item.image_path" 
                             :alt="item.title"
                             class="w-24 h-24 object-cover rounded-lg">
                        <div>
                            <h3 class="font-semibold text-lg">{{ item.title }}</h3>
                            <p class="text-gray-600">{{ item.body }}</p>
                            <span class="text-sm text-gray-400">{{ formatDate(item.created_at) }}</span>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { api } from '../../lib/api';

const loading = ref(true);
const news = ref([]);

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

onMounted(async () => {
    try {
        const response = await api.get('/news');
        news.value = response.data;
    } catch (error) {
        console.error('Error fetching news:', error);
    } finally {
        loading.value = false;
    }
});
</script>