import axios from "axios";

export const API_URL = import.meta.env.VITE_API_URL || "http://127.0.0.1:8000/api";
export const BASE_URL = import.meta.env.VITE_APP_URL || "http://127.0.0.1:8000";

// Configure axios for Sanctum SPA authentication
export const api = axios.create({ 
    baseURL: API_URL,
    withCredentials: true, // Important for CSRF cookies
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    }
});

// Function to get CSRF token before making authenticated requests
export async function getCsrfCookie() {
    try {
        await axios.get(`${BASE_URL}/sanctum/csrf-cookie`, {
            withCredentials: true,
        });
    } catch (error) {
        console.error('Error getting CSRF cookie:', error);
    }
}

export function setAuthToken(token) {
    if (token) api.defaults.headers.common.Authorization = `Bearer ${token}`;
    else delete api.defaults.headers.common.Authorization;
}
