<template>
    <Head title="Paso2: crear-categorías" />
    <MainLayout>
        <NotificationBox
            class="absolute top-10 right-0 w-120"
            v-if="message"
            :is-error="isError"
            :message="message"
        ></NotificationBox>

        <StepNavigation :items="steps" :current="current" />

        <div class="mx-auto max-w-2xl px-4 py-5">
            <CategoryForm
                :survey_id="page.props.surveyId"
                @update-categories="updateCategories"
            />
        </div>

        <div class="mb-3 flex h-15 w-full items-center justify-end">
            <div class="w-fit">
                <button
                    @click="NextStep()"
                    class="yellow-button-app flex cursor-pointer items-center gap-x-2"
                    :disabled="!categories.length > 0"
                >
                    <Icon class="text-2xl" icon="ic:outline-plus" />
                    Cargar preguntas
                </button>
            </div>
        </div>

        <div
            class="w-full overflow-hidden rounded-lg border border-slate-700 bg-gray-500/50"
        >
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr
                            class="border-b border-slate-700 bg-slate-900/50 text-xs tracking-wider text-white uppercase"
                        >
                            <th class="w-30 p-4">Orden</th>
                            <th class="p-4">Nombre</th>
                            <th class="w-55 p-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <draggable
                        v-model="categories"
                        item-key="id"
                        tag="tbody"
                        class="custom-scrollbar divide-y divide-slate-700/50"
                        @end="onReorder"
                    >
                        <template #item="{ element: category }">
                            <tr
                                class="text-slate-200 transition-colors hover:bg-slate-600/30"
                            >
                                <td class="p-4">{{ category?.order }}</td>
                                <td class="p-4">{{ category?.name }}</td>
                                <td class="p-4">
                                    <div
                                        class="flex w-full items-center justify-center gap-x-3"
                                    >
                                        <Link
                                            :href="`/categories/details/${category.id}`"
                                        >
                                            <Icon
                                                class="cursor-pointer text-xl text-blue-400 hover:text-blue-300"
                                                icon="ic:baseline-remove-red-eye"
                                            />
                                        </Link>

                                        <Icon
                                            @click="editCategory(category)"
                                            class="cursor-pointer text-xl text-yellow-500 hover:text-yellow-400"
                                            icon="ic:baseline-edit"
                                        />
                                        <Icon
                                            @click="
                                                handleHideCategory(category.id)
                                            "
                                            class="cursor-pointer text-xl text-red-500 hover:text-red-400"
                                            icon="ic:baseline-restore-from-trash"
                                        />
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </draggable>
                </table>
            </div>
        </div>

        <Modal :show="isEditModalOpen" @close="isEditModalOpen = false">
            <CategoryForm
                :survey_id="page.props.surveyId"
                :category-id="categoryToEdit"
                @update-categories="updateCategories"
                @updated="handleCategoryUpdated"
            />
        </Modal>
    </MainLayout>
</template>
<script setup>
const message = ref('');
const isError = ref(false);
import { Icon } from '@iconify/vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import draggable from 'vuedraggable';
import CategoryForm from '@/components/forms/category-form.vue';
import Modal from '@/components/modal.vue';
import NotificationBox from '@/components/notification-box.vue';
import StepNavigation from '@/components/StepNavigation.vue';
import { hideCategory, reorderCategories } from '@/composables/api/categories';
import { getCategoriesBySurvey } from '@/composables/api/surveys';
import MainLayout from '@/layouts/main-layout.vue';
import { currentStep, stepsBreadcrumb } from '@/store/store';

const page = usePage();
const categories = ref([]);
const steps = stepsBreadcrumb;
const current = currentStep;
const isEditModalOpen = ref(false);
const categoryToEdit = ref(0);

onMounted(async () => {
    const { data } = await getCategoriesBySurvey(parseInt(page.props.surveyId));

    categories.value = data;
    current.value = 'Categorías';
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

const editCategory = (category) => {
    categoryToEdit.value = category.id;
    isEditModalOpen.value = true;
};

const handleCategoryUpdated = async () => {
    isEditModalOpen.value = false;
    categoryToEdit.value = 0;

    const { data } = await getCategoriesBySurvey(page.props.surveyId);
    categories.value = data;
};

const handleHideCategory = async (id) => {
    const { errorFlag, responseMessage } = await hideCategory(id);

    message.value = errorFlag
        ? responseMessage || 'Error al ocultar la categoría'
        : 'Categoría ocultada correctamente';
    isError.value = errorFlag;
    setTimeout(() => {
        message.value = '';
    }, 3000);

    if (!errorFlag) {
        const { data } = await getCategoriesBySurvey(page.props.surveyId);
        categories.value = data;
    }
};

const onReorder = async () => {
    const snapshot = categories.value.map((category) => ({ ...category }));
    const items = categories.value.map((category, index) => ({
        id: category.id,
        order: index + 1,
    }));

    categories.value = categories.value.map((category, index) => ({
        ...category,
        order: index + 1,
    }));

    const { errorFlag, responseMessage } = await reorderCategories(items);

    if (errorFlag) {
        categories.value = snapshot;
        isError.value = true;
        message.value = responseMessage || 'Error al reordenar las categorías';
        setTimeout(() => {
            message.value = '';
        }, 3500);
    }
};
const NextStep = () => {
    console.log(page.props);
    router.get('/surveys/create-survey/step-3', {
        surveyId: page.props.surveyId,
    });
};
</script>
<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #475569;
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #64748b;
}
</style>
