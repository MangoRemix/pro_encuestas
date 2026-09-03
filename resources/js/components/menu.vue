<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { Icon } from '@iconify/vue';
import { router } from '@inertiajs/vue3';
import MenuItem from '@/components/MenuItem.vue';

const props = defineProps({
  show: { type: Boolean, default: false },
  user: { type: Object, default: () => ({ name: 'Usuario Invitado' }) },
  items: { type: Array, default: () => [] },
});

const emit = defineEmits(['close']);

const userRole = props.user?.role ?? null;

const openDropdowns = ref({});

const toggleDropdown = (key) => {
  openDropdowns.value[key] = !openDropdowns.value[key];
};

const closeMenu = () => {
  emit('close');
};

const handleNavigation = () => {
  if (window.innerWidth < 1024) {
    emit('close');
  }
};

const handleLogout = () => {
  router.post('/logout');
};

watch(() => props.show, (newVal) => {
  document.body.style.overflow = newVal ? 'hidden' : '';
});

const handleKeyDown = (e) => {
  if (e.key === 'Escape' && props.show) {
    emit('close');
  }
};

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
  document.body.style.overflow = '';
});
</script>

<template>
  <div class="relative">
    <Transition name="fade-overlay">
      <div
        v-if="show"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 transition-opacity lg:hidden"
        @click="closeMenu"
      ></div>
    </Transition>

    <Transition name="slide-menu">
      <div
        v-if="show"
        class="fixed top-0 left-0 h-full w-80 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 shadow-2xl z-50 flex flex-col transition-transform duration-300 ease-in-out lg:translate-x-0"
      >
        <button
          @click="closeMenu"
          class="absolute top-4 right-4 p-2 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 dark:text-slate-400 cursor-pointer transition-colors"
        >
          <Icon icon="ic:round-close" class="text-2xl" />
        </button>

        <div class="flex flex-col items-center justify-center p-8 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
          <div class="relative w-24 h-24 rounded-full overflow-hidden border-4 border-white dark:border-slate-800 shadow-md mb-3">
            <img
              src="/images/logoAlcaldia.png"
              :alt="user.name"
              class="w-full h-full object-cover bg-white"
            />
          </div>
          <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100 tracking-wide text-center">
            {{ user.name }}
          </h2>
          <span class="text-xs text-slate-400 dark:text-slate-500 mt-1 font-medium">Panel de Usuario</span>
        </div>

        <div class="flex-1 overflow-y-auto px-4 py-6 menu-scrollable">
          <ul class="space-y-2">
            <MenuItem
              v-for="(item, index) in items"
              :key="index"
              :item="{ ...item, _key: String(index) }"
              :depth="0"
              :open-dropdowns="openDropdowns"
              :toggle-dropdown="toggleDropdown"
              :user-role="userRole"
              @close="handleNavigation"
            />

            <li class="mt-8 pt-6 border-t border-slate-200 dark:border-slate-800">
              <button
                @click="handleLogout"
                class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all duration-200 cursor-pointer"
              >
                <Icon icon="ic:round-logout" class="text-xl" />
                <span class="font-medium text-sm">Cerrar sesión</span>
              </button>
            </li>
          </ul>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.fade-overlay-enter-active,
.fade-overlay-leave-active {
  transition: opacity 0.3s ease;
}
.fade-overlay-enter-from,
.fade-overlay-leave-to {
  opacity: 0;
}

.slide-menu-enter-active,
.slide-menu-leave-active {
  transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.slide-menu-enter-from,
.slide-menu-leave-to {
  transform: translateX(-100%);
}

.expand-enter-active,
.expand-leave-active {
  transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.2s ease;
  max-height: 600px;
}
.expand-enter-from,
.expand-leave-to {
  max-height: 0;
  opacity: 0;
}
</style>

<style>
.menu-scrollable::-webkit-scrollbar {
  width: 6px;
}

.menu-scrollable::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.menu-scrollable::-webkit-scrollbar-thumb {
  background: #94a3b8;
  border-radius: 3px;
  transition: background 0.2s ease;
}

.menu-scrollable::-webkit-scrollbar-thumb:hover {
  background: #64748b;
}

.dark .menu-scrollable::-webkit-scrollbar-track {
  background: #1e293b;
}

.dark .menu-scrollable::-webkit-scrollbar-thumb {
  background: #475569;
}

.dark .menu-scrollable::-webkit-scrollbar-thumb:hover {
  background: #64748b;
}

/* Firefox */
.menu-scrollable {
  scrollbar-width: thin;
  scrollbar-color: #94a3b8 #f1f5f9;
}

.dark .menu-scrollable {
  scrollbar-color: #475569 #1e293b;
}
</style>
