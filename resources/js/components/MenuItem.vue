<script setup>
import { computed } from 'vue';
import { Icon } from '@iconify/vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  item: { type: Object, required: true },
  depth: { type: Number, default: 0 },
  openDropdowns: { type: Object, required: true },
  toggleDropdown: { type: Function, required: true },
  userRole: { type: String, default: null },
});

const emit = defineEmits(['close']);

const isVisible = computed(() => {
  const hasPerm = !props.item.permission || props.userRole === props.item.permission;
  if (!hasPerm) return false;
  if (props.item.children) {
    return visibleChildren.value.length > 0;
  }
  return !!props.item.link;
});

const visibleChildren = computed(() => {
  if (!props.item.children) return [];
  return props.item.children.filter((child) => {
    if (!child.permission) return true;
    return props.userRole === child.permission;
  });
});
</script>

<template>
  <li v-if="isVisible" class="relative">
    <!-- Item con hijos (dropdown) -->
    <div v-if="item.children && visibleChildren.length > 0">
      <button
        @click="toggleDropdown(item._key)"
        class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 hover:text-slate-950 dark:hover:text-white cursor-pointer transition-all duration-200"
        :class="{ 'pl-8': depth > 0 }"
      >
        <div class="flex items-center gap-3">
          <Icon v-if="item.icon && depth === 0" :icon="item.icon" class="text-xl text-slate-500" />
          <span class="font-medium text-sm">{{ item.label }}</span>
        </div>
        <Icon
          icon="ic:round-keyboard-arrow-down"
          class="text-xl text-slate-400 transition-transform duration-200"
          :class="{ 'rotate-180': openDropdowns[item._key] }"
        />
      </button>

      <Transition name="expand">
        <ul v-show="openDropdowns[item._key]" class="mt-1 space-y-1 overflow-hidden" :class="depth === 0 ? 'pl-4' : 'pl-4 border-l border-slate-200 dark:border-slate-700 ml-3'">
          <MenuItem
            v-for="(child, childIndex) in visibleChildren"
            :key="childIndex"
            :item="{ ...child, _key: `${item._key}-${childIndex}` }"
            :depth="depth + 1"
            :open-dropdowns="openDropdowns"
            :toggle-dropdown="toggleDropdown"
            :user-role="userRole"
            @close="emit('close')"
          />
        </ul>
      </Transition>
    </div>

    <!-- Item enlace simple -->
    <Link
      v-else
      :href="item.link"
      class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 hover:text-slate-950 dark:hover:text-white transition-all duration-200"
      :class="{ 'pl-8 text-sm': depth > 0, 'text-xs': depth > 1 }"
      @click="emit('close')"
    >
      <Icon v-if="item.icon && depth === 0" :icon="item.icon" class="text-xl text-slate-500" />
      <span class="font-medium" :class="{ 'text-sm': depth <= 1, 'text-xs': depth > 1 }">{{ item.label }}</span>
    </Link>
  </li>
</template>