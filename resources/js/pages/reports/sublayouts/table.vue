<template>
    <div class="p-6">
        <div v-if="categories && categories.length" class="bg-white/30 backdrop-blur-md shadow-lg rounded-xl p-6 border border-blue-700/50 w-full h-135">
            <div id="table-body" class="w-full max-h-120 overflow-y-scroll scrollbar-thumb-blue-800 scrollbar-track-white/30">
                <table class="table-fixed w-full text-left text-white">
                    <thead class="sticky top-0 bg-blue-900/80 backdrop-blur-sm">
                        <tr>
                            <th class="p-2 w-1/4">Categoría</th>
                            <th class="p-2 w-1/4">Pregunta</th>
                            <th class="p-2 w-1/4">Respuesta</th>
                            <th class="p-2 w-1/4 text-center">Votos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="cat in categories" :key="cat.id">
                            <tr v-for="(q, qIndex) in cat.questions" :key="q.id" class=" uppercase border-b border-white/20">
                                <td v-if="qIndex === 0" :rowspan="cat.questions.length" class="p-2 font-bold align-top">
                                    {{ cat.name }}
                                </td>
                                <td class="p-2 align-top">{{ q.name }}</td>
                                <td class="p-0" colspan="2">
                                    <table class="w-full">
                                        <tr v-for="ans in q.answers" :key="ans.id" class="border-b border-white/10 last:border-0">
                                            <td class="p-2 w-1/2">{{ ans.name }}</td>
                                            <td class="p-2 w-1/2 text-center font-bold">{{ ans.total_votes ?? 0 }}</td>
                                            <td class="p-2 w-1/2 text-center font-bold">{{ ans.count }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
        <p v-else class="text-white/70 italic text-center py-4 bg-white/10 backdrop-blur-sm rounded-xl border border-blue-700/30">
            No hay datos para mostrar...
        </p>
    </div>
</template>

<script setup>
defineProps(['categories'])
</script>
