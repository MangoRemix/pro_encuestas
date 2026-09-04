<template>
    <div id="main" 
         :class="['w-full min-h-screen relative flex bg-[#0B1E36]', isMenuOpen && windowWidth < 1024 ? 'overflow-hidden h-screen' : '']">
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
             :style="{ 
                 width: (user && isMenuOpen && windowWidth >= 1024) ? 'calc(100% - 20rem)' : '100%', 
                 marginLeft: (user && isMenuOpen && windowWidth >= 1024) ? '20rem' : '0rem' 
             }">
            
            <div class="pt-7 w-full max-w-7xl mx-auto px-4">
                <img src="/images/logoAlcaldia.png" class="bg-white rounded-full object-cover h-30 w-30 mx-auto border-2 border-white" alt="Logo">
            </div>

            <div class="w-full max-w-7xl 2xl:max-w-500 min-h-screen mx-auto mt-4 px-4 pb-10">
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

const isMenuOpen = ref(window.innerWidth >= 1024);
const windowWidth = ref(window.innerWidth);

const updateWidth = () => { 
    windowWidth.value = window.innerWidth;
    if (windowWidth.value < 1024) {
        isMenuOpen.value = false;
    } else {
        isMenuOpen.value = true;
    }
};

onMounted(() => { 
    window.addEventListener('resize', updateWidth); 
});
onUnmounted(() => window.removeEventListener('resize', updateWidth));

const page = usePage();
const user = computed(() => page.props.auth?.user);
const userRole = computed(() => user.value?.role);

const filterMenuItems = (items) => {
    return items
        .map(item => {
            if (item.children) {
                return { ...item, children: filterMenuItems(item.children) };
            }
            return item;
        })
        .filter(item => {
            if (item.children) {
                return item.children.length > 0;
            }
            return !item.permission || item.permission === userRole.value;
        });
};

const filteredMenuItems = computed(() => filterMenuItems(menuItems.value));

const menuItems = ref([
    { label: 'Inicio', icon: 'ic:round-home', link: '/' },
    {
        label: 'Encuestas',
        icon: 'ic:baseline-assignment',
        children: [
            { label: 'Ver todas', link: '/surveys?page=1', permission:'ADMIN' },
            { label: 'Crear nueva', link: '/surveys/create-survey/step-1', permission:'ADMIN' },
        ]
    },
    {
        label: 'Gestión de usuarios',
        icon: 'ic:baseline-people',
        children: [
            { label: 'Encuestadores/Admins', children: [{ label: 'Nuevo Encuestador/Admin', link: '/users/create', permission: 'ADMIN' }] },
            { label: 'Mostrar usuarios', link: '/users', permission:'ADMIN' },
        ]
    },
    {
        label: 'Encuestados',
        icon: 'ic:baseline-category',
        children: [
            { label: 'Nuevo encuestado', link: '/poll-users/step-1' , permission:'POLLSTER'},
            { label: 'Encuestas Realizadas', link: '/poll-users/finished-list', permission:'POLLSTER' }
        ]
    },
    {
        label: 'Estadísticas',
        icon: 'ic:baseline-bar-chart',
        children: [
            { label: 'Reportes Generales', link: '/reports', permission:'ADMIN'},
            { label: 'Respuestas Recientes', link: '/answers', permission:'ADMIN'}
        ]
    },
    {
        label: 'Configuración',
        icon: 'ic:baseline-settings',
        children: [
            { label: 'Configuración general', link: '/settings' },
            { label: 'Gestión de parroquias', link: '/parishes' }
        ]
    }
]);
</script>
