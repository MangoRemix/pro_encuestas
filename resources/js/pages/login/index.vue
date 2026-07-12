<template>
    <Head title="Inicio de sesión"></Head>
    <MainLayout>
        
        <div class="flex items-center justify-center min-h-[70vh] bg-white/30 backdrop-blur-md shadow-lg w-1/3 mx-auto rounded-xl">
            <div class="w-full max-w-sm p-6 space-y-6">

                <div class="flex justify-center mb-8">
                    <div class="w-24 h-24 border-2 border-white rounded-full flex items-center justify-center">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                </div>

                <form @submit.prevent="login" class="space-y-4">
                    <div class="flex bg-gray-200 rounded overflow-hidden">
                        <div class="px-3 py-2 bg-blue-900 flex items-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <input type="text" placeholder="USERNAME" class="w-full px-3 py-2 bg-transparent focus:outline-none text-gray-700 placeholder-gray-500 font-bold">
                    </div>

                    <div class="flex bg-gray-200 rounded overflow-hidden">
                        <div class="px-3 py-2 bg-blue-900 flex items-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <input type="password" placeholder="********" class="w-full px-3 py-2 bg-transparent focus:outline-none text-gray-700 placeholder-gray-500 font-bold">
                    </div>

                    <button class="w-full primary-button-app py-3 font-bold uppercase tracking-wider text-white rounded">
                        Login
                    </button>
                </form>

                <div class="flex justify-between items-center text-sm text-gray-300">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" class="rounded border-gray-400 text-purple-900 focus:ring-purple-900">
                        <span>Remember me</span>
                    </label>
                    <a href="#" class="hover:underline">Forgot your password?</a>
                </div>
            </div>
        </div>

    </MainLayout>
</template>
<script setup>
import MainLayout from '@/layouts/main-layout.vue';
import { Head } from '@inertiajs/vue3';

const login = () => {
    console.log('Login attempt');
};
</script>
<style>
    
</style>