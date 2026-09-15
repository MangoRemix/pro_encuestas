<template>
    <Head title="Categorias: crear-nueva" />
    <MainLayout>
        <NotificationBox
            class="absolute top-10 right-0 w-120"
            v-if="message"
            :is-error="isError"
            :message="message"
        ></NotificationBox>
        <div class="mx-auto max-w-2xl px-4 py-10">
            <CategoryForm
                :survey_id="page.props.surveyId"
                @update-categories="updateCategories"
            />
        </div>
        <div
            class="h-125 w-full rounded-xl border border-blue-700/50 bg-white/30 p-6 shadow-lg backdrop-blur-md"
        >
            <h3 class="mb-3 text-center text-xl font-extrabold text-white">
                Listado de Categorías
            </h3>
            <div id="table-header" class="h-10 w-full">
                <table class="w-full table-fixed text-left">
                    <thead>
                        <tr class="border-b border-white/30 text-lg text-white">
                            <th class="w-30">Orden</th>
                            <th>Nombre</th>
                            <th class="w-55 text-center">Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div
                id="table-body"
                class="max-h-100 w-full scrollbar-thumb-blue-800 scrollbar-track-white/30 overflow-y-scroll"
            >
                <table class="w-full table-fixed">
                    <tbody class="">
                        <tr
                            v-for="category in categories"
                            :key="category.id"
                            class="border-b border-neutral-400 text-white"
                        >
                            <td class="w-30 py-2">
                                {{ category?.order }}
                            </td>
                            <td class="py-2">
                                {{ category?.name }}
                            </td>
                            <td class="w-45 py-2">
                                <div
                                    class="flex w-full items-center justify-center gap-x-3"
                                >
                                    <Link
                                        :href="`/categories/details/${category.id}`"
                                    >
                                        <Icon
                                            class="cursor-pointer text-lg text-blue-600 hover:text-blue-500 md:text-2xl"
                                            icon="ic:baseline-remove-red-eye"
                                        />
                                    </Link>

                                    <Icon
                                        class="cursor-pointer text-2xl text-yellow-600 hover:text-yellow-500"
                                        icon="ic:baseline-edit"
                                    />
                                    <Icon
                                        class="cursor-pointer text-lg text-red-600 hover:text-red-500 md:text-2xl"
                                        icon="ic:baseline-restore-from-trash"
                                    />
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
const message = ref('');
const isError = ref(false);
import { Icon } from '@iconify/vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import CategoryForm from '@/components/forms/category-form.vue';
import NotificationBox from '@/components/notification-box.vue';
import { getCategoriesBySurvey } from '@/composables/api/surveys';
import MainLayout from '@/layouts/main-layout.vue';

const page = usePage();
const categories = ref([]);
onMounted(async () => {
    const { data } = await getCategoriesBySurvey(parseInt(page.props.surveyId));

    categories.value = data;
});

const updateCategories = async (status) => {
    try {
        message.value = status.message;
        isError.value = !status.success;

        setTimeout(() => {
            message.value = '';
        }, 3000);

        if (status.success) {
            const { data } = await getCategoriesBySurvey(page.props.surveyId);
            categories.value = data;
        }
    } catch (error) {
        console.error(error);
    }
};
</script>
