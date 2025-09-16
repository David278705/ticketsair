<template>
    <div class="min-h-screen bg-slate-50 dark:bg-slate-900 py-8">
        <div class="container max-w-2xl">
            <!-- Header -->
            <div
                class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6 mb-6"
            >
                <h1
                    class="text-2xl font-bold text-slate-900 dark:text-white mb-2"
                >
                    Contactar Soporte
                </h1>
                <p class="text-slate-600 dark:text-slate-400">
                    Envía un mensaje a nuestro equipo de soporte. Responderemos
                    lo antes posible.
                </p>
            </div>

            <!-- Formulario -->
            <div
                class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6"
            >
                <form @submit.prevent="submitMessage" class="space-y-4">
                    <!-- Asunto -->
                    <div>
                        <label
                            class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
                        >
                            Asunto *
                        </label>
                        <input
                            v-model="form.subject"
                            type="text"
                            required
                            placeholder="Describe brevemente tu consulta..."
                            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{ 'border-red-500': errors.subject }"
                        />
                        <p
                            v-if="errors.subject"
                            class="text-red-500 text-xs mt-1"
                        >
                            {{ errors.subject[0] }}
                        </p>
                    </div>

                    <!-- Mensaje -->
                    <div>
                        <label
                            class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
                        >
                            Mensaje *
                        </label>
                        <textarea
                            v-model="form.body"
                            rows="6"
                            required
                            placeholder="Describe detalladamente tu consulta o problema..."
                            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                            :class="{ 'border-red-500': errors.body }"
                        />
                        <p v-if="errors.body" class="text-red-500 text-xs mt-1">
                            {{ errors.body[0] }}
                        </p>
                    </div>

                    <!-- Información del usuario -->
                    <div class="bg-slate-50 dark:bg-slate-700 rounded-lg p-4">
                        <p
                            class="text-sm text-slate-600 dark:text-slate-400 mb-2"
                        >
                            Información de contacto:
                        </p>
                        <p
                            class="text-sm font-medium text-slate-900 dark:text-white"
                        >
                            {{ auth.user?.first_name }}
                            {{ auth.user?.last_name }}
                        </p>
                        <p class="text-sm text-slate-600 dark:text-slate-400">
                            {{ auth.user?.email }}
                        </p>
                    </div>

                    <!-- Botón enviar -->
                    <div class="flex justify-end">
                        <button
                            type="submit"
                            :disabled="
                                loading ||
                                !form.subject.trim() ||
                                !form.body.trim()
                            "
                            class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2 disabled:opacity-50"
                        >
                            <Send class="w-4 h-4" />
                            {{ loading ? "Enviando..." : "Enviar Mensaje" }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Mensajes enviados -->
            <div v-if="messages.length" class="mt-6">
                <h2
                    class="text-lg font-semibold text-slate-900 dark:text-white mb-4"
                >
                    Mis Mensajes
                </h2>
                <div class="space-y-4">
                    <div
                        v-for="message in messages"
                        :key="message.id"
                        class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-4"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <h3
                                class="font-medium text-slate-900 dark:text-white"
                            >
                                {{ message.subject }}
                            </h3>
                            <div class="flex items-center gap-2">
                                <span
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                    :class="
                                        message.is_read
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                            : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                                    "
                                >
                                    {{
                                        message.is_read ? "Leído" : "Pendiente"
                                    }}
                                </span>
                                <time
                                    class="text-sm text-slate-500 dark:text-slate-400"
                                >
                                    {{ formatDate(message.sent_at) }}
                                </time>
                            </div>
                        </div>
                        <p class="text-slate-700 dark:text-slate-300 text-sm">
                            {{ message.body }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import { Send } from "lucide-vue-next";
import { api } from "../lib/api";
import { useAuth } from "../stores/auth";

const auth = useAuth();

// Estado
const loading = ref(false);
const messages = ref([]);
const errors = ref({});

// Formulario
const form = reactive({
    subject: "",
    body: "",
});

// Métodos
const submitMessage = async () => {
    if (!form.subject.trim() || !form.body.trim()) return;

    loading.value = true;
    errors.value = {};

    try {
        const { data } = await api.post("/messages/send-to-admin", {
            subject: form.subject,
            body: form.body,
        });

        if (data.status === "success") {
            // Limpiar formulario
            form.subject = "";
            form.body = "";

            // Recargar mensajes
            await loadMessages();

            alert(
                "Mensaje enviado exitosamente. Recibirás una respuesta por email."
            );
        }
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
        } else {
            alert(error.response?.data?.message || "Error al enviar mensaje");
        }
    } finally {
        loading.value = false;
    }
};

const loadMessages = async () => {
    try {
        const { data } = await api.get("/messages");
        if (data.status === "success") {
            messages.value = data.data;
        }
    } catch (error) {
        console.error("Error loading messages:", error);
    }
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("es-ES", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

onMounted(() => {
    loadMessages();
});
</script>
