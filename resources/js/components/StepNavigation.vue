<script setup>
const props = defineProps({
  items: { type: Array, required: true },
  current: { type: String, required: true }
});
</script>

<template>
  <nav class="flex w-full overflow-hidden rounded-full border border-gray-200 bg-gray-50 shadow-sm mb-6">
    <div
      v-for="(item, index) in items"
      :key="index"
      :class="[
        'step-item relative flex items-center justify-center flex-1 py-3 px-6 transition-all duration-300',
        current === item ? 'bg-blue-800 text-white' : 'bg-white text-gray-400'
      ]"
    >
      <div class="flex items-center gap-2 z-10">
        <span class="text-xs font-bold uppercase tracking-tighter md:tracking-normal">{{ item }}</span>
        <div v-if="current === item" class="w-4 h-4 bg-green-500 rounded-full flex items-center justify-center">
            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
            </svg>
        </div>
      </div>
    </div>
  </nav>
</template>

<style scoped>
.step-item {
  clip-path: polygon(calc(100% - 12px) 0%, 100% 50%, calc(100% - 12px) 100%, 0% 100%, 12px 50%, 0% 0%);
  margin-left: -11px;
}
.step-item:first-child {
  clip-path: polygon(calc(100% - 12px) 0%, 100% 50%, calc(100% - 12px) 100%, 0% 100%, 0% 0%);
  margin-left: 0;
}
.step-item:last-child {
  clip-path: polygon(100% 0%, 100% 100%, 0% 100%, 12px 50%, 0% 0%);
}
</style>
