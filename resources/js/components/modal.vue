<script setup>
defineProps({
  show: Boolean
});

defineEmits(['close']);
</script>

<template>
  <!-- Teleport mueve el modal al final del <body> para evitar problemas de z-index -->
  
    <Transition name="fade-modal">
      <div v-if="show" class="modal-overlay" @click.self="$emit('close')">
        <div class="modal-content">
          <slot />
          <button class="close-btn" @click="$emit('close')">&times;</button>
        </div>
      </div>
    </Transition>
  
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5); /* Oscurece el fondo */
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  position: relative;
  min-width: 300px;
  max-width: 90%;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.close-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  border: none;
  background: none;
  font-size: 1.5rem;
  cursor: pointer;
}

/* --- Animaciones --- */

/* La transición dura 0.3s */
.fade-modal-enter-active,
.fade-modal-leave-active {
  transition: opacity 0.3s ease;
}

/* El contenido escala ligeramente mientras el fondo aparece */
.fade-modal-enter-active .modal-content,
.fade-modal-leave-active .modal-content {
  transition: transform 0.3s ease;
}

.fade-modal-enter-from,
.fade-modal-leave-to {
  opacity: 0;
}

.fade-modal-enter-from .modal-content,
.fade-modal-leave-to .modal-content {
  transform: scale(0.9);
}
</style>