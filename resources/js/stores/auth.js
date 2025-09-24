import { defineStore } from "pinia";
import { api, setAuthToken, getCsrfCookie } from "../lib/api";

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
                // Get CSRF cookie before making the request
                await getCsrfCookie();
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
                // Get CSRF cookie before making the login request
                await getCsrfCookie();
                const { data } = await api.post("/login", { email, password });
                this.token = data.token;
                localStorage.setItem("token", this.token);
                setAuthToken(this.token);
                this.user = data.user;
                
                // Return both user data and completion status
                return {
                    user: data.user,
                    requires_completion: data.requires_completion || false
                };
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
                // Get CSRF cookie before making the request
                await getCsrfCookie();
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
                // Get CSRF cookie before making the request
                await getCsrfCookie();
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
                // Get CSRF cookie before making the request
                await getCsrfCookie();
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
