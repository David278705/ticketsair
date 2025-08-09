<template>
    <main>
        <Hero />
        <FlightSearch />
        <PopularRoutes class="mt-16" />
        <Promotions class="mt-16" />
        <Benefits class="mt-16" />
        <Testimonials class="mt-16" />
        <!-- opcional -->
        <CheckinBox class="mt-16" />
    </main>
</template>

<script setup>
import Hero from "../components/landing/Hero.vue";
import FlightSearch from "../components/landing/FlightSearch.vue";
import PopularRoutes from "../components/landing/PopularRoutes.vue";
import Promotions from "../components/landing/Promotions.vue";
import Benefits from "../components/landing/Benefits.vue";
import Testimonials from "../components/landing/Testimonials.vue";
import CheckinBox from "../components/checkin/CheckinBox.vue";
</script>
