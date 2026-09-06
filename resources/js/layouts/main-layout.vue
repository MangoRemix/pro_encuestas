<template>
    <div id="main" 
         :class="['w-full min-h-screen relative flex bg-[#0B1E36]', isMenuOpen && !isLargeScreen ? 'overflow-hidden h-screen' : '']">
        <!-- Botón siempre visible para alternar el menú -->
        <div v-if="user" class="fixed top-5 left-5 z-50">
            <button 
                @click="isMenuOpen = !isMenuOpen" 
                class="bg-white dark:bg-slate-800 p-3 rounded-full shadow-lg border border-slate-100 dark:border-slate-700 hover:scale-110 active:scale-95 transition-all cursor-pointer"
                aria-label="Alternar menú"
            >
                <Icon icon="ic:round-menu" class="text-2xl text-slate-700 dark:text-slate-200" />
            </button>
        </div>

        <!-- Menú -->
        <Menu v-if="user" :show="isMenuOpen" :user="user" :items="filteredMenuItems" @close="isMenuOpen = false" />
        
        <!-- Contenedor principal: ancho dinámico usando calc() para restar el menú cuando está abierto -->
        <div class="flex-1 flex flex-col transition-all duration-300 min-w-0" 
             :class="user && isMenuOpen ? 'lg:ml-80' : 'ml-0'">
            <div class="pt-7 w-full max-w-7xl mx-auto px-4">
                <img src="/images/logoAlcaldia.png" class="bg-white rounded-full object-cover h-30 w-30 mx-auto border-2 border-white" alt="Logo">
            </div>

            <div class="w-full max-w-7xl 2xl:max-w-500 mx-auto mt-4 px-4 pb-10">
                <slot />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Icon } from '@iconify/vue';
import { usePage } from '@inertiajs/vue3';
import Menu from '@/components/menu.vue';

const MENU_ITEMS = [
    { label: 'Inicio', icon: 'ic:round-home', link: '/' },
    {
        label: 'Encuestas',
        icon: 'ic:baseline-assignment',
        children: [
            { label: 'Ver todas', link: '/surveys?page=1', permission: 'ADMIN' },
            { label: 'Crear nueva', link: '/surveys/create-survey/step-1', permission: 'ADMIN' },
        ]
    },
    {
        label: 'Gestión de usuarios',
        icon: 'ic:baseline-people',
        children: [
            { label: 'Encuestadores/Admins', children: [{ label: 'Nuevo Encuestador/Admin', link: '/users/create', permission: 'ADMIN' }] },
            { label: 'Mostrar usuarios', link: '/users', permission: 'ADMIN' },
        ]
    },
    {
        label: 'Encuestados',
        icon: 'ic:baseline-category',
        children: [
            { label: 'Nuevo encuestado', link: '/poll-users/step-1', permission: 'POLLSTER' },
            { label: 'Encuestas Realizadas', link: '/poll-users/finished-list', permission: 'POLLSTER' }
        ]
    },
    {
        label: 'Estadísticas',
        icon: 'ic:baseline-bar-chart',
        children: [
            { label: 'Reportes Generales', link: '/reports', permission: 'ADMIN' },
            { label: 'Respuestas Recientes', link: '/answers', permission: 'ADMIN' }
        ]
    },
    {
        label: 'Configuración',
        icon: 'ic:baseline-settings',
        children: [
            { label: 'Configuración general', link: '/settings' },
            { label: 'Gestión de parroquias', link: '/parishes', permission: 'ADMIN' }
        ]
    }
];

const isLargeScreen = ref(typeof window !== 'undefined' ? window.innerWidth >= 1024 : true);
const isMenuOpen = ref(isLargeScreen.value);

let mediaQuery;

const handleMediaChange = (e) => {
    isLargeScreen.value = e.matches;
    isMenuOpen.value = e.matches;
};

onMounted(() => {
    mediaQuery = window.matchMedia('(min-width: 1024px)');
    isLargeScreen.value = mediaQuery.matches;
    mediaQuery.addEventListener('change', handleMediaChange);
});

onUnmounted(() => {
    mediaQuery?.removeEventListener('change', handleMediaChange);
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const userRole = computed(() => user.value?.role);

const filterMenuItems = (items) => {
    return items
        .map(item => item.children ? { ...item, children: filterMenuItems(item.children) } : item)
        .filter(item => item.children ? item.children.length > 0 : (!item.permission || item.permission === userRole.value));
};

const filteredMenuItems = computed(() => filterMenuItems(MENU_ITEMS));
</script>

