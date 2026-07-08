<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { Icon } from '@iconify/vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  user: {
    type: Object,
    default: () => ({
      name: 'Usuario Invitado',
    })
  },
  items: {
    type: Array,
    default: () => [
      {
        label: 'Inicio',
        icon: 'ic:round-home',
        link: '/'
      },
      {
        label: 'Gestión de usuarios',
        icon: 'ic:baseline-people',
        children: [
          {
            label: 'Encuestadores/Admins',
        children: [
              { label: 'Nuevo Encuestador/Admin', link: '/users/create', permission: 'ADMIN' },
        ]
      },
      {
            label: 'Encuestados',
        children: [
              { label: 'Nuevo Encuestado', link: '/surveys/create', permission: 'POLLSTER' },
        ]
      }
    ]
      },
      {
        label: 'Categorías',
        icon: 'ic:outline-question-mark',
        children: [
          { label: 'Crear nueva', link: '/categories' , permission:'ADMIN'},
        ]
      },
      {
        label: 'Configuración',
        icon: 'ic:baseline-settings',
        children: [
          { label: 'Mi Perfil', link: '/profile' },
          { label: 'Seguridad', link: '/security' }
        ]
      }
    ]
  }
});

const emit = defineEmits(['close']);

// Almacena el estado de apertura de los submenús por índice o identificador único
const openDropdowns = ref({});

const toggleDropdown = (index) => {
  openDropdowns.value[index] = !openDropdowns.value[index];
};

// Controlar el scroll del body cuando el menú esté activo
watch(() => props.show, (newVal) => {
  if (newVal) {
    document.body.style.overflow = 'hidden';
  } else {
    document.body.style.overflow = '';
  }
});

// Cerrar con la tecla Escape
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
    <!-- Backdrop Overlay (Fondo oscuro) -->
    <Transition name="fade-overlay">
      <div 
        v-if="show" 
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 transition-opacity" 
        @click="emit('close')"
      ></div>
    </Transition>

    <!-- Panel del Menú Lateral -->
    <Transition name="slide-menu">
      <div 
        v-if="show" 
        class="fixed top-0 left-0 h-full w-80 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 shadow-2xl z-50 flex flex-col transition-transform duration-300 ease-in-out"
      >
        <!-- Botón de Cerrar -->
        <button 
          @click="emit('close')" 
          class="absolute top-4 right-4 p-2 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 dark:text-slate-400 cursor-pointer transition-colors"
        >
          <Icon icon="ic:round-close" class="text-2xl" />
        </button>

        <!-- Cabecera: Perfil de Usuario -->
        <div class="flex flex-col items-center justify-center p-8 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
          <div class="relative w-24 h-24 rounded-full overflow-hidden border-4 border-white dark:border-slate-800 shadow-md mb-3">
            <img 
              src="" 
              :alt="user.name" 
              class="w-full h-full object-cover"
              @error="(e) => e.target.src = '/images/logoAlcaldia.png'"
            />
          </div>
          <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100 tracking-wide text-center">
            {{ user.name }}
          </h2>
          <span class="text-xs text-slate-400 dark:text-slate-500 mt-1 font-medium">Panel de Usuario</span>
        </div>

        <!-- Lista de Items del Menú -->
        <div class="flex-1 overflow-y-auto px-4 py-6">
          <ul class="space-y-2">
            <li v-for="(item, index) in items" :key="index" class="relative">
              <!-- Item tipo Dropdown (Tiene hijos) -->
              <div v-if="item.children && item.children.length > 0">
                <button 
                  @click="toggleDropdown(index)"
                  class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 hover:text-slate-950 dark:hover:text-white cursor-pointer transition-all duration-200"
                >
                  <div class="flex items-center gap-3">
                    <Icon v-if="item.icon" :icon="item.icon" class="text-xl text-slate-500" />
                    <span class="font-medium text-sm">{{ item.label }}</span>
                  </div>
                  <Icon 
                    icon="ic:round-keyboard-arrow-down" 
                    class="text-xl text-slate-400 transition-transform duration-200"
                    :class="{ 'rotate-180': openDropdowns[index] }"
                  />
                </button>

                <!-- Submenú con transición de colapso -->
                <Transition name="expand">
                  <ul v-show="openDropdowns[index]" class="mt-1 pl-4 space-y-1 overflow-hidden">
                    <li v-for="(subItem, subIndex) in item.children" :key="subIndex">
                      <!-- NIVEL 2: Item con hijos (Sub-dropdown) -->
                      <div v-if="subItem.children && subItem.children.length > 0">
                        <button
                          @click="toggleDropdown(`${index}-${subIndex}`)"
                          class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all duration-200 cursor-pointer"
                        >
                          <span>{{ subItem.label }}</span>
                          <Icon
                            icon="ic:round-keyboard-arrow-down"
                            class="text-lg transition-transform duration-200"
                            :class="{ 'rotate-180': openDropdowns[`${index}-${subIndex}`] }"
                          />
                        </button>

                        <!-- NIVEL 3: Hijos del subItem -->
                        <Transition name="expand">
                          <ul v-show="openDropdowns[`${index}-${subIndex}`]" class="mt-1 pl-4 space-y-1 overflow-hidden border-l border-slate-200 dark:border-slate-700 ml-3">
                            <li v-for="(nestedItem, nestedIndex) in subItem.children" :key="nestedIndex">
                              <a
                                :href="nestedItem.link"
                                class="block px-3 py-2 rounded-lg text-xs text-slate-500 dark:text-slate-400 hover:text-slate-950 dark:hover:text-white hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-all duration-200"
                                @click="emit('close')"
                              >
                                {{ nestedItem.label }}
                              </a>
                            </li>
                          </ul>
                        </Transition>
                      </div>

                      <!-- NIVEL 2: Enlace normal -->
                      <a
                        v-else-if="subItem.link"
                        :href="subItem.link"
                        class="block px-3 py-2 rounded-lg text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 hover:text-slate-950 dark:hover:text-white transition-all duration-200"
                        @click="emit('close')"
                      >
                        {{ subItem.label }}
                      </a>
                    </li>
                  </ul>
                </Transition>
              </div>

              <!-- Item Enlace Normal (No tiene hijos) -->
              <a
                v-else-if="item.link"
                :href="item.link"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 hover:text-slate-950 dark:hover:text-white transition-all duration-200"
                @click="emit('close')"
              >
                <Icon v-if="item.icon" :icon="item.icon" class="text-xl text-slate-500" />
                <span class="font-medium text-sm">{{ item.label }}</span>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
/* Transiciones para el Overlay (Fade) */
.fade-overlay-enter-active,
.fade-overlay-leave-active {
  transition: opacity 0.3s ease;
}
.fade-overlay-enter-from,
.fade-overlay-leave-to {
  opacity: 0;
}

/* Transiciones para el Menú Lateral (Deslizar Izquierda -> Derecha) */
.slide-menu-enter-active,
.slide-menu-leave-active {
  transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.slide-menu-enter-from,
.slide-menu-leave-to {
  transform: translateX(-100%);
}

/* Transición para expandir el dropdown de submenús */
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