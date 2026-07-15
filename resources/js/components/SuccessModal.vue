<template>
  <Transition name="fade">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
      <div class="bg-white p-8 rounded-2xl shadow-2xl flex flex-col items-center animate-bounce-in">
        <div class="w-20 h-20 rounded-full border-4 border-green-500 flex items-center justify-center mb-4">
          <svg class="w-10 h-10 text-green-500 checkmark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <p class="text-gray-800 font-semibold text-lg">Guardado exitoso</p>
      </div>
    </div>
  </Transition>
</template>

<script setup>
defineProps({ show: Boolean });
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.animate-bounce-in { animation: bounceIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); }

@keyframes bounceIn {
  0% { transform: scale(0.5); opacity: 0; }
  100% { transform: scale(1); opacity: 1; }
}

.checkmark {
  stroke-dasharray: 20;
  stroke-dashoffset: 20;
  animation: draw 0.6s ease forwards 0.3s;
}

@keyframes draw {
  to { stroke-dashoffset: 0; }
}
</style>
