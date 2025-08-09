<template>
    <section class="container">
        <h2 class="text-2xl font-bold mb-6">Rutas populares</h2>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <article
                v-for="r in routes"
                :key="r.id"
                class="rounded-2xl border border-slate-200 dark:border-slate-800 p-5 hover:shadow-md transition"
            >
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold">{{ r.from }} → {{ r.to }}</h3>
                    <span class="text-sm text-slate-500">{{ r.duration }}</span>
                </div>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                    Desde
                    <b class="text-slate-900 dark:text-white">{{ r.price }}</b>
                </p>
                <div class="mt-3 flex flex-wrap gap-2 text-xs">
                    <span
                        class="px-2 py-1 rounded-full bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300 border border-blue-200/70 dark:border-blue-500/20"
                        >Economy</span
                    >
                    <span
                        class="px-2 py-1 rounded-full bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300 border border-amber-200/70 dark:border-amber-500/20"
                        >Flexible</span
                    >
                </div>
            </article>
        </div>
    </section>
</template>

<script setup>
const routes = [
    {
        id: 1,
        from: "Bogotá (BOG)",
        to: "Cartagena (CTG)",
        duration: "1h 25m",
        price: "$ 180.000",
    },
    {
        id: 2,
        from: "Medellín (MDE)",
        to: "Miami (MIA)",
        duration: "3h 15m",
        price: "$ 980.000",
    },
    {
        id: 3,
        from: "Cali (CLO)",
        to: "Buenos Aires (EZE)",
        duration: "6h 40m",
        price: "$ 1.650.000",
    },
    {
        id: 4,
        from: "Pereira (PEI)",
        to: "Madrid (MAD)",
        duration: "9h 20m",
        price: "$ 2.200.000",
    },
    {
        id: 5,
        from: "Bogotá (BOG)",
        to: "Londres (LHR)",
        duration: "10h 10m",
        price: "$ 2.750.000",
    },
    {
        id: 6,
        from: "Medellín (MDE)",
        to: "Cartagena (CTG)",
        duration: "1h 05m",
        price: "$ 210.000",
    },
];
</script>
