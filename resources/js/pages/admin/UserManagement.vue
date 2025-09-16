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
                        Gestión de Usuarios
                    </h1>
                    <p class="text-slate-600 dark:text-slate-400">
                        Administrar usuarios del sistema
                    </p>
                </div>
                <button
                    @click="openCreateAdmin"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
                >
                    <UserPlus class="w-5 h-5" />
                    Crear Admin
                </button>
            </div>
        </div>

        <!-- Filtros -->
        <div
            class="p-6 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700"
        >
            <div class="flex flex-wrap gap-4">
                <!-- Búsqueda -->
                <div class="flex-1 min-w-64">
                    <div class="relative">
                        <Search
                            class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-slate-400"
                        />
                        <input
                            v-model="filters.search"
                            @input="debouncedSearch"
                            placeholder="Buscar por nombre, email o DNI..."
                            class="w-full pl-10 pr-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>
                </div>

                <!-- Filtro por rol -->
                <select
                    v-model="filters.role"
                    @change="loadUsers"
                    class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">Todos los roles</option>
                    <option value="root">Root</option>
                    <option value="admin">Admin</option>
                    <option value="client">Cliente</option>
                    <option value="visitor">Visitante</option>
                </select>

                <!-- Filtro por estado -->
                <select
                    v-model="filters.active"
                    @change="loadUsers"
                    class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">Todos los estados</option>
                    <option value="true">Activo</option>
                    <option value="false">Inactivo</option>
                </select>
            </div>
        </div>

        <!-- Lista de usuarios -->
        <div class="p-6">
            <div v-if="loading" class="flex justify-center py-8">
                <div
                    class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"
                ></div>
            </div>

            <div
                v-else-if="users.data?.length"
                class="bg-white dark:bg-slate-800 rounded-lg shadow overflow-hidden"
            >
                <div class="overflow-x-auto">
                    <table
                        class="min-w-full divide-y divide-slate-200 dark:divide-slate-700"
                    >
                        <thead class="bg-slate-50 dark:bg-slate-700">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider"
                                >
                                    Usuario
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider"
                                >
                                    Rol
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider"
                                >
                                    Estado
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider"
                                >
                                    Fecha de registro
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider"
                                >
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700"
                        >
                            <tr
                                v-for="user in users.data"
                                :key="user.id"
                                class="hover:bg-slate-50 dark:hover:bg-slate-700"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div
                                                class="h-10 w-10 rounded-full bg-slate-300 dark:bg-slate-600 flex items-center justify-center"
                                            >
                                                <User
                                                    class="w-5 h-5 text-slate-600 dark:text-slate-300"
                                                />
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div
                                                class="text-sm font-medium text-slate-900 dark:text-white"
                                            >
                                                {{ user.first_name }}
                                                {{ user.last_name }}
                                            </div>
                                            <div
                                                class="text-sm text-slate-500 dark:text-slate-400"
                                            >
                                                {{ user.email }}
                                            </div>
                                            <div
                                                class="text-xs text-slate-400 dark:text-slate-500"
                                            >
                                                DNI: {{ user.dni }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                        :class="
                                            getRoleBadgeClass(user.role?.name)
                                        "
                                    >
                                        {{
                                            getRoleDisplayName(user.role?.name)
                                        }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                        :class="
                                            user.is_active
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                                : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                        "
                                    >
                                        {{
                                            user.is_active
                                                ? "Activo"
                                                : "Inactivo"
                                        }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400"
                                >
                                    {{ formatDate(user.created_at) }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                >
                                    <div
                                        class="flex items-center justify-end gap-2"
                                    >
                                        <button
                                            @click="editUser(user)"
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                            title="Editar"
                                        >
                                            <Edit class="w-4 h-4" />
                                        </button>
                                        <button
                                            @click="resetPassword(user)"
                                            class="text-orange-600 hover:text-orange-900 dark:text-orange-400 dark:hover:text-orange-300"
                                            title="Resetear contraseña"
                                        >
                                            <Key class="w-4 h-4" />
                                        </button>
                                        <button
                                            v-if="user.role?.name !== 'root'"
                                            @click="toggleStatus(user)"
                                            :class="
                                                user.is_active
                                                    ? 'text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300'
                                                    : 'text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300'
                                            "
                                            :title="
                                                user.is_active
                                                    ? 'Desactivar'
                                                    : 'Activar'
                                            "
                                        >
                                            <component
                                                :is="
                                                    user.is_active
                                                        ? UserX
                                                        : UserCheck
                                                "
                                                class="w-4 h-4"
                                            />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div
                    v-if="users.meta?.links?.length"
                    class="px-6 py-4 bg-slate-50 dark:bg-slate-700 border-t border-slate-200 dark:border-slate-600"
                >
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-slate-700 dark:text-slate-300">
                            Mostrando {{ users.meta.from }} a
                            {{ users.meta.to }} de
                            {{ users.meta.total }} resultados
                        </div>
                        <div class="flex gap-2">
                            <button
                                v-for="link in users.meta.links"
                                :key="link.label"
                                @click="changePage(link.url)"
                                :disabled="!link.url"
                                class="px-3 py-1 rounded border border-slate-300 dark:border-slate-600 text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-slate-100 dark:hover:bg-slate-600"
                                :class="
                                    link.active
                                        ? 'bg-blue-600 text-white border-blue-600'
                                        : 'bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300'
                                "
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-else
                class="text-center py-8 text-slate-500 dark:text-slate-400"
            >
                No se encontraron usuarios.
            </div>
        </div>

        <!-- Modales -->
        <CreateAdminModal
            v-model:open="createAdminOpen"
            @created="onUserCreated"
        />

        <EditUserModal
            v-model:open="editUserOpen"
            v-model:user="selectedUser"
            @updated="onUserUpdated"
        />

        <ResetPasswordModal
            v-model:open="resetPasswordOpen"
            v-model:user="selectedUser"
            @reset="onPasswordReset"
        />
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import {
    Search,
    UserPlus,
    User,
    Edit,
    Key,
    UserX,
    UserCheck,
} from "lucide-vue-next";
import { api } from "../../lib/api";
import { useAuth } from "../../stores/auth";
import { debounce } from "lodash";
import CreateAdminModal from "../../components/admin/CreateAdminModal.vue";
import EditUserModal from "../../components/admin/EditUserModal.vue";
import ResetPasswordModal from "../../components/admin/ResetPasswordModal.vue";

const auth = useAuth();

// Estado
const loading = ref(false);
const users = ref({ data: [], meta: null });
const createAdminOpen = ref(false);
const editUserOpen = ref(false);
const resetPasswordOpen = ref(false);
const selectedUser = ref(null);

// Filtros
const filters = reactive({
    search: "",
    role: "",
    active: "",
});

// Métodos
const loadUsers = async (url = null) => {
    loading.value = true;
    try {
        const params = {};
        if (filters.search) params.search = filters.search;
        if (filters.role) params.role = filters.role;
        if (filters.active) params.active = filters.active;

        const endpoint = "/admin/users";
        const { data } = await api.get(endpoint, { params });
        users.value = data.data;
    } catch (error) {
        console.error("Error loading users:", error);
        alert("Error al cargar usuarios");
    } finally {
        loading.value = false;
    }
};

const debouncedSearch = debounce(() => {
    loadUsers();
}, 500);

const changePage = (url) => {
    if (url) loadUsers(url);
};

const openCreateAdmin = () => {
    createAdminOpen.value = true;
};

const editUser = (user) => {
    selectedUser.value = { ...user };
    editUserOpen.value = true;
};

const resetPassword = (user) => {
    selectedUser.value = { ...user };
    resetPasswordOpen.value = true;
};

const toggleStatus = async (user) => {
    try {
        const { data } = await api.patch(
            `/admin/users/${user.id}/toggle-status`
        );
        if (data.status === "success") {
            await loadUsers();
            alert(data.message);
        }
    } catch (error) {
        console.error("Error toggling user status:", error);
        alert("Error al cambiar estado del usuario");
    }
};

const onUserCreated = () => {
    loadUsers();
};

const onUserUpdated = () => {
    loadUsers();
};

const onPasswordReset = () => {
    alert("Contraseña restablecida exitosamente");
};

// Utilidades
const getRoleBadgeClass = (role) => {
    const classes = {
        root: "bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200",
        admin: "bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200",
        client: "bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200",
        visitor:
            "bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200",
    };
    return classes[role] || classes.visitor;
};

const getRoleDisplayName = (role) => {
    const names = {
        root: "Root",
        admin: "Administrador",
        client: "Cliente",
        visitor: "Visitante",
    };
    return names[role] || "Desconocido";
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("es-ES", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

onMounted(() => {
    loadUsers();
});
</script>
