<template>
    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute inset-0 -z-10">
            <div
                class="absolute -top-32 -left-32 h-96 w-96 rounded-full bg-gradient-to-br from-blue-600/20 to-cyan-400/20 blur-3xl animate-blob"
            ></div>
            <div
                class="absolute -bottom-24 -right-24 h-[28rem] w-[28rem] rounded-full bg-gradient-to-tl from-fuchsia-500/10 to-indigo-500/10 blur-3xl animate-blob animation-delay-2000"
            ></div>
            <div
                class="absolute top-10 right-1/4 h-56 w-56 rounded-full bg-gradient-to-tr from-sky-400/10 to-emerald-400/10 blur-3xl animate-blob animation-delay-4000"
            ></div>
        </div>
        <div
            class="container py-14 md:py-24 grid md:grid-cols-2 gap-10 items-center"
        >
            <div class="space-y-6">
                <span
                    class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1 text-xs"
                >
                    <span class="size-2 rounded-full bg-green-500"></span>
                    Mejorando precios en tiempo real
                </span>
                <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight">
                    Vuela fácil, rápido y
                    <span
                        class="bg-gradient-to-r from-blue-600 to-cyan-400 bg-clip-text text-transparent"
                        >al mejor precio</span
                    >.
                </h1>
                <p class="text-slate-600 max-w-xl">
                    Explora miles de rutas y encuentra tu opción ideal. Sin
                    sobrecargas: claridad, velocidad y seguridad.
                </p>
                <div class="flex gap-6 text-sm text-slate-600">
                    <div>
                        <span class="text-xl font-bold">+120K</span> viajeros
                    </div>
                    <div>
                        <span class="text-xl font-bold">4.9★</span> satisfacción
                    </div>
                    <div>
                        <span class="text-xl font-bold">24/7</span> soporte
                    </div>
                </div>
            </div>

            <div class="relative">
                <div
                    class="aspect-[4/3] w-full rounded-3xl bg-gradient-to-tr from-blue-600/15 to-cyan-400/15 border border-slate-200 dark:border-slate-800 shadow-xl"
                ></div>
                <div
                    class="absolute inset-0 -z-10 blur-2xl bg-gradient-to-tr from-blue-600/10 to-cyan-400/10 rounded-3xl"
                ></div>
            </div>
        </div>
    </section>
</template>
