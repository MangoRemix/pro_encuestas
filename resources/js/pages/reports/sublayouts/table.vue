<template>
    <div class="px-10 py-1">
        <div v-if="categories && categories.length" class="bg-slate-900/50 border border-slate-700 rounded-lg overflow-hidden w-full h-135">
            <div id="table-body" class="w-full max-h-120 overflow-x-auto overflow-y-auto custom-scrollbar">
                <table class="w-full text-left border-collapse text-slate-200">
                    <thead class="sticky top-0 bg-slate-900/90 backdrop-blur-sm z-10">
                        <tr class="border-b border-slate-700 text-xs uppercase tracking-wider text-white">
                            <th class="p-4 w-1/4">Categoría</th>
                            <th class="p-4 w-1/4">Pregunta</th>
                            <th class="p-4 w-1/2" colspan="2">Respuesta / Votos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        <template v-for="cat in categories" :key="cat.id">
                            <tr v-for="(q, qIndex) in cat.questions" :key="q.id" class="hover:bg-slate-600/30 transition-colors">
                                <td v-if="qIndex === 0" :rowspan="cat.questions.length" class="p-4 font-bold border-r border-slate-700/50 align-top">
                                    {{ cat.name }}
                                </td>
                                <td class="p-4 align-top border-r border-slate-700/50">{{ q.name }}</td>
                                <td class="p-0" colspan="2">
                                    <table class="w-full border-collapse">
                                        <tr v-for="ans in q.answers" :key="ans.id" class="border-b border-slate-700/30 last:border-0">
                                            <td class="p-4 w-3/4">{{ ans.name }}</td>
                                            <td class="p-4 w-1/4 text-center font-bold text-blue-400">{{ ans.total_votes ?? 0 }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
        <p v-else class="text-slate-400 italic text-center py-8 bg-slate-900/30 rounded-xl border border-slate-700">
            No hay datos para mostrar...
        </p>
    </div>
</template>

<script setup>
defineProps(['categories'])
</script>

<style scoped>
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #64748b #0f172a;
}
.custom-scrollbar::-webkit-scrollbar {
    width: 8px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #0f172a;
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #64748b;
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>

