<template>
    <div id="main" class="w-full overflow-x-scroll min-h-screen relative">
        <!-- Botón para abrir el Menú Lateral -->
        <div class="fixed top-5 left-5 z-40">
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
            :show="isMenuOpen" 
            :user="currentUser"
            :items="menuItems"
            @close="isMenuOpen = false" 
        />

        <div class="w-full px-10 pl-14 xl:w-7xl mx-auto ">
            <img src="../../../public/images/logoAlcaldia.png " class="bg-white rounded-full h-25 w-25 border-2 border-white mt-5" alt="">
        </div>
        
        <div class="w-full xl:w-7xl min-h-screen mx-auto mt-4 px-4 sm:px-6">
            <slot />
        </div>
        
    </div>
</template>
<script setup>
import { ref } from 'vue';
import { Icon } from '@iconify/vue';
import Menu from '@/components/menu.vue';

const isMenuOpen = ref(false);

// Datos del usuario para mostrar en el menú
const currentUser = ref({
    name: 'Ing. Luis Rodríguez',
});

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
            { label: 'Ver todas', link: '/surveys' },
            { label: 'Crear nueva', link: '/surveys/create-survey/step-1' },
            //{ label: 'Categorías', link: '/categories' }
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
                link: '/users',
            },
        ]
    },
    {
        label: 'Encuestados',
        icon: 'ic:baseline-category',
        children: [
            { label: 'Nuevo encuestado', link: '/poll-users/step-1' , permission:'POLLSTER'},
            { label: 'Encuestas Realizadas', link: '/poll-users/finished-list' },
        ]
    },
    {
        label: 'Estadísticas',
        icon: 'ic:baseline-bar-chart',
        children: [
            { label: 'Reportes Generales', link: '/reports' },
            { label: 'Respuestas Recientes', link: '/answers' }
        ]
    },
    {
        label: 'Configuración',
        icon: 'ic:baseline-settings',
        link: '/settings'
    }
]);
</script>
<style>
    #main{
        /*background-image: url('../../../public/images/vanishing-stripes.svg');*/
        /*background-color: #111827;*/ /*POSIBLE*/
        /*background-color: #031B33;*/ /*POSIBLE 2*/
        background-color: #0B1E36;   /*POSIBLE 3*/
    }
</style>