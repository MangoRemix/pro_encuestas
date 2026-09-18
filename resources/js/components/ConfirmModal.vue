<script setup>
import { useConfirm } from '@/composables/useConfirm';
import Modal from './modal.vue';

const { isOpen, title, message, accept, cancel } = useConfirm();
</script>

<template>
    <Modal :show="isOpen" @close="cancel">
        <div class="min-w-70 p-2 text-center">
            <h3 class="mb-3 text-lg font-bold text-slate-800">{{ title }}</h3>
            <p class="mb-6 text-slate-600">{{ message }}</p>
            <div class="flex justify-center gap-3">
                <button
                    type="button"
                    @click="cancel"
                    class="cursor-pointer rounded-lg border border-slate-300 px-4 py-2 font-medium text-slate-700 transition-colors hover:bg-slate-100"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    @click="accept"
                    class="cursor-pointer rounded-lg bg-red-600 px-4 py-2 font-medium text-white transition-colors hover:bg-red-500"
                >
                    Aceptar
                </button>
            </div>
        </div>
    </Modal>
</template>
