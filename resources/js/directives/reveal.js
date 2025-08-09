export default {
    mounted(el) {
        el.classList.add("opacity-0", "translate-y-4");
        const on = new IntersectionObserver(
            ([entry]) => {
                if (entry.isIntersecting) {
                    el.classList.add("transition", "duration-700", "ease-out");
                    el.classList.remove("opacity-0", "translate-y-4");
                    el.classList.add("opacity-100", "translate-y-0");
                    on.unobserve(el);
                }
            },
            { threshold: 0.15 }
        );
        on.observe(el);
    },
};
