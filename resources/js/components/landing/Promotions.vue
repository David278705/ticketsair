<template>
    <section id="promos" class="container">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold">Promociones</h2>
            <a
                href="#"
                class="text-sm text-blue-600 dark:text-cyan-400 hover:underline"
                >Ver todas →</a
            >
        </div>
        <div class="grid gap-4 md:grid-cols-3">
            <div
                v-for="p in promos"
                :key="p.id"
                class="rounded-3xl p-6 border border-slate-200 dark:border-slate-800 bg-gradient-to-br from-blue-600/10 via-cyan-400/10 to-transparent hover:shadow-lg transition"
            >
                <h3 class="font-semibold">{{ p.title }}</h3>
                <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">
                    {{ p.desc }}
                </p>
                <div
                    class="mt-4 text-3xl font-extrabold bg-clip-text text-transparent bg-gradient-to-tr from-blue-600 to-cyan-400"
                >
                    {{ p.off }}
                </div>
                <button
                    class="mt-4 h-10 rounded-xl border border-slate-300/70 dark:border-slate-700 px-4 hover:bg-slate-100 dark:hover:bg-slate-900"
                >
                    Detalles
                </button>
            </div>
        </div>
    </section>
</template>

<script setup>
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
