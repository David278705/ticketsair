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
                <li
                    v-for="item in news"
                    :key="item.id"
                    class="border-b last:border-0 pb-4 last:pb-0"
                >
                    <h3 class="font-semibold text-lg">{{ item.title }}</h3>
                    <p class="text-gray-600">{{ item.description }}</p>
                    <span class="text-sm text-gray-400"
                        >{{ formatDate(item.created_at) }}</span
                    >
                </li>
            </ul>
        </div>
    </section>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { api } from "../../lib/api";

const loading = ref(true);
const news = ref([]);

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("es-ES", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};

onMounted(async () => {
    try {
        const response = await api.get("/news");
        news.value = response.data;
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
