<script setup>
defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    required: true
  }
});
</script>

<template>
  <div class="bg-white border border-slate-200 p-6 rounded-sm shadow-sm flex flex-col justify-between w-full sm:w-64 md:w-82">
    <h3 class="text-slate-500 text-sm font-medium uppercase tracking-wider self-start">
      {{ title }}
    </h3>
    <div class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-900 mt-4 text-center">
      {{ value }}
    </div>
  </div>
</template>
