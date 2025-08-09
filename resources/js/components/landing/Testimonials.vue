<template>
    <section class="container">
        <h2 class="text-2xl font-bold mb-6">Lo que dicen quienes viajan</h2>
        <div class="grid gap-4 md:grid-cols-3">
            <figure
                v-for="t in testimonials"
                :key="t.id"
                class="rounded-2xl border border-slate-200 dark:border-slate-800 p-5"
            >
                <blockquote
                    class="text-sm text-slate-700 dark:text-slate-300 italic"
                >
                    “{{ t.text }}”
                </blockquote>
                <figcaption class="mt-3 text-sm text-slate-500">
                    — {{ t.author }}
                </figcaption>
            </figure>
        </div>
    </section>
</template>

<script setup>
const testimonials = [
    { id: 1, text: "La búsqueda es súper rápida y clara.", author: "Ana L." },
    {
        id: 2,
        text: "Pude reservar en minutos y luego comprar sin problemas.",
        author: "David P.",
    },
    { id: 3, text: "La experiencia móvil es perfecta.", author: "María G." },
];
</script>
