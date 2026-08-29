<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import Modal from '@/components/modal.vue'
import axios from 'axios'

defineProps({
    show: Boolean
})

const emit = defineEmits(['close', 'import-started'])

const file = ref(null)
const init_date = ref(new Date().toISOString().split('T')[0])
const finish_date = ref('')
const loading = ref(false)
const errorMessage = ref('')

const handleFileChange = (e) => {
    file.value = e.target.files[0]
}

const submitUpload = async () => {
    if (!file.value) {
        errorMessage.value = 'Por favor selecciona un archivo Excel.'
        return
    }
    if (!init_date.value || !finish_date.value) {
        errorMessage.value = 'Por favor selecciona fecha de inicio y fin.'
        return
    }

    const formData = new FormData()
    formData.append('file', file.value)
    formData.append('init_date', init_date.value)
    formData.append('finish_date', finish_date.value)

    emit('import-started', formData)
}
</script>

<template>
    <Modal :show="show" @close="$emit('close')">
        <div class="p-4">
            <h3 class="text-lg font-semibold mb-4 text-zinc-900">Importar Encuesta desde Excel</h3>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-zinc-700 mb-1">Archivo Excel</label>
                <input 
                    type="file" 
                    accept=".xlsx, .xls"
                    @change="handleFileChange"
                    class="block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                />
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-zinc-700">Fecha Inicio</label>
                    <input type="date" v-model="init_date" class="w-full mt-1 rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700">Fecha Fin</label>
                    <input type="date" v-model="finish_date" class="w-full mt-1 rounded-md border-zinc-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                </div>
            </div>

            <p v-if="errorMessage" class="mb-4 text-sm text-red-600">{{ errorMessage }}</p>

            <div class="flex justify-end gap-3 mt-6">
                <button
                    @click="$emit('close')"
                    class="px-4 py-2 text-sm font-medium text-zinc-700 bg-zinc-100 rounded-md hover:bg-zinc-200">
                    Cancelar
                </button>
                <button
                    @click="submitUpload"
                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                    Importar
                </button>
            </div>
        </div>
    </Modal>
</template>

