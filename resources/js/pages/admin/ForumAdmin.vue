<template>
    <div class="min-h-screen bg-slate-50">
        <!-- Header -->
        <div class="bg-white border-b border-slate-200 px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">
                        Gestión de Foros
                    </h1>
                    <p class="text-slate-600">Administra conversaciones con usuarios</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium">
                        {{ totalUnread }} sin leer
                    </div>
                    <button
                        @click="refreshAll"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
                    >
                        <RefreshCw class="w-5 h-5" :class="{ 'animate-spin': refreshing }" />
                        Actualizar
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white border-b border-slate-200">
            <nav class="flex px-6">
                <button
                    @click="activeTab = 'public'"
                    :class="[
                        activeTab === 'public'
                            ? 'border-blue-500 text-blue-600'
                            : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300',
                        'py-4 px-6 border-b-2 font-medium text-sm transition-colors flex items-center gap-2'
                    ]"
                >
                    <MessageCircle class="w-5 h-5" />
                    Foro Público
                </button>
                <button
                    @click="activeTab = 'private'"
                    :class="[
                        activeTab === 'private'
                            ? 'border-blue-500 text-blue-600'
                            : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300',
                        'py-4 px-6 border-b-2 font-medium text-sm transition-colors flex items-center gap-2'
                    ]"
                >
                    <Lock class="w-5 h-5" />
                    Soporte Privado
                    <span v-if="privateUnread > 0" class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-500 rounded-full">
                        {{ privateUnread }}
                    </span>
                </button>
            </nav>
        </div>

        <div class="flex">
            <!-- Public Forum -->
            <div v-if="activeTab === 'public'" class="flex-1 bg-white">
                <div class="p-6 border-b border-slate-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-slate-900">Foro Público</h2>
                            <p class="text-sm text-slate-600 mt-1">Responde consultas públicas de los usuarios</p>
                        </div>
                        <button
                            @click="loadPublicForum"
                            class="p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors"
                        >
                            <RefreshCw class="w-5 h-5" :class="{ 'animate-spin': loadingPublic }" />
                        </button>
                    </div>
                </div>

                <!-- Messages -->
                <div ref="publicMessagesContainer" class="p-6 space-y-6 bg-slate-50 min-h-[400px]">
                    <div v-if="loadingPublic" class="flex justify-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    </div>

                    <div v-else-if="publicMessages.length === 0" class="text-center py-8 text-slate-500">
                        <MessageCircle class="w-12 h-12 mx-auto mb-3 text-slate-300" />
                        <p>No hay mensajes en el foro público</p>
                    </div>

                    <div v-else class="space-y-6">
                        <div v-for="message in publicMessages" :key="message.id" class="flex gap-3 animate-fade-in">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold shadow-sm"
                                    :class="isAdmin(message.from) ? 'bg-blue-600' : 'bg-slate-400'">
                                    {{ getInitials(message.from) }}
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-slate-900">
                                                {{ message.from.first_name }} {{ message.from.last_name }}
                                            </span>
                                            <span class="text-sm text-slate-500">
                                                ({{ message.from.email }})
                                            </span>
                                            <span v-if="isAdmin(message.from)" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                <Shield class="w-3 h-3 mr-1" />
                                                {{ message.from.role?.name === 'root' ? 'Root' : 'Admin' }}
                                            </span>
                                        </div>
                                        <time class="text-xs text-slate-500">
                                            {{ formatTime(message.sent_at || message.created_at) }}
                                        </time>
                                    </div>
                                    <p class="text-slate-700 whitespace-pre-wrap leading-relaxed">{{ message.body }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Message Input -->
                <div class="p-6 border-t border-slate-200 bg-white">
                    <form @submit.prevent="sendPublicMessage" class="flex gap-3">
                        <textarea
                            v-model="publicMessageText"
                            @keydown.enter.ctrl="sendPublicMessage"
                            placeholder="Escribe tu respuesta... (Ctrl+Enter para enviar)"
                            rows="2"
                            class="flex-1 px-4 py-2 border border-slate-300 rounded-lg bg-white text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                        ></textarea>
                        <button
                            type="submit"
                            :disabled="!publicMessageText.trim() || sendingPublic"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <Send class="w-4 h-4" />
                            Enviar
                        </button>
                    </form>
                </div>
            </div>

            <!-- Private Support -->
            <div v-else class="flex flex-1 min-h-screen">
                <!-- List of private threads -->
                <div class="w-1/3 bg-white border-r border-slate-200">
                    <div class="p-4 border-b border-slate-200 sticky top-0 bg-white z-10">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Buscar conversaciones..."
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg bg-white text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>

                    <div v-if="loadingThreads" class="p-4 text-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                    </div>

                    <div v-else-if="filteredThreads.length === 0" class="p-4 text-center text-slate-500">
                        <Inbox class="w-12 h-12 mx-auto mb-3 text-slate-300" />
                        <p>No hay conversaciones privadas</p>
                    </div>

                    <div v-else class="divide-y divide-slate-200">
                        <div
                            v-for="thread in filteredThreads"
                            :key="thread.id"
                            @click="selectThread(thread)"
                            class="p-4 hover:bg-slate-50 cursor-pointer transition-colors"
                            :class="{
                                'bg-blue-50 border-l-4 border-l-blue-500': selectedThread?.id === thread.id,
                                'border-l-4 border-l-transparent': selectedThread?.id !== thread.id
                            }"
                        >
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full bg-slate-300 flex items-center justify-center text-white font-semibold">
                                        {{ getInitials(thread.user) }}
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <h3 class="font-medium text-slate-900 truncate">
                                            {{ thread.user.first_name }} {{ thread.user.last_name }}
                                        </h3>
                                        <span v-if="thread.unread_count > 0" class="flex-shrink-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold text-white bg-red-500 rounded-full">
                                            {{ thread.unread_count }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-slate-600 truncate">{{ thread.title }}</p>
                                    <p v-if="thread.latest_message" class="text-xs text-slate-500 truncate mt-1">
                                        {{ thread.latest_message.body }}
                                    </p>
                                    <time class="text-xs text-slate-400 mt-1 block">
                                        {{ formatTime(thread.last_message_at) }}
                                    </time>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Selected thread messages -->
                <div class="flex-1 bg-white" v-if="selectedThread">
                    <div class="p-6 border-b border-slate-200 bg-white sticky top-0 z-10">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-full bg-slate-300 flex items-center justify-center text-white font-semibold text-lg">
                                    {{ getInitials(selectedThread.user) }}
                                </div>
                                <div>
                                    <h2 class="text-xl font-semibold text-slate-900">
                                        {{ selectedThread.user.first_name }} {{ selectedThread.user.last_name }}
                                    </h2>
                                    <p class="text-sm text-slate-600">{{ selectedThread.user.email }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    v-if="selectedThread.status === 'open'"
                                    @click="closeThread(selectedThread)"
                                    class="px-3 py-1 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                >
                                    <X class="w-4 h-4 inline-block mr-1" />
                                    Cerrar
                                </button>
                                <span v-else class="px-3 py-1 text-sm bg-slate-100 text-slate-600 rounded-lg">
                                    Cerrado
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div ref="privateMessagesContainer" class="p-6 space-y-5 bg-slate-50 min-h-[400px]">
                        <div v-if="loadingMessages" class="flex justify-center py-8">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        </div>

                        <div v-else class="space-y-5">
                            <div v-for="message in selectedThreadMessages" :key="message.id" class="flex gap-3 animate-fade-in"
                                :class="{ 'flex-row-reverse': isMyMessage(message) }">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold shadow-sm"
                                        :class="isAdmin(message.from) ? 'bg-blue-600' : 'bg-slate-400'">
                                        {{ getInitials(message.from) }}
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0 max-w-[75%]">
                                    <div class="rounded-xl p-4 shadow-sm"
                                        :class="isMyMessage(message) ? 'bg-blue-600 text-white' : 'bg-white border border-slate-100'">
                                        <div class="flex items-center justify-between mb-2" :class="{ 'flex-row-reverse': isMyMessage(message) }">
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold text-sm" :class="isMyMessage(message) ? 'text-white' : 'text-slate-900'">
                                                    {{ message.from.first_name }} {{ message.from.last_name }}
                                                </span>
                                                <span v-if="isAdmin(message.from)" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                                    :class="isMyMessage(message) ? 'bg-white/20 text-white' : 'bg-blue-100 text-blue-800'">
                                                    <Shield class="w-3 h-3 mr-1" />
                                                    {{ message.from.role?.name === 'root' ? 'Root' : 'Admin' }}
                                                </span>
                                            </div>
                                            <time class="text-xs ml-2" :class="isMyMessage(message) ? 'text-blue-100' : 'text-slate-500'">
                                                {{ formatTime(message.sent_at || message.created_at) }}
                                            </time>
                                        </div>
                                        <p class="whitespace-pre-wrap leading-relaxed text-sm" :class="isMyMessage(message) ? 'text-white' : 'text-slate-700'">
                                            {{ message.body }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Message Input -->
                    <div class="p-6 border-t border-slate-200 bg-white">
                        <form @submit.prevent="sendPrivateMessage" class="flex gap-3">
                            <textarea
                                v-model="privateMessageText"
                                @keydown.enter.ctrl="sendPrivateMessage"
                                placeholder="Escribe tu respuesta... (Ctrl+Enter para enviar)"
                                rows="2"
                                :disabled="selectedThread.status === 'closed'"
                                class="flex-1 px-4 py-2 border border-slate-300 rounded-lg bg-white text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none disabled:bg-slate-100 disabled:cursor-not-allowed"
                            ></textarea>
                            <button
                                type="submit"
                                :disabled="!privateMessageText.trim() || sendingPrivate || selectedThread.status === 'closed'"
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                            >
                                <Send class="w-4 h-4" />
                                Enviar
                            </button>
                        </form>
                    </div>
                </div>

                <!-- No thread selected -->
                <div v-else class="flex-1 flex items-center justify-center bg-slate-50">
                    <div class="text-center">
                        <MessageCircle class="w-16 h-16 mx-auto mb-4 text-slate-300" />
                        <p class="text-lg font-medium text-slate-900">Selecciona una conversación</p>
                        <p class="text-sm text-slate-600">Elige una conversación de la lista para ver los mensajes</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch, nextTick, onUnmounted } from 'vue';
import { MessageCircle, Lock, Send, RefreshCw, Shield, X, Inbox } from 'lucide-vue-next';
import { api } from '../../lib/api';
import { useAuth } from '../../stores/auth';

const auth = useAuth();

// Estado
const activeTab = ref('public');
const refreshing = ref(false);
const loadingPublic = ref(false);
const loadingThreads = ref(false);
const loadingMessages = ref(false);
const sendingPublic = ref(false);
const sendingPrivate = ref(false);
const searchQuery = ref('');

// Foro público
const publicThread = ref(null);
const publicMessages = computed(() => publicThread.value?.messages || []);
const publicMessageText = ref('');

// Hilos privados
const privateThreads = ref([]);
const selectedThread = ref(null);
const selectedThreadMessages = computed(() => selectedThread.value?.messages || []);
const privateMessageText = ref('');

// Contador de no leídos
const privateUnread = computed(() => {
    return privateThreads.value.reduce((sum, thread) => sum + (thread.unread_count || 0), 0);
});

const totalUnread = computed(() => {
    return privateUnread.value;
});

// Hilos filtrados
const filteredThreads = computed(() => {
    if (!searchQuery.value) return privateThreads.value;
    
    const query = searchQuery.value.toLowerCase();
    return privateThreads.value.filter(thread => {
        const userName = `${thread.user.first_name} ${thread.user.last_name}`.toLowerCase();
        const email = thread.user.email.toLowerCase();
        const title = thread.title?.toLowerCase() || '';
        return userName.includes(query) || email.includes(query) || title.includes(query);
    });
});

// Refs para scroll
const publicMessagesContainer = ref(null);
const privateMessagesContainer = ref(null);

// Métodos
const loadPublicForum = async (isAutoRefresh = false) => {
    if (!isAutoRefresh) {
        loadingPublic.value = true;
    }
    
    try {
        // Si es auto-refresh y ya tenemos mensajes, solo pedir los nuevos
        const params = {};
        if (isAutoRefresh && publicMessages.value.length > 0) {
            const lastMessageId = publicMessages.value[publicMessages.value.length - 1].id;
            params.since_message_id = lastMessageId;
        }
        
        const { data } = await api.get('/forum/public', { params });
        
        if (data.status === 'success') {
            if (isAutoRefresh && params.since_message_id) {
                // Solo agregar los mensajes nuevos
                if (data.data.new_messages && data.data.new_messages.length > 0) {
                    publicThread.value.messages.push(...data.data.new_messages);
                }
            } else {
                // Carga inicial o manual: reemplazar todo
                publicThread.value = data.data;
                await nextTick();
                scrollToBottom('public');
            }
        }
    } catch (error) {
        console.error('Error loading public forum:', error);
    } finally {
        if (!isAutoRefresh) {
            loadingPublic.value = false;
        }
    }
};

const loadPrivateThreads = async (isAutoRefresh = false) => {
    if (!isAutoRefresh) {
        loadingThreads.value = true;
    }
    
    try {
        const { data } = await api.get('/admin/forum/private-threads');
        if (data.status === 'success') {
            if (isAutoRefresh && privateThreads.value.length > 0) {
                // En auto-refresh, actualizar contadores sin re-renderizar toda la lista
                const newThreads = data.data.data || [];
                privateThreads.value.forEach((thread, index) => {
                    const updatedThread = newThreads.find(t => t.id === thread.id);
                    if (updatedThread) {
                        // Solo actualizar propiedades que pueden cambiar
                        thread.unread_count = updatedThread.unread_count;
                        thread.last_message_at = updatedThread.last_message_at;
                    }
                });
            } else {
                // Carga inicial o manual
                privateThreads.value = data.data.data || [];
            }
        }
    } catch (error) {
        console.error('Error loading private threads:', error);
    } finally {
        if (!isAutoRefresh) {
            loadingThreads.value = false;
        }
    }
};

const selectThread = async (thread, isAutoRefresh = false) => {
    if (!isAutoRefresh) {
        selectedThread.value = thread;
        loadingMessages.value = true;
    }
    
    try {
        // Si es auto-refresh y ya tenemos mensajes, solo pedir los nuevos
        const params = {};
        if (isAutoRefresh && selectedThreadMessages.value.length > 0) {
            const lastMessageId = selectedThreadMessages.value[selectedThreadMessages.value.length - 1].id;
            params.since_message_id = lastMessageId;
        }
        
        const { data } = await api.get(`/forum/threads/${thread.id}`, { params });
        
        if (data.status === 'success') {
            if (isAutoRefresh && params.since_message_id) {
                // Solo agregar los mensajes nuevos
                if (data.data.new_messages && data.data.new_messages.length > 0) {
                    selectedThread.value.messages.push(...data.data.new_messages);
                    thread.unread_count = 0;
                }
            } else {
                // Carga inicial o manual: reemplazar todo
                selectedThread.value = data.data;
                await nextTick();
                scrollToBottom('private');
                thread.unread_count = 0;
            }
        }
    } catch (error) {
        console.error('Error loading thread:', error);
    } finally {
        if (!isAutoRefresh) {
            loadingMessages.value = false;
        }
    }
};

const sendPublicMessage = async () => {
    if (!publicMessageText.value.trim() || !publicThread.value) return;

    sendingPublic.value = true;
    try {
        const { data } = await api.post(`/forum/threads/${publicThread.value.id}/messages`, {
            body: publicMessageText.value
        });

        if (data.status === 'success') {
            publicThread.value.messages.push(data.data);
            publicMessageText.value = '';
            await nextTick();
            scrollToBottom('public');
        }
    } catch (error) {
        console.error('Error sending public message:', error);
        alert('Error al enviar mensaje');
    } finally {
        sendingPublic.value = false;
    }
};

const sendPrivateMessage = async () => {
    if (!privateMessageText.value.trim() || !selectedThread.value) return;

    sendingPrivate.value = true;
    try {
        const { data } = await api.post(`/forum/threads/${selectedThread.value.id}/messages`, {
            body: privateMessageText.value
        });

        if (data.status === 'success') {
            selectedThread.value.messages.push(data.data);
            privateMessageText.value = '';
            await nextTick();
            scrollToBottom('private');
            
            // Actualizar timestamp del thread en la lista
            const threadInList = privateThreads.value.find(t => t.id === selectedThread.value.id);
            if (threadInList) {
                threadInList.last_message_at = new Date().toISOString();
                threadInList.latest_message = data.data;
            }
        }
    } catch (error) {
        console.error('Error sending private message:', error);
        alert('Error al enviar mensaje');
    } finally {
        sendingPrivate.value = false;
    }
};

const closeThread = async (thread) => {
    if (!confirm('¿Estás seguro de cerrar esta conversación?')) return;

    try {
        const { data } = await api.patch(`/admin/forum/threads/${thread.id}/close`);
        if (data.status === 'success') {
            thread.status = 'closed';
            if (selectedThread.value?.id === thread.id) {
                selectedThread.value.status = 'closed';
            }
            alert('Conversación cerrada exitosamente');
        }
    } catch (error) {
        console.error('Error closing thread:', error);
        alert('Error al cerrar conversación');
    }
};

const refreshAll = async () => {
    refreshing.value = true;
    if (activeTab.value === 'public') {
        await loadPublicForum();
    } else {
        await loadPrivateThreads();
        if (selectedThread.value) {
            await selectThread(selectedThread.value);
        }
    }
    refreshing.value = false;
};

const scrollToBottom = (type) => {
    // Con scroll natural de página, scroll al final del documento
    nextTick(() => {
        window.scrollTo({
            top: document.documentElement.scrollHeight,
            behavior: 'smooth'
        });
    });
};

const isAdmin = (user) => {
    return user?.role?.name === 'admin' || user?.role?.name === 'root';
};

const isMyMessage = (message) => {
    return message.from_user_id === auth.user?.id;
};

const getInitials = (user) => {
    if (!user) return '?';
    const first = user.first_name?.[0] || '';
    const last = user.last_name?.[0] || '';
    return (first + last).toUpperCase() || '?';
};

const formatTime = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const now = new Date();
    const diffInHours = (now - date) / (1000 * 60 * 60);

    if (diffInHours < 1) {
        return 'Ahora';
    } else if (diffInHours < 24) {
        return date.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
    } else if (diffInHours < 168) {
        return date.toLocaleDateString('es-ES', { weekday: 'short', hour: '2-digit', minute: '2-digit' });
    } else {
        return date.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
    }
};

// Watchers
watch(activeTab, (newTab) => {
    if (newTab === 'public' && !publicThread.value) {
        loadPublicForum();
    } else if (newTab === 'private' && privateThreads.value.length === 0) {
        loadPrivateThreads();
    }
});

// Auto-refresh cada 10 segundos para actualizaciones en tiempo real
let refreshInterval;
onMounted(() => {
    loadPublicForum();
    loadPrivateThreads();

    // Auto-refresh cada 10 segundos con carga incremental
    refreshInterval = setInterval(() => {
        if (activeTab.value === 'public') {
            loadPublicForum(true); // isAutoRefresh = true
        } else {
            loadPrivateThreads(true); // isAutoRefresh = true
            if (selectedThread.value) {
                selectThread(selectedThread.value, true); // isAutoRefresh = true
            }
        }
    }, 10000); // Cada 10 segundos
});

onUnmounted(() => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>

<style scoped>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeIn 0.3s ease-out;
}

/* Smooth scroll para toda la página */
html {
    scroll-behavior: smooth;
}
</style>