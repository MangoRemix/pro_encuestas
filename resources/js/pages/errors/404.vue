<template>
    <div id="main" class="min-h-screen flex flex-col items-center justify-center text-white p-6">
        <img src="/images/logoAlcaldia.png" alt="Logo Alcaldía" class="w-32 h-32 mb-8 rounded-full border-4 border-white/10 shadow-xl bg-white">
        
        <h1 class="text-9xl font-bold text-blue-400 mb-4">404</h1>
        <h2 class="text-3xl font-semibold mb-2">Página no encontrada</h2>
        <p class="text-slate-400 mb-8 max-w-md text-center">
            Lo sentimos, la página que estás buscando no existe o ha sido movida.
        </p>

        <div class="flex items-center justify-center mx-auto w-fit md:w-1/3">
            <a 
            href="/" 
            class="primary-button-app text-center"
        >
            Volver al inicio
        </a>
        </div>
        
    </div>
</template>

<script setup>
</script>

<style scoped>
    #main{
        background-color: #0B1E36;
    }
</style>