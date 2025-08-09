import { defineStore } from "pinia";

export const useUi = defineStore("ui", {
    state: () => ({
        authOpen: false, // controla el AuthModal
        authMode: "login", // 'login' | 'register'
    }),
    actions: {
        openAuth(mode = "login") {
            this.authMode = mode;
            this.authOpen = true;
        },
        closeAuth() {
            this.authOpen = false;
        },
    },
});
