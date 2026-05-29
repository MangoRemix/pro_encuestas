<template>
    <div id="main" class="w-full overflow-x-scroll">
        <div class="w-11/12 flex items-center justify-end">
            <img src="./../../../public/images/logoAlcaldia.png" class="bg-white rounded-full h-25 w-25 border-2 border-white mt-5" alt="">
        </div>
        <div class="w-full xl:w-6xl min-h-screen mx-auto">
            <slot />
        </div>
        
    </div>
</template>
<script setup>

    
</script>
<style>
    #main{
        background-image: url('../../../public/images/vanishing-stripes.svg');
    }
</style>