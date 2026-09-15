<script setup>
import { Icon } from '@iconify/vue';
import { router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import MenuItem from '@/components/MenuItem.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    user: { type: Object, default: () => ({ name: 'Usuario Invitado' }) },
    items: { type: Array, default: () => [] },
});

const emit = defineEmits(['close']);

const userRole = props.user?.role ?? null;

const openDropdowns = ref({});

const toggleDropdown = (key) => {
    openDropdowns.value[key] = !openDropdowns.value[key];
};

const closeMenu = () => {
    emit('close');
};

const handleNavigation = () => {
    if (window.innerWidth < 1024) {
        emit('close');
    }
};

const handleLogout = () => {
    router.post('/logout');
};

const handleKeyDown = (e) => {
    if (e.key === 'Escape' && props.show) {
        emit('close');
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
});
</script>

<template>
    <div class="relative">
        <Transition name="fade-overlay">
            <div
                v-if="show"
                class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm transition-opacity lg:hidden"
                @click="closeMenu"
            ></div>
        </Transition>

        <Transition name="slide-menu">
            <div
                v-if="show"
                class="fixed top-0 left-0 z-50 flex h-screen w-80 flex-col border-r border-slate-200 bg-white shadow-2xl transition-transform duration-300 ease-in-out lg:translate-x-0 dark:border-slate-800 dark:bg-slate-900"
            >
                <button
                    @click="closeMenu"
                    class="absolute top-4 right-4 cursor-pointer rounded-full p-2 text-slate-500 transition-colors hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800"
                >
                    <Icon icon="ic:round-close" class="text-2xl" />
                </button>

                <div
                    class="flex flex-col items-center justify-center border-b border-slate-100 bg-slate-50/50 p-8 dark:border-slate-800 dark:bg-slate-900/50"
                >
                    <div
                        class="relative mb-3 h-24 w-24 overflow-hidden rounded-full border-4 border-white shadow-md dark:border-slate-800"
                    >
                        <img
                            src="/images/logoAlcaldia.png"
                            :alt="user.name"
                            class="h-full w-full bg-white object-cover"
                        />
                    </div>
                    <h2
                        class="text-center text-lg font-bold tracking-wide text-slate-800 dark:text-slate-100"
                    >
                        {{ user.name }}
                    </h2>
                    <span
                        class="mt-1 text-xs font-medium text-slate-400 dark:text-slate-500"
                        >Panel de Usuario</span
                    >
                </div>

                <div class="menu-scrollable flex-1 overflow-y-auto px-4 py-6">
                    <ul class="space-y-2">
                        <MenuItem
                            v-for="(item, index) in items"
                            :key="index"
                            :item="{ ...item, _key: String(index) }"
                            :depth="0"
                            :open-dropdowns="openDropdowns"
                            :toggle-dropdown="toggleDropdown"
                            :user-role="userRole"
                            @close="handleNavigation"
                        />

                        <li
                            class="mt-8 border-t border-slate-200 pt-6 dark:border-slate-800"
                        >
                            <button
                                @click="handleLogout"
                                class="flex w-full cursor-pointer items-center gap-3 rounded-xl px-4 py-3 text-red-600 transition-all duration-200 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                            >
                                <Icon icon="ic:round-logout" class="text-xl" />
                                <span class="text-sm font-medium"
                                    >Cerrar sesión</span
                                >
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.fade-overlay-enter-active,
.fade-overlay-leave-active {
    transition: opacity 0.3s ease;
}
.fade-overlay-enter-from,
.fade-overlay-leave-to {
    opacity: 0;
}

.slide-menu-enter-active,
.slide-menu-leave-active {
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.slide-menu-enter-from,
.slide-menu-leave-to {
    transform: translateX(-100%);
}

.expand-enter-active,
.expand-leave-active {
    transition:
        max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1),
        opacity 0.2s ease;
    max-height: 600px;
}
.expand-enter-from,
.expand-leave-to {
    max-height: 0;
    opacity: 0;
}
</style>

<style>
.menu-scrollable::-webkit-scrollbar {
    width: 6px;
}

.menu-scrollable::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

.menu-scrollable::-webkit-scrollbar-thumb {
    background: #94a3b8;
    border-radius: 3px;
    transition: background 0.2s ease;
}

.menu-scrollable::-webkit-scrollbar-thumb:hover {
    background: #64748b;
}

.dark .menu-scrollable::-webkit-scrollbar-track {
    background: #1e293b;
}

.dark .menu-scrollable::-webkit-scrollbar-thumb {
    background: #475569;
}

.dark .menu-scrollable::-webkit-scrollbar-thumb:hover {
    background: #64748b;
}

/* Firefox */
.menu-scrollable {
    scrollbar-width: thin;
    scrollbar-color: #94a3b8 #f1f5f9;
}

.dark .menu-scrollable {
    scrollbar-color: #475569 #1e293b;
}
</style>
