<script setup>
    const {message, isError} = defineProps(['message','isError'])
</script>
<template>
    <div 
      v-if="message" 
      :class="[
        'mt-4 p-3 rounded-md text-sm text-center font-medium transition-all',
        isError ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'
      ]"
    >
      {{ message }}
    </div>
</template>