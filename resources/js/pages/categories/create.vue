<template>
    <Head title="Categorias: crear-nueva" />
    <MainLayout>
        <div class="max-w-2xl mx-auto py-10 px-4">
            <CategoryForm :survey_id="page.props.surveyId" @update-categories="updateCategories" />
        </div>
        <div class="bg-white/30 backdrop-blur-md shadow-lg rounded-xl p-6 border border-blue-700/50 
            w-full
            h-125">
            <h3 class="text-xl text-center text-white font-extrabold mb-3">Listado de Categorías</h3>
            <div id="table-header" class="h-10 w-full">
                <table class="table-fixed w-full text-left">
                    <thead>
                        <tr class="border-b border-white/30 text-white text-lg">
                            <th class="w-30">Orden</th>
                            <th>Nombre</th>
                            <th class="w-55 text-center">Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div id="table-body" class="w-full max-h-100 overflow-y-scroll scrollbar-thumb-blue-800 scrollbar-track-white/30">
                <table class="table-fixed w-full">
                    <tbody class="">
                        <tr  v-for="category in categories" class="text-white border-b border-neutral-400">
                            <td class="py-2 w-30">
                                {{ category?.order }}
                            </td>
                            <td class="py-2">
                                {{ category?.name }}
                            </td>
                            <td class="py-2 w-45">
                                <div class="flex items-center justify-center gap-x-3 w-full">
                                    <Link :href="`/categories/details/${category.id}`">
                                        <Icon class="text-lg md:text-2xl text-blue-600 hover:text-blue-500 cursor-pointer" icon="ic:baseline-remove-red-eye"/>
                                    </Link>
                                    
                                    <Icon class="text-2xl text-yellow-600 hover:text-yellow-500 cursor-pointer" icon="ic:baseline-edit"/>
                                    <Icon class="text-lg md:text-2xl text-red-600 hover:text-red-500 cursor-pointer" icon="ic:baseline-restore-from-trash"/>
                                </div>
                            </td>
                        </tr>
                        
                    </tbody>
                </table>
            </div>
        </div>
    </MainLayout>
</template>
<script setup>
import { onMounted, ref } from 'vue';
import MainLayout from '@/layouts/main-layout.vue';
import CategoryForm from '@/components/forms/category-form.vue';
import { Icon } from '@iconify/vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { getCategoriesBySurvey } from '@/composables/api/surveys';

const page = usePage()
const categories = ref([])
onMounted(async() => {
    const {data,errorFlag} = await getCategoriesBySurvey(parseInt(page.props.surveyId))

    categories.value = data
});

const updateCategories = async (value)=>{
    try {
       const {data} = await getCategoriesBySurvey(page.props.surveyId)
       categories.value = data
    } catch (error) {
        
    }
}
</script>
