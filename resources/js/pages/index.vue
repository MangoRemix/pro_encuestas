<template>
    <Head title="Dashboard" />
    <MainLayout>
        <!--<DashboardHome /> DEPRECADA-->
        
        <DashboardHome2 />
    </MainLayout>
</template>
<script setup>

import { Head, usePage } from '@inertiajs/vue3';
import DashboardHome from '@/components/dashboard-home.vue';
import DashboardHome2 from '@/components/dashboard-home2.vue';
import MainLayout from '@/layouts/main-layout.vue';

const page = usePage()

</script>
<style scoped>
    
</style>