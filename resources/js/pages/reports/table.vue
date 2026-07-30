<template>
    <div class="p-6">
        
        <div v-if="reportData.length" class="bg-white/30 backdrop-blur-md shadow-lg rounded-xl p-6 border border-blue-700/50 
            w-full h-135">
            
            <div id="table-header" class="h-10 w-full mb-3">
                <table class="table-fixed w-full text-left">
                    <thead>
                        <tr class="border-b border-white/30 text-white text-lg">
                            <th class="text-sm p-2 lg:text-lg w-1/4">Categoría</th>
                            <th class="text-sm p-2 lg:text-lg w-1/4">Pregunta</th>
                            <th class="text-sm p-2 lg:text-lg w-1/4">Respuesta</th>
                            <th class="text-sm p-2 lg:text-lg w-1/4">Total</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div id="table-body" class="w-full max-h-100 overflow-y-scroll scrollbar-thumb-blue-800 scrollbar-track-white/30">
                <table class="table-fixed w-full">
                    <tbody class="">
                        <tr v-for="(item, i) in reportData" :key="i" class="text-white border-b border-neutral-400">
                            <td class="py-2 w-1/4">
                                {{ item.category_name }}
                            </td>
                            <td class="py-2 w-1/4">
                                {{ item.question_name }}
                            </td>
                            <td class="py-2 w-1/4">
                                {{ item.answer_name }}
                            </td>
                            <td class="py-2 w-1/4 font-bold">
                                {{ item.total }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <p v-else class="text-white/70 italic text-center py-4 bg-white/10 backdrop-blur-sm rounded-xl border border-blue-700/30">
            Cargando datos o sin resultados...
        </p>
    </div>
</template>
<script setup>

const {reportData} = defineProps(['reportData'])

</script>
<style scoped>
    
</style>