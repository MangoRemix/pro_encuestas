<template>
    <div id="main" class="w-full min-h-screen relative pt-5">
       <!-- Botón para abrir el Menú Lateral -->
        <div v-if="user" class="fixed top-5 left-5 z-40">
            <button 
                @click="isMenuOpen = true" 
                class="flex items-center justify-center bg-white dark:bg-slate-800 p-3 rounded-full shadow-lg border border-slate-100 dark:border-slate-700 hover:scale-110 active:scale-95 transition-all cursor-pointer group"
                aria-label="Abrir menú"
            >
                <Icon 
                    icon="ic:round-menu" 
                    class="text-2xl text-slate-700 dark:text-slate-200 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" 
                />
            </button>
        </div>

        <!-- Componente de Menú Lateral -->
        <Menu 
            v-if="user"
            :show="isMenuOpen" 
            :user="user"
            :items="filteredMenuItems"
            @close="isMenuOpen = false" 
        />
        <div class="w-1/3 md:w-full xl:w-7xl mx-auto h-fit">
            <!-- <img src="/images/logoAlcaldia.png" class="bg-white rounded-full object-cover h-30 w-30 md:h-25 md:w-25 mx-auto border-2 border-white mt-5" alt=""> -->
            <img src="/images/logoAlcaldia.png" class="bg-white rounded-full object-cover h-30 w-30 md:h-25 md:w-25 mx-auto border-2 border-white" alt="">
        </div>

        <div class="w-full md:w-7xl 2xl:w-8/12 min-h-screen mx-auto mt-4 px-4">
            <slot />
        </div>
    </div>
</template>
<script setup>
import { ref, computed } from 'vue';
import { Icon } from '@iconify/vue';
import { usePage } from '@inertiajs/vue3';
import Menu from '@/components/menu.vue';

const isMenuOpen = ref(false);
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

// Ítems del menú con soporte para dropdowns
const menuItems = ref([
    {
        label: 'Inicio',
        icon: 'ic:round-home',
        link: '/'
    },
    {
        label: 'Encuestas',
        icon: 'ic:baseline-assignment',
        children: [
            { label: 'Ver todas', link: '/surveys?page=1', permission:'ADMIN' },
            { label: 'Crear nueva', link: '/surveys/create-survey/step-1', permission:'ADMIN' },
            { label: 'Registrar rangos de edad', link: '/age-ranges', permission:'ADMIN' }
        ]
    },
    {
        label: 'Gestión de usuarios',
        icon: 'ic:baseline-people',
        children: [
            {
                label: 'Encuestadores/Admins',
        children: [
    {
                        label: 'Nuevo Encuestador/Admin', link: '/users/create', permission: 'ADMIN'
    },
        ]
            },
            {
                label: 'Mostrar usuarios',
                link: '/users', permission:'ADMIN' ,
            },
        ]
    },
    {
        label: 'Encuestados',
        icon: 'ic:baseline-category',
        children: [
            { label: 'Nuevo encuestado', link: '/poll-users/step-1' , permission:'POLLSTER'},
            { label: 'Encuestas Realizadas', link: '/poll-users/finished-list',permission:'POLLSTER' }
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
<style>
    #main{
        background-color: #0B1E36;
    }
</style>