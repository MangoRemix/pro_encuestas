<template>
    <Head title="Gestión de Personal" />
    <MainLayout>
        <div class="mx-auto max-w-7xl p-0 md:p-6">
            <h1
                class="mb-6 text-center text-2xl font-bold text-slate-100 md:text-3xl"
            >
                Gestión de Personal
            </h1>

            <!-- Barra de herramientas -->
            <div class="mb-6 flex flex-col justify-between gap-4 md:flex-row">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Buscar por nombre, email o rol..."
                    class="w-full rounded border border-slate-700 bg-slate-900 px-4 py-2 text-slate-200 placeholder-slate-500 transition-all focus:ring-1 focus:ring-slate-500 focus:outline-none md:w-80"
                />
                <div class="w-50">
                    <button
                        @click="isModalOpen = true"
                        class="green-button-app flex cursor-pointer items-center justify-center gap-x-2 rounded px-4 py-2 font-medium transition-colors"
                    >
                        <Icon class="text-2xl" icon="ic:outline-plus" />
                        Crear usuario
                    </button>
                </div>
            </div>

            <!-- Vista Móvil: Tarjetas -->
            <div class="space-y-4 md:hidden">
                <div v-if="isLoading" class="py-8 text-center text-slate-400">
                    Cargando...
                </div>
                <div
                    v-else-if="errorMessage"
                    class="py-8 text-center text-red-400"
                >
                    {{ errorMessage }}
                </div>
                <div
                    v-for="user in filteredStaff"
                    :key="user.id"
                    class="rounded-lg border border-slate-700 bg-slate-800 p-3 shadow-sm"
                >
                    <div class="mb-3 flex items-start justify-between">
                        <h3 class="text-lg font-bold text-white">
                            {{ user.name }}
                        </h3>
                        <span
                            class="rounded border border-slate-700 bg-slate-800 px-2 py-1 text-xs text-blue-100"
                        >
                            {{ getRoleName(user.rol_id) }}
                        </span>
                    </div>
                    <div class="mb-4 space-y-1 text-sm text-slate-400">
                        <p>Email: {{ user.email }}</p>
                        <p>Sexo: {{ user.sex_id === 1 ? 'M' : 'F' }}</p>
                    </div>
                    <!-- Placeholder for actions if any were needed -->
                    <div
                        class="flex justify-end gap-2 border-t border-slate-700 pt-3"
                    >
                        <!-- Future implementation for edit/delete actions could go here -->
                    </div>
                </div>
            </div>

            <!-- Tabla con estilo Dashboard (Vista Escritorio) -->
            <div
                class="custom-scrollbar hidden max-h-150 overflow-hidden overflow-y-auto rounded-lg border border-slate-700 bg-gray-500/30 md:block"
            >
                <table class="w-full border-collapse text-left">
                    <thead class="sticky top-0 z-10 bg-slate-900">
                        <tr
                            class="border-b border-slate-700 text-xs tracking-wider text-white uppercase"
                        >
                            <th class="p-4">Nombre</th>
                            <th class="p-4">Email</th>
                            <th class="p-4">Sexo</th>
                            <th class="p-4">Rol</th>
                            <th class="p-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        <tr v-if="isLoading">
                            <td
                                colspan="5"
                                class="p-4 text-center text-slate-400"
                            >
                                Cargando...
                            </td>
                        </tr>
                        <tr v-else-if="errorMessage">
                            <td
                                colspan="5"
                                class="p-4 text-center text-red-400"
                            >
                                {{ errorMessage }}
                            </td>
                        </tr>
                        <tr
                            v-for="user in filteredStaff"
                            :key="user.id"
                            class="text-slate-200 transition-colors hover:bg-slate-600/30"
                        >
                            <td class="p-4">{{ user.name }}</td>
                            <td class="p-4">{{ user.email }}</td>
                            <td class="p-4">
                                {{ user.sex_id === 1 ? 'M' : 'F' }}
                            </td>
                            <td class="p-4">
                                <span
                                    class="rounded border border-slate-700 bg-slate-800 px-2 py-1 text-xs"
                                >
                                    {{ getRoleName(user.rol_id) }}
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="flex justify-center gap-3">
                                    <Icon
                                        @click="editUser(user)"
                                        class="cursor-pointer text-xl text-yellow-500 hover:text-yellow-400"
                                        icon="ic:baseline-edit"
                                    />
                                    <Icon
                                        class="cursor-pointer text-xl text-red-500 hover:text-red-400"
                                        icon="ic:baseline-restore-from-trash"
                                        @click="confirmDelete(user.id)"
                                    />
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <Modal :show="isModalOpen" @close="isModalOpen = false">
            <UserForm class="w-100" @created="handleUserCreated" />
        </Modal>

        <Modal :show="isDeleteModalOpen" @close="isDeleteModalOpen = false">
            <div class="p-4 text-center">
                <h3 class="mb-4 text-lg font-bold text-slate-800">
                    Confirmar eliminación
                </h3>
                <p class="mb-6 text-slate-600">
                    ¿Estás seguro de que deseas eliminar este usuario? Esta
                    acción no se puede deshacer.
                </p>
                <div class="flex justify-center gap-4">
                    <button
                        @click="isDeleteModalOpen = false"
                        class="rounded bg-slate-200 px-4 py-2 text-slate-800 hover:bg-slate-300"
                    >
                        Cancelar
                    </button>
                    <button
                        @click="handleDeleteUser"
                        class="cursor-pointer rounded bg-red-600 px-4 py-2 text-white hover:bg-red-700"
                    >
                        Confirmar
                    </button>
                </div>
            </div>
        </Modal>

        <Pagination
            v-if="staffData.total > 0"
            :current-page="staffData.current_page"
            :last-page="staffData.last_page"
            :total="staffData.total"
            :from="staffData.from"
            :to="staffData.to"
            @page-change="getStaff"
        />
    </MainLayout>
</template>

<script setup>
import { Icon } from '@iconify/vue';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref, computed } from 'vue';
import UserForm from '@/components/forms/user-form.vue';
import Modal from '@/components/modal.vue';
import Pagination from '@/components/pagination.vue';
import { useUsers } from '@/composables/api/users';
import MainLayout from '@/layouts/main-layout.vue';

const {
    staffData,
    isLoading,
    errorMessage,
    getStaff,
    deleteUser: deleteUserApi,
    getRoleName,
} = useUsers();

const searchQuery = ref('');
const isModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const userToDelete = ref(null);

const filteredStaff = computed(() => {
    const query = searchQuery.value.toLowerCase();

    if (!query) {
        return staffData.value.data;
    }

    return staffData.value.data.filter((user) => {
        const roleName = getRoleName(user.rol_id).toLowerCase();

        return (
            user.name.toLowerCase().includes(query) ||
            user.email.toLowerCase().includes(query) ||
            roleName.includes(query)
        );
    });
});

const confirmDelete = (id) => {
    userToDelete.value = id;
    isDeleteModalOpen.value = true;
};

const handleDeleteUser = async () => {
    if (!userToDelete.value) {
        return;
    }

    const success = await deleteUserApi(userToDelete.value);

    if (success) {
        isDeleteModalOpen.value = false;
        userToDelete.value = null;
    }
};

const editUser = (user) => {
    // TODO: Implementar edición de usuario
    console.log('Editar usuario:', user);
};

const handleUserCreated = async () => {
    isModalOpen.value = false;
    await getStaff();
};

onMounted(async () => {
    await getStaff();
});
</script>

<style scoped>
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #64748b #0f172a;
}
.custom-scrollbar::-webkit-scrollbar {
    width: 8px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #0f172a;
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #64748b;
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
