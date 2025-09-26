<template>
    <footer class="border-t border-slate-200">
        <div class="container py-10 grid gap-8 md:grid-cols-4">
            <div class="space-y-2">
                <div class="flex items-center gap-2 font-semibold">
                    <img 
                        :src="logoUrl" 
                        alt="TicketsAir Logo" 
                        class="h-8 w-auto"
                    >
                    <span>TicketsAir</span>
                </div>
                <p class="text-sm text-slate-600">
                    La forma moderna de encontrar y gestionar tus vuelos.
                </p>
            </div>

            <div>
                <h4 class="font-semibold mb-3">Compañía</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="hover:underline">Nosotros</a></li>
                    <li><a href="#" class="hover:underline">Blog</a></li>
                    <li><a href="#" class="hover:underline">Carreras</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold mb-3">Ayuda</h4>
                <ul class="space-y-2 text-sm">
                    <li>
                        <a href="#soporte" class="hover:underline">Soporte</a>
                    </li>
                    <li>
                        <a href="#" class="hover:underline"
                            >Estado del servicio</a
                        >
                    </li>
                    <li><a href="#" class="hover:underline">Políticas</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold mb-3">Suscríbete</h4>
                <div class="flex gap-2">
                    <input
                        type="email"
                        placeholder="tu@email"
                        class="flex-1 h-11 rounded-xl border border-slate-300 bg-white px-3"
                    />
                    <button
                        class="px-4 h-11 rounded-xl bg-gradient-to-tr from-blue-600 to-cyan-400 text-white"
                    >
                        Enviar
                    </button>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-200">
            <div
                class="container py-4 text-xs text-slate-500 flex items-center justify-between"
            >
                <span
                    >© {{ year }} TicketsAir. Todos los derechos
                    reservados.</span
                >
                <div class="flex gap-4">
                    <a href="#" class="hover:underline">Privacidad</a>
                    <a href="#" class="hover:underline">Términos</a>
                </div>
            </div>
        </div>
    </footer>
</template>

<script setup>
const logoUrl = '/logo.png';
const year = new Date().getFullYear();
</script>
