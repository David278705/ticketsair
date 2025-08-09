import axios from "axios";
export const API_URL =
    import.meta.env.VITE_API_URL || "http://127.0.0.1:8000/api";
export const api = axios.create({ baseURL: API_URL });
export function setAuthToken(token) {
    if (token) api.defaults.headers.common.Authorization = `Bearer ${token}`;
    else delete api.defaults.headers.common.Authorization;
}
