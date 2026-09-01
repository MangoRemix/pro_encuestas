<script setup>
const props = defineProps({
  items: { type: Array, required: true },
  current: { type: String, required: true }
});
</script>

<template>
  <nav aria-label="Progreso" class="w-full mb-8">
    <ol role="list" class="flex items-center justify-between w-full">
      <li 
        v-for="(item, index) in items" 
        :key="index"
        class="relative flex flex-col items-center flex-1"
      >
        <!-- Línea conectora -->
        <div 
          v-if="index !== items.length - 1" 
          class="absolute top-3 left-[50%] w-full h-[2px]"
          :class="items.indexOf(current) > index ? 'bg-blue-600' : 'bg-gray-200'"
        ></div>
        
        <!-- Círculo de paso -->
        <div 
          class="relative flex items-center justify-center w-6 h-6 rounded-full text-xs font-medium z-10 transition-colors duration-300 ring-4 ring-white"
          :class="[
            current === item ? 'bg-blue-600 text-white' : 
            items.indexOf(current) > index ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-400 border border-gray-300'
          ]"
        >
          <!-- Checkmark para pasos completados -->
          <svg v-if="items.indexOf(current) > index" class="w-3.5 h-3.5 text-white" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
          </svg>
          <span v-else>{{ index + 1 }}</span>
        </div>
        
        <!-- Etiqueta del paso -->
        <span 
          class="mt-3 text-xs font-semibold tracking-wide uppercase transition-colors duration-300 text-center"
          :class="current === item ? 'text-blue-900' : 'text-gray-400'"
        >
          {{ item }}
        </span>
      </li>
    </ol>
  </nav>
</template>
