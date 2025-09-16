<template>
    <div class="min-h-screen bg-slate-50 dark:bg-slate-900">
        <!-- Header -->
        <div
            class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 px-6 py-4"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-bold text-slate-900 dark:text-white"
                    >
                        Mensajería
                    </h1>
                    <p class="text-slate-600 dark:text-slate-400">
                        Gestionar mensajes de clientes
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <div
                        class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-3 py-1 rounded-full text-sm font-medium"
                    >
                        {{ unreadCount }} mensajes sin leer
                    </div>
                    <button
                        @click="loadMessages"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
                    >
                        <RefreshCw
                            class="w-5 h-5"
                            :class="{ 'animate-spin': loading }"
                        />
                        Actualizar
                    </button>
                </div>
            </div>
        </div>

        <div class="flex h-[calc(100vh-88px)]">
            <!-- Lista de conversaciones -->
            <div
                class="w-1/3 bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 overflow-y-auto"
            >
                <div
                    class="p-4 border-b border-slate-200 dark:border-slate-700"
                >
                    <div class="relative">
                        <Search
                            class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-slate-400"
                        />
                        <input
                            v-model="searchQuery"
                            @input="debouncedSearch"
                            placeholder="Buscar conversaciones..."
                            class="w-full pl-10 pr-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>
                </div>

                <div v-if="loading" class="p-4 text-center">
                    <div
                        class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"
                    ></div>
                </div>

                <div
                    v-else-if="conversations.length"
                    class="divide-y divide-slate-200 dark:divide-slate-700"
                >
                    <div
                        v-for="conversation in conversations"
                        :key="conversation.id"
                        @click="selectConversation(conversation)"
                        class="p-4 hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer transition-colors"
                        :class="{
                            'bg-blue-50 dark:bg-blue-900/30':
                                selectedConversation?.id === conversation.id,
                        }"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div
                                    class="h-10 w-10 rounded-full bg-slate-300 dark:bg-slate-600 flex items-center justify-center"
                                >
                                    <User
                                        class="w-5 h-5 text-slate-600 dark:text-slate-300"
                                    />
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p
                                        class="text-sm font-medium text-slate-900 dark:text-white truncate"
                                    >
                                        {{ conversation.from.first_name }}
                                        {{ conversation.from.last_name }}
                                    </p>
                                    <div class="flex items-center gap-1">
                                        <span
                                            v-if="!conversation.is_read"
                                            class="h-2 w-2 bg-blue-600 rounded-full"
                                        ></span>
                                        <time
                                            class="text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            {{
                                                formatTime(conversation.sent_at)
                                            }}
                                        </time>
                                    </div>
                                </div>
                                <p
                                    class="text-sm text-slate-600 dark:text-slate-400 truncate"
                                >
                                    {{ conversation.subject }}
                                </p>
                                <p
                                    class="text-xs text-slate-500 dark:text-slate-500 truncate mt-1"
                                >
                                    {{ conversation.body }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-else
                    class="p-4 text-center text-slate-500 dark:text-slate-400"
                >
                    No hay conversaciones disponibles.
                </div>
            </div>

            <!-- Panel de conversación -->
            <div class="flex-1 flex flex-col">
                <div v-if="selectedConversation" class="flex flex-col h-full">
                    <!-- Header de conversación -->
                    <div
                        class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 px-6 py-4"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <h3
                                    class="text-lg font-medium text-slate-900 dark:text-white"
                                >
                                    {{ selectedConversation.from.first_name }}
                                    {{ selectedConversation.from.last_name }}
                                </h3>
                                <p
                                    class="text-sm text-slate-600 dark:text-slate-400"
                                >
                                    {{ selectedConversation.from.email }}
                                </p>
                            </div>
                            <button
                                v-if="!selectedConversation.is_read"
                                @click="markAsRead(selectedConversation)"
                                class="text-sm bg-green-600 text-white px-3 py-1 rounded-lg hover:bg-green-700 transition-colors"
                            >
                                Marcar como leído
                            </button>
                        </div>
                    </div>

                    <!-- Mensaje original -->
                    <div
                        class="flex-1 overflow-y-auto p-6 space-y-4 bg-slate-50 dark:bg-slate-900"
                    >
                        <div
                            class="bg-white dark:bg-slate-800 rounded-lg p-4 shadow-sm"
                        >
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="h-8 w-8 rounded-full bg-slate-300 dark:bg-slate-600 flex items-center justify-center"
                                    >
                                        <User
                                            class="w-4 h-4 text-slate-600 dark:text-slate-300"
                                        />
                                    </div>
                                    <span
                                        class="font-medium text-slate-900 dark:text-white"
                                    >
                                        {{
                                            selectedConversation.from.first_name
                                        }}
                                        {{
                                            selectedConversation.from.last_name
                                        }}
                                    </span>
                                </div>
                                <time
                                    class="text-sm text-slate-500 dark:text-slate-400"
                                >
                                    {{
                                        formatDateTime(
                                            selectedConversation.sent_at
                                        )
                                    }}
                                </time>
                            </div>
                            <h4
                                class="font-medium text-slate-900 dark:text-white mb-2"
                            >
                                {{ selectedConversation.subject }}
                            </h4>
                            <div
                                class="text-slate-700 dark:text-slate-300 whitespace-pre-wrap"
                            >
                                {{ selectedConversation.body }}
                            </div>
                        </div>

                        <!-- Conversación completa -->
                        <div v-if="fullConversation.length" class="space-y-4">
                            <div
                                v-for="message in fullConversation"
                                :key="message.id"
                                class="bg-white dark:bg-slate-800 rounded-lg p-4 shadow-sm"
                                :class="
                                    message.from.id === auth.user?.id
                                        ? 'ml-8'
                                        : 'mr-8'
                                "
                            >
                                <div
                                    class="flex items-center justify-between mb-3"
                                >
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="h-8 w-8 rounded-full flex items-center justify-center"
                                            :class="
                                                message.from.id ===
                                                auth.user?.id
                                                    ? 'bg-blue-100 dark:bg-blue-900'
                                                    : 'bg-slate-300 dark:bg-slate-600'
                                            "
                                        >
                                            <User
                                                class="w-4 h-4"
                                                :class="
                                                    message.from.id ===
                                                    auth.user?.id
                                                        ? 'text-blue-600 dark:text-blue-300'
                                                        : 'text-slate-600 dark:text-slate-300'
                                                "
                                            />
                                        </div>
                                        <span
                                            class="font-medium text-slate-900 dark:text-white"
                                        >
                                            {{ message.from.first_name }}
                                            {{ message.from.last_name }}
                                            <span
                                                v-if="
                                                    message.from.id ===
                                                    auth.user?.id
                                                "
                                                class="text-xs text-blue-600 dark:text-blue-400"
                                                >(Tú)</span
                                            >
                                        </span>
                                    </div>
                                    <time
                                        class="text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        {{ formatDateTime(message.sent_at) }}
                                    </time>
                                </div>
                                <div
                                    class="text-slate-700 dark:text-slate-300 whitespace-pre-wrap"
                                >
                                    {{ message.body }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form de respuesta -->
                    <div
                        class="bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 p-6"
                    >
                        <form @submit.prevent="sendReply" class="space-y-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
                                >
                                    Respuesta
                                </label>
                                <textarea
                                    v-model="replyForm.body"
                                    rows="4"
                                    required
                                    placeholder="Escribe tu respuesta..."
                                    class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                                />
                            </div>
                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="
                                        !replyForm.body.trim() || sendingReply
                                    "
                                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2 disabled:opacity-50"
                                >
                                    <Send class="w-4 h-4" />
                                    {{
                                        sendingReply
                                            ? "Enviando..."
                                            : "Enviar Respuesta"
                                    }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div
                    v-else
                    class="flex-1 flex items-center justify-center text-slate-500 dark:text-slate-400"
                >
                    <div class="text-center">
                        <MessageSquare
                            class="w-16 h-16 mx-auto mb-4 text-slate-300 dark:text-slate-600"
                        />
                        <p class="text-lg font-medium">
                            Selecciona una conversación
                        </p>
                        <p class="text-sm">
                            Elige una conversación de la lista para ver los
                            mensajes
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from "vue";
import { Search, RefreshCw, User, Send, MessageSquare } from "lucide-vue-next";
import { api } from "../../lib/api";
import { useAuth } from "../../stores/auth";
import { debounce } from "lodash";

const auth = useAuth();

// Estado
const loading = ref(false);
const sendingReply = ref(false);
const conversations = ref([]);
const selectedConversation = ref(null);
const fullConversation = ref([]);
const searchQuery = ref("");

// Form de respuesta
const replyForm = reactive({
    body: "",
});

// Contador de mensajes no leídos
const unreadCount = computed(() => {
    return conversations.value.filter((c) => !c.is_read).length;
});

// Métodos
const loadMessages = async () => {
    loading.value = true;
    try {
        const { data } = await api.get("/messages/admin");
        if (data.status === "success") {
            conversations.value = data.data;
        }
    } catch (error) {
        console.error("Error loading messages:", error);
    } finally {
        loading.value = false;
    }
};

const selectConversation = async (conversation) => {
    selectedConversation.value = conversation;

    try {
        const { data } = await api.get(
            `/messages/conversation/${conversation.from.id}`
        );
        if (data.status === "success") {
            fullConversation.value = data.data.filter(
                (msg) => msg.id !== conversation.id
            );
        }
    } catch (error) {
        console.error("Error loading conversation:", error);
        fullConversation.value = [];
    }
};

const markAsRead = async (conversation) => {
    try {
        const { data } = await api.patch(`/messages/${conversation.id}/read`);
        if (data.status === "success") {
            conversation.is_read = true;
            await loadMessages(); // Recargar para actualizar contador
        }
    } catch (error) {
        console.error("Error marking as read:", error);
        alert("Error al marcar como leído");
    }
};

const sendReply = async () => {
    if (!selectedConversation.value || !replyForm.body.trim()) return;

    sendingReply.value = true;
    try {
        const { data } = await api.post("/messages/reply", {
            original_message_id: selectedConversation.value.id,
            to_user_id: selectedConversation.value.from.id,
            body: replyForm.body,
        });

        if (data.status === "success") {
            // Agregar la respuesta a la conversación
            fullConversation.value.push(data.data);
            replyForm.body = "";

            // Marcar original como leído si no lo estaba
            if (!selectedConversation.value.is_read) {
                await markAsRead(selectedConversation.value);
            }

            alert("Respuesta enviada exitosamente");
        }
    } catch (error) {
        console.error("Error sending reply:", error);
        alert("Error al enviar respuesta");
    } finally {
        sendingReply.value = false;
    }
};

const debouncedSearch = debounce(() => {
    // Implementar búsqueda filtrada localmente
    // En una implementación real, esto podría ser una llamada al servidor
}, 300);

// Utilidades
const formatTime = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffInHours = (now - date) / (1000 * 60 * 60);

    if (diffInHours < 24) {
        return date.toLocaleTimeString("es-ES", {
            hour: "2-digit",
            minute: "2-digit",
        });
    } else if (diffInHours < 168) {
        // 7 días
        return date.toLocaleDateString("es-ES", {
            weekday: "short",
        });
    } else {
        return date.toLocaleDateString("es-ES", {
            day: "2-digit",
            month: "2-digit",
        });
    }
};

const formatDateTime = (dateString) => {
    return new Date(dateString).toLocaleString("es-ES", {
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
