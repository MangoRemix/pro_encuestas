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
                <div class="flex w-full items-center gap-2 md:w-80">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Buscar por nombre, email o rol..."
                        @keyup.enter="handleSearch"
                        class="w-full rounded border border-slate-700 bg-slate-900 px-4 py-2 text-slate-200 placeholder-slate-500 transition-all focus:ring-1 focus:ring-slate-500 focus:outline-none"
                    />
                    <button
                        @click="handleSearch"
                        class="shrink-0 rounded border border-slate-700 bg-slate-800 px-4 py-2 font-medium text-slate-200 transition-colors hover:bg-slate-700"
                    >
                        Buscar
                    </button>
                </div>
                <div class="w-50">
                    <button
                        @click="openCreateModal"
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
                    v-for="user in staffData.data"
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
                            {{ getRoleName(user) }}
                        </span>
                    </div>
                    <div class="mb-4 space-y-1 text-sm text-slate-400">
                        <p>Email: {{ user.email }}</p>
                        <p>Sexo: {{ user.sex_id === 1 ? 'M' : 'F' }}</p>
                        <p v-if="user.disabled_at" class="text-red-400">
                            Deshabilitado
                        </p>
                    </div>
                    <div
                        class="flex justify-end gap-2 border-t border-slate-700 pt-3"
                    >
                        <Icon
                            @click="editUser(user)"
                            class="cursor-pointer text-xl text-yellow-500 hover:text-yellow-400"
                            icon="ic:baseline-edit"
                        />
                        <Icon
                            v-if="user.disabled_at"
                            @click="handleEnableUser(user.id)"
                            class="cursor-pointer text-xl text-green-500 hover:text-green-400"
                            icon="ic:baseline-restore"
                            title="Habilitar"
                        />
                        <Icon
                            v-else
                            @click="confirmDisable(user.id)"
                            class="cursor-pointer text-xl text-red-500 hover:text-red-400"
                            icon="ic:baseline-restore-from-trash"
                            title="Deshabilitar"
                        />
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
                            v-for="user in staffData.data"
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
                                    {{ getRoleName(user) }}
                                </span>
                                <span
                                    v-if="user.disabled_at"
                                    class="ml-2 rounded border border-red-700 bg-red-900/50 px-2 py-1 text-xs text-red-300"
                                >
                                    Deshabilitado
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
                                        v-if="user.disabled_at"
                                        @click="handleEnableUser(user.id)"
                                        class="cursor-pointer text-xl text-green-500 hover:text-green-400"
                                        icon="ic:baseline-restore"
                                        title="Habilitar"
                                    />
                                    <Icon
                                        v-else
                                        class="cursor-pointer text-xl text-red-500 hover:text-red-400"
                                        icon="ic:baseline-restore-from-trash"
                                        title="Deshabilitar"
                                        @click="confirmDisable(user.id)"
                                    />
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <Modal :show="isModalOpen" @close="isModalOpen = false">
            <UserForm
                class="w-100"
                :user="userToEdit"
                @created="handleUserCreated"
                @updated="handleUserUpdated"
            />
        </Modal>

        <Modal :show="isDisableModalOpen" @close="isDisableModalOpen = false">
            <div class="p-4 text-center">
                <h3 class="mb-4 text-lg font-bold text-slate-800">
                    Deshabilitar usuario
                </h3>
                <p class="mb-4 text-slate-600">
                    El usuario no podrá iniciar sesión mientras esté
                    deshabilitado. Indica el motivo.
                </p>
                <textarea
                    v-model="disableReason"
                    rows="3"
                    maxlength="500"
                    placeholder="Motivo de la deshabilitación"
                    class="inputs-form mb-4 w-full"
                ></textarea>
                <div class="flex justify-center gap-4">
                    <button
                        @click="isDisableModalOpen = false"
                        class="rounded bg-slate-200 px-4 py-2 text-slate-800 hover:bg-slate-300"
                    >
                        Cancelar
                    </button>
                    <button
                        @click="handleDisableUser"
                        :disabled="!disableReason.trim()"
                        class="cursor-pointer rounded bg-red-600 px-4 py-2 text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
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
import { onMounted, ref } from 'vue';
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
    disablePerson,
    enablePerson,
    getRoleName,
} = useUsers();

const searchQuery = ref('');
const isModalOpen = ref(false);
const isDisableModalOpen = ref(false);
const userToDisable = ref(null);
const disableReason = ref('');
const userToEdit = ref(null);

const handleSearch = () => {
    getStaff(1, { search: searchQuery.value });
};

const confirmDisable = (id) => {
    userToDisable.value = id;
    disableReason.value = '';
    isDisableModalOpen.value = true;
};

const handleDisableUser = async () => {
    if (!userToDisable.value || !disableReason.value.trim()) {
        return;
    }

    const success = await disablePerson(
        userToDisable.value,
        disableReason.value.trim(),
    );

    if (success) {
        isDisableModalOpen.value = false;
        userToDisable.value = null;
    }
};

const handleEnableUser = async (id) => {
    await enablePerson(id);
};

const openCreateModal = () => {
    userToEdit.value = null;
    isModalOpen.value = true;
};

const editUser = (user) => {
    userToEdit.value = user;
    isModalOpen.value = true;
};

const handleUserCreated = async () => {
    isModalOpen.value = false;
    await getStaff();
};

const handleUserUpdated = async () => {
    isModalOpen.value = false;
    userToEdit.value = null;
    await getStaff(staffData.value.current_page);
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
