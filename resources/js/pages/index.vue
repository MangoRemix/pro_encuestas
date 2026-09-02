<template>
    <Head title="Dashboard" />
    <MainLayout>
        <!--<DashboardHome /> DEPRECADA-->
        
        <DashboardHome2 />
    </MainLayout>
</template>
<script setup>

import DashboardHome from '@/components/dashboard-home.vue';
import DashboardHome2 from '@/components/dashboard-home2.vue';
import MainLayout from '@/layouts/main-layout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const page = usePage()

onMounted(()=>{
    console.log(page.props)
})

</script>
<style scoped>
    
</style>