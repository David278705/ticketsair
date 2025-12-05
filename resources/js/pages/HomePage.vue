<template>
    <main>
        <Hero />
        <FlightSearch ref="flightSearchRef" />
        <CheckinBox class="mt-16" />
        <!-- <PopularRoutes class="mt-16" /> -->
        <NewsModule class="mt-16" />
        <Benefits class="mt-16" />
        <Testimonials class="mt-16" />
    </main>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import Hero from "../components/landing/Hero.vue";
import FlightSearch from "../components/landing/FlightSearch.vue";
import NewsModule from "../components/landing/NewsModule.vue";
import PopularRoutes from "../components/landing/PopularRoutes.vue";
import Benefits from "../components/landing/Benefits.vue";
import Testimonials from "../components/landing/Testimonials.vue";
import CheckinBox from "../components/checkin/CheckinBox.vue";

const route = useRoute();
const flightSearchRef = ref(null);

onMounted(async () => {
    // Verificar si hay un flight_id en la URL
    const flightId = route.query.flight_id;
    if (flightId && flightSearchRef.value) {
        // Esperar un momento para que el componente esté completamente montado
        setTimeout(() => {
            if (flightSearchRef.value.openFlightModal) {
                flightSearchRef.value.openFlightModal(flightId);
            }
        }, 500);
    }
});
</script>
