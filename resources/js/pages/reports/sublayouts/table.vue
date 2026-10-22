<template>
    <div class="px-10 py-1">
        <div
            v-if="categories && categories.length"
            class="w-full overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"
        >
            <div id="table-body" class="max-h-150 w-full overflow-x-auto">
                <table class="w-full border-collapse text-left text-slate-800">
                    <thead
                        class="sticky top-0 z-10 bg-slate-50 border-b border-slate-200 backdrop-blur-sm"
                    >
                        <tr
                            class="text-xs tracking-wider text-slate-500 uppercase font-semibold"
                        >
                            <th class="w-1/4 p-4">Categoría</th>
                            <th class="w-1/4 p-4">Pregunta</th>
                            <th class="w-1/2 p-4" colspan="2">
                                Respuesta / Votos
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template v-for="cat in categories" :key="cat.id">
                            <tr
                                v-for="(q, qIndex) in cat.questions"
                                :key="q.id"
                                class="transition-colors hover:bg-slate-50"
                            >
                                <td
                                    v-if="qIndex === 0"
                                    :rowspan="cat.questions.length"
                                    class="border-r border-slate-100 p-4 align-top font-bold text-slate-900"
                                >
                                    {{ cat.name }}
                                </td>
                                <td
                                    class="border-r border-slate-100 p-4 align-top text-slate-800"
                                >
                                    {{ q.name }}
                                </td>
                                <td class="p-0" colspan="2">
                                    <table class="w-full border-collapse">
                                        <tr
                                            v-for="ans in q.answers"
                                            :key="ans.id"
                                            class="border-b border-slate-100 last:border-0"
                                        >
                                            <td class="w-3/4 p-4 text-slate-700">
                                                {{ ans.name }}
                                            </td>
                                            <td
                                                class="w-1/4 p-4 text-center font-bold text-blue-600"
                                            >
                                                {{ ans.total_votes ?? 0 }}
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
        <p
            v-else
            class="rounded-xl border border-slate-200 bg-white py-8 text-center text-slate-500 italic shadow-sm"
        >
            No hay datos para mostrar...
        </p>
    </div>
</template>

<script setup>
defineProps(['categories']);
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
