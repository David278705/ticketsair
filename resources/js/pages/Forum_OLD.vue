<template>
    <div class="min-h-screen bg-slate-50">
        <div class="container mx-auto px-4 py-8 max-w-7xl">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 mb-6">
                <h1 class="text-3xl font-bold text-slate-900 mb-2">
                    Foros de Soporte
                </h1>
                <p class="text-slate-600">
                    Comunícate con nuestro equipo y otros usuarios
                </p>
            </div>

            <!-- Tabs -->
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 mb-6">
                <div class="border-b border-slate-200">
                    <nav class="flex -mb-px">
                        <button
                            @click="activeTab = 'public'"
                            :class="[
                                activeTab === 'public'
                                    ? 'border-blue-500 text-blue-600'
                                    : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300',
                                'flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors'
                            ]"
                        >
                            <MessageCircle class="w-5 h-5 inline-block mr-2" />
                            Foro Público
                        </button>
                        <button
                            @click="activeTab = 'private'"
                            :class="[
                                activeTab === 'private'
                                    ? 'border-blue-500 text-blue-600'
                                    : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300',
                                'flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors'
                            ]"
                        >
                            <Lock class="w-5 h-5 inline-block mr-2" />
                            Soporte Privado
                            <span v-if="privateUnreadCount > 0" class="ml-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-500 rounded-full">
                                {{ privateUnreadCount }}
                            </span>
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Public Forum -->
            <div v-if="activeTab === 'public'" class="bg-white rounded-lg shadow-sm border border-slate-200">
                <div class="p-6 border-b border-slate-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-slate-900">Foro Público</h2>
                            <p class="text-sm text-slate-600 mt-1">Pregunta y ayuda a otros usuarios</p>
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
                <div ref="publicMessagesContainer" class="h-[500px] overflow-y-auto p-6 space-y-4 bg-slate-50">
                    <div v-if="loadingPublic" class="flex justify-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    </div>

                    <div v-else-if="publicMessages.length === 0" class="text-center py-8 text-slate-500">
                        <MessageCircle class="w-12 h-12 mx-auto mb-3 text-slate-300" />
                        <p>No hay mensajes aún. ¡Sé el primero en escribir!</p>
                    </div>

                    <div v-else class="space-y-6">
                        <div
                            v-for="message in publicMessages"
                            :key="message.id"
                            class="flex gap-3 animate-fade-in"
                        >
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold shadow-sm"
                                    :class="isAdminUser(message.from) ? 'bg-blue-600' : 'bg-slate-400'">
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
                                            <span v-if="isAdminUser(message.from)" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
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
                    <!-- Alerta para usuario root -->
                    <div v-if="isRoot" class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                        <div class="flex items-center gap-2 text-amber-800">
                            <Shield class="w-5 h-5" />
                            <span class="font-medium">Modo Supervisor</span>
                        </div>
                        <p class="text-sm text-amber-700 mt-1">
                            Los usuarios root solo pueden visualizar el foro, no enviar mensajes.
                        </p>
                    </div>
                    
                    <form v-if="canSendMessages" @submit.prevent="sendPublicMessage" class="flex gap-3">
                        <textarea
                            v-model="publicMessageText"
                            @keydown.enter.ctrl="sendPublicMessage"
                            placeholder="Escribe tu mensaje... (Ctrl+Enter para enviar)"
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
            <div v-if="activeTab === 'private'" class="bg-white rounded-lg shadow-sm border border-slate-200">
                <!-- No thread yet -->
                <div v-if="!hasPrivateThread && !loadingPrivate" class="p-12 text-center">
                    <Lock class="w-16 h-16 mx-auto mb-4 text-slate-300" />
                    <h3 class="text-xl font-semibold text-slate-900 mb-2">
                        Soporte Privado
                    </h3>
                    
                    <!-- Mensaje para clientes -->
                    <div v-if="isClient">
                        <p class="text-slate-600 mb-6 max-w-md mx-auto">
                            Inicia una conversación privada con nuestro equipo de soporte.
                            Solo tú y los administradores podrán ver estos mensajes.
                        </p>
                        <button
                            @click="showCreateThreadModal = true"
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors inline-flex items-center gap-2"
                        >
                            <Plus class="w-5 h-5" />
                            Iniciar Conversación
                        </button>
                    </div>
                    
                    <!-- Mensaje para admins/root -->
                    <div v-else class="max-w-md mx-auto">
                        <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-center gap-2 text-blue-800 mb-2">
                                <Shield class="w-5 h-5" />
                                <span class="font-medium">Vista de {{ isRoot ? 'Supervisor' : 'Administrador' }}</span>
                            </div>
                            <p class="text-sm text-blue-700">
                                {{ isRoot 
                                    ? 'Los usuarios root pueden supervisar todas las conversaciones desde el panel de administración, pero no pueden iniciar nuevas conversaciones ni enviar mensajes.'
                                    : 'Los administradores no pueden iniciar conversaciones. Gestiona las conversaciones de clientes desde el panel de administración.'
                                }}
                            </p>
                        </div>
                        <router-link 
                            v-if="isAdmin"
                            to="/admin/forum"
                            class="mt-4 inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            Ir al Panel de Administración
                        </router-link>
                    </div>
                </div>

                <!-- Thread exists -->
                <div v-else-if="hasPrivateThread">
                    <div class="p-6 border-b border-slate-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-semibold text-slate-900">{{ privateThread.title }}</h2>
                                <p class="text-sm text-slate-600 mt-1">Conversación privada con el equipo de soporte</p>
                            </div>
                            <button
                                @click="loadPrivateThread"
                                class="p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors"
                            >
                                <RefreshCw class="w-5 h-5" :class="{ 'animate-spin': loadingPrivate }" />
                            </button>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div ref="privateMessagesContainer" class="h-[500px] overflow-y-auto p-6 space-y-5 bg-slate-50">
                        <div v-if="loadingPrivate" class="flex justify-center py-8">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        </div>

                        <div v-else class="space-y-5">
                            <div
                                v-for="message in privateMessages"
                                :key="message.id"
                                class="flex gap-3 animate-fade-in"
                                :class="{ 'flex-row-reverse': isMyMessage(message) }"
                            >
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold shadow-sm"
                                        :class="isAdminUser(message.from) ? 'bg-blue-600' : 'bg-slate-400'">
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
                                                <span v-if="isAdminUser(message.from)" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                                    :class="isMyMessage(message) ? 'bg-white/20 text-white' : 'bg-blue-100 text-blue-800'">
                                                    <Shield class="w-3 h-3 mr-1" />
                                                    {{ message.from.role?.name === 'root' ? 'Root' : 'Admin' }}
                                                </span>
                                            </div>
                                            <time class="text-xs ml-2" :class="isMyMessage(message) ? 'text-blue-100' : 'text-slate-500'">
                                                {{ formatTime(message.sent_at || message.created_at) }}
                                            </time>
                                        </div>
                                        <p class="whitespace-pre-wrap leading-relaxed text-sm" :class="isMyMessage(message) ? 'text-white' : 'text-slate-700'">{{ message.body }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Message Input -->
                    <div class="p-6 border-t border-slate-200 bg-white">
                        <!-- Alerta para usuario root -->
                        <div v-if="isRoot" class="p-4 bg-amber-50 border border-amber-200 rounded-lg">
                            <div class="flex items-center gap-2 text-amber-800">
                                <Shield class="w-5 h-5" />
                                <span class="font-medium">Modo Supervisor</span>
                            </div>
                            <p class="text-sm text-amber-700 mt-1">
                                Los usuarios root solo pueden visualizar las conversaciones, no enviar mensajes.
                            </p>
                        </div>
                        
                        <form v-if="canSendMessages" @submit.prevent="sendPrivateMessage" class="flex gap-3">
                            <textarea
                                v-model="privateMessageText"
                                @keydown.enter.ctrl="sendPrivateMessage"
                                placeholder="Escribe tu mensaje... (Ctrl+Enter para enviar)"
                                rows="2"
                                class="flex-1 px-4 py-2 border border-slate-300 rounded-lg bg-white text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                            ></textarea>
                            <button
                                type="submit"
                                :disabled="!privateMessageText.trim() || sendingPrivate"
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                            >
                                <Send class="w-4 h-4" />
                                Enviar
                            </button>
                        </form>
                    </div>
                </div>

                <div v-else-if="loadingPrivate" class="p-12 text-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                </div>
            </div>
        </div>

        <!-- Create Thread Modal -->
        <div v-if="showCreateThreadModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg max-w-lg w-full p-6">
                <h3 class="text-xl font-semibold text-slate-900 mb-4">
                    Iniciar Conversación Privada
                </h3>
                <form @submit.prevent="createPrivateThread" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Título (opcional)
                        </label>
                        <input
                            v-model="createThreadForm.title"
                            type="text"
                            placeholder="Ej: Consulta sobre mi reserva"
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg bg-white text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Primer mensaje *
                        </label>
                        <textarea
                            v-model="createThreadForm.first_message"
                            rows="4"
                            required
                            placeholder="Describe tu consulta o problema..."
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg bg-white text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                        ></textarea>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            @click="showCreateThreadModal = false"
                            class="px-4 py-2 text-slate-700 hover:bg-slate-100 rounded-lg transition-colors"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="!createThreadForm.first_message.trim() || creatingThread"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ creatingThread ? 'Creando...' : 'Crear Conversación' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch, nextTick } from 'vue';
import { MessageCircle, Lock, Send, RefreshCw, Plus, Shield } from 'lucide-vue-next';
import { api } from '../lib/api';
import { useAuth } from '../stores/auth';

const auth = useAuth();

// Computed para permisos de rol
const isRoot = computed(() => auth.user?.role?.name === 'root');
const isAdmin = computed(() => auth.user?.role?.name === 'admin');
const isClient = computed(() => auth.user?.role?.name === 'client');
const canSendMessages = computed(() => !isRoot.value); // Root no puede enviar mensajes
const canCreatePrivateThread = computed(() => isClient.value); // Solo clientes pueden crear hilos privados

// Estado
const activeTab = ref('public');
const loadingPublic = ref(false);
const loadingPrivate = ref(false);
const sendingPublic = ref(false);
const sendingPrivate = ref(false);
const creatingThread = ref(false);
const showCreateThreadModal = ref(false);

// Foro público
const publicThread = ref(null);
const publicMessages = computed(() => publicThread.value?.messages || []);
const publicMessageText = ref('');

// Foro privado
const privateThread = ref(null);
const hasPrivateThread = ref(false);
const privateMessages = computed(() => privateThread.value?.messages || []);
const privateMessageText = ref('');
const privateUnreadCount = ref(0);

// Refs para scroll
const publicMessagesContainer = ref(null);
const privateMessagesContainer = ref(null);

// Form para crear thread
const createThreadForm = reactive({
    title: '',
    first_message: ''
});

// Métodos
const loadPublicForum = async (isAutoRefresh = false) => {
    // No mostrar loading si es un refresh automático
    if (!isAutoRefresh) {
        loadingPublic.value = true;
    }
    
    try {
        const { data } = await api.get('/forum/public');
        
        if (data.status === 'success') {
            if (isAutoRefresh && publicThread.value) {
                // En auto-refresh, solo agregar nuevos mensajes sin re-renderizar todo
                const currentMessageIds = publicMessages.value.map(m => m.id);
                const newMessages = data.data.messages.filter(m => !currentMessageIds.includes(m.id));
                
                if (newMessages.length > 0) {
                    // Solo agregar los mensajes nuevos al array existente
                    publicThread.value.messages.push(...newMessages);
                }
            } else {
                // Carga inicial o manual: reemplazar todo
                publicThread.value = data.data;
                
                // Solo hacer scroll en carga inicial/manual
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

const loadPrivateThread = async (isAutoRefresh = false) => {
    // No mostrar loading si es un refresh automático
    if (!isAutoRefresh) {
        loadingPrivate.value = true;
    }
    
    try {
        const { data } = await api.get('/forum/private');
        
        if (data.status === 'success') {
            hasPrivateThread.value = data.has_thread;
            if (data.data) {
                if (isAutoRefresh && privateThread.value) {
                    // En auto-refresh, solo agregar nuevos mensajes sin re-renderizar todo
                    const currentMessageIds = privateMessages.value.map(m => m.id);
                    const newMessages = data.data.messages.filter(m => !currentMessageIds.includes(m.id));
                    
                    if (newMessages.length > 0) {
                        // Solo agregar los mensajes nuevos al array existente
                        privateThread.value.messages.push(...newMessages);
                        
                        // Marcar como leído
                        await api.patch(`/forum/threads/${data.data.id}/read`);
                        loadUnreadCount();
                    }
                } else {
                    // Carga inicial o manual: reemplazar todo
                    privateThread.value = data.data;
                    
                    // Solo hacer scroll en carga inicial/manual
                    await nextTick();
                    scrollToBottom('private');
                }
            }
        }
    } catch (error) {
        console.error('Error loading private thread:', error);
    } finally {
        if (!isAutoRefresh) {
            loadingPrivate.value = false;
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
    if (!privateMessageText.value.trim() || !privateThread.value) return;

    sendingPrivate.value = true;
    try {
        const { data } = await api.post(`/forum/threads/${privateThread.value.id}/messages`, {
            body: privateMessageText.value
        });

        if (data.status === 'success') {
            privateThread.value.messages.push(data.data);
            privateMessageText.value = '';
            await nextTick();
            scrollToBottom('private');
        }
    } catch (error) {
        console.error('Error sending private message:', error);
        alert('Error al enviar mensaje');
    } finally {
        sendingPrivate.value = false;
    }
};

const createPrivateThread = async () => {
    if (!createThreadForm.first_message.trim()) return;

    creatingThread.value = true;
    try {
        const { data } = await api.post('/forum/private/create', createThreadForm);

        if (data.status === 'success') {
            privateThread.value = data.data;
            hasPrivateThread.value = true;
            showCreateThreadModal.value = false;
            createThreadForm.title = '';
            createThreadForm.first_message = '';
            await nextTick();
            scrollToBottom('private');
        }
    } catch (error) {
        console.error('Error creating private thread:', error);
        if (error.response?.data?.errors) {
            alert(Object.values(error.response.data.errors).flat().join('\n'));
        } else {
            alert('Error al crear conversación');
        }
    } finally {
        creatingThread.value = false;
    }
};

const loadUnreadCount = async () => {
    try {
        const { data } = await api.get('/forum/unread-count');
        if (data.status === 'success') {
            privateUnreadCount.value = data.unread_count;
        }
    } catch (error) {
        console.error('Error loading unread count:', error);
    }
};

const scrollToBottom = (type) => {
    const container = type === 'public' ? publicMessagesContainer.value : privateMessagesContainer.value;
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
};

const isAdminUser = (user) => {
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
    } else if (newTab === 'private' && !privateThread.value) {
        loadPrivateThread();
    }
});

// Setup Laravel Echo para WebSockets en tiempo real
onMounted(() => {
    loadPublicForum();
    loadPrivateThread();
    loadUnreadCount();

    // Escuchar mensajes del foro público
    window.Echo.channel('forum.public')
        .listen('.message.sent', (e) => {
            console.log('Nuevo mensaje público:', e.message);
            // Solo agregar si no existe ya (evitar duplicados)
            if (publicThread.value && !publicMessages.value.find(m => m.id === e.message.id)) {
                publicThread.value.messages.push(e.message);
                
                // Scroll solo si estoy en la tab pública y soy yo quien envió
                if (activeTab.value === 'public' && e.message.from_user_id === auth.user?.id) {
                    nextTick(() => scrollToBottom('public'));
                }
            }
        });

    // Escuchar mensajes de mi thread privado
    if (auth.user) {
        // Primero cargar el thread para obtener su ID
        loadPrivateThread().then(() => {
            if (privateThread.value) {
                window.Echo.private(`forum.thread.${privateThread.value.id}`)
                    .listen('.message.sent', (e) => {
                        console.log('Nuevo mensaje privado:', e.message);
                        // Solo agregar si no existe ya
                        if (!privateMessages.value.find(m => m.id === e.message.id)) {
                            privateThread.value.messages.push(e.message);
                            
                            // Actualizar contador si no es mi mensaje
                            if (e.message.from_user_id !== auth.user?.id) {
                                loadUnreadCount();
                            }
                            
                            // Scroll solo si estoy en la tab privada y soy yo quien envió
                            if (activeTab.value === 'private' && e.message.from_user_id === auth.user?.id) {
                                nextTick(() => scrollToBottom('private'));
                            }
                        }
                    });
            }
        });
    }
});

// Cleanup
import { onUnmounted } from 'vue';
onUnmounted(() => {
    // Dejar de escuchar los canales
    window.Echo.leave('forum.public');
    if (privateThread.value) {
        window.Echo.leave(`forum.thread.${privateThread.value.id}`);
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

/* Smooth scroll */
.overflow-y-auto {
    scroll-behavior: smooth;
}

/* Mejora visual de scrollbar */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
