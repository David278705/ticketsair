import { defineStore } from "pinia";
import { api, setAuthToken } from "../lib/api";

export const useAuth = defineStore("auth", {
    state: () => ({
        user: null,
        token: localStorage.getItem("token") || null,
        loading: false,
        error: null,
    }),
    actions: {
        async register(payload) {
            this.loading = true;
            this.error = null;
            try {
                await api.post("/register", payload);
                return await this.login({
                    email: payload.email,
                    password: payload.password,
                });
            } catch (e) {
                this.error = e.response?.data || e.message;
                throw e;
            } finally {
                this.loading = false;
            }
        },
        async login({ email, password }) {
            this.loading = true;
            this.error = null;
            try {
                const { data } = await api.post("/login", { email, password });
                this.token = data.token;
                localStorage.setItem("token", this.token);
                setAuthToken(this.token);
                this.user = data.user;
                return data.user;
            } catch (e) {
                this.error = e.response?.data || e.message;
                throw e;
            } finally {
                this.loading = false;
            }
        },
        async me() {
            if (!this.token) return;
            setAuthToken(this.token);
            try {
                const { data } = await api.get("/me");
                this.user = data;
            } catch {
                this.logout();
            }
        },
        logout() {
            this.user = null;
            this.token = null;
            localStorage.removeItem("token");
            setAuthToken(null);
        },
        bootstrap() {
            if (this.token) setAuthToken(this.token);
        },
        async forgotPassword(email) {
            this.loading = true;
            this.error = null;
            try {
                const { data } = await api.post("/forgot-password", { email });
                return data;
            } catch (e) {
                this.error = e.response?.data || e.message;
                throw e;
            } finally {
                this.loading = false;
            }
        },
        async resetPassword(payload) {
            this.loading = true;
            this.error = null;
            try {
                const { data } = await api.post("/reset-password", payload);
                return data;
            } catch (e) {
                this.error = e.response?.data || e.message;
                throw e;
            } finally {
                this.loading = false;
            }
        },
        async checkResetToken(token, email) {
            try {
                const { data } = await api.post("/check-reset-token", {
                    token,
                    email,
                });
                return data.user;
            } catch (e) {
                throw e;
            }
        },
    },
});
