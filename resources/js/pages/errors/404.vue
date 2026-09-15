<template>
    <div
        id="main"
        class="flex min-h-screen flex-col items-center justify-center p-6 text-white"
    >
        <img
            src="/images/logoAlcaldia.png"
            alt="Logo Alcaldía"
            class="mb-8 h-32 w-32 rounded-full border-4 border-white/10 bg-white shadow-xl"
        />

        <h1 class="mb-4 text-9xl font-bold text-blue-400">404</h1>
        <h2 class="mb-2 text-3xl font-semibold">Página no encontrada</h2>
        <p class="mb-8 max-w-md text-center text-slate-400">
            Lo sentimos, la página que estás buscando no existe o ha sido
            movida.
        </p>

        <div class="mx-auto flex w-fit items-center justify-center md:w-1/3">
            <a href="/" class="primary-button-app text-center">
                Volver al inicio
            </a>
        </div>
    </div>
</template>

<script setup></script>

<style scoped>
#main {
    background-color: #0b1e36;
}
</style>
