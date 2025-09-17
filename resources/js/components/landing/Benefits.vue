<template>
    <section id="beneficios" class="container">
        <h2 class="text-2xl font-bold mb-6">Por qué elegirnos</h2>
        <div class="grid gap-4 md:grid-cols-3">
            <div
                v-for="b in benefits"
                :key="b.id"
                class="rounded-2xl border border-slate-200 p-5"
            >
                <div class="text-3xl">{{ b.icon }}</div>
                <h3 class="mt-3 font-semibold">{{ b.title }}</h3>
                <p class="text-sm text-slate-600 mt-1">
                    {{ b.text }}
                </p>
            </div>
        </div>
    </section>
</template>

<script setup>
const benefits = [
    {
        id: 1,
        icon: "⚡",
        title: "Rápido y claro",
        text: "Interfaz sin ruido visual, resultados al instante.",
    },
    {
        id: 2,
        icon: "🔒",
        title: "Seguro",
        text: "Buenas prácticas y protección de datos.",
    },
    {
        id: 3,
        icon: "💬",
        title: "Soporte 24/7",
        text: "Siempre disponibles para ayudarte.",
    },
];
</script>
