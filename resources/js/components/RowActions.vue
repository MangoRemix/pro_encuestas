<script setup>
import { Icon } from '@iconify/vue';

defineProps({
    canEdit: { type: Boolean, default: true },
    isAdmin: { type: Boolean, default: false },
    isTrashed: { type: Boolean, default: false },
});

const emit = defineEmits(['edit', 'hide', 'restore', 'force-delete', 'view']);
</script>

<template>
    <div class="flex items-center justify-center gap-3">
        <template v-if="isTrashed">
            <Icon
                @click="emit('restore')"
                class="cursor-pointer text-xl text-green-500 hover:text-green-400"
                icon="ic:baseline-restore"
                title="Restaurar"
            />
            <Icon
                v-if="isAdmin"
                @click="emit('force-delete')"
                class="cursor-pointer text-xl text-red-600 hover:text-red-500"
                icon="ic:baseline-delete-forever"
                title="Eliminar permanentemente"
            />
        </template>
        <template v-else>
            <Icon
                @click="emit('view')"
                class="cursor-pointer text-xl text-blue-400 hover:text-blue-300"
                icon="ic:baseline-remove-red-eye"
                title="Ver"
            />
            <Icon
                v-if="canEdit"
                @click="emit('edit')"
                class="cursor-pointer text-xl text-yellow-500 hover:text-yellow-400"
                icon="ic:baseline-edit"
                title="Editar"
            />
            <Icon
                @click="emit('hide')"
                class="cursor-pointer text-xl text-red-500 hover:text-red-400"
                icon="ic:baseline-restore-from-trash"
                title="Ocultar"
            />
            <Icon
                v-if="isAdmin"
                @click="emit('force-delete')"
                class="cursor-pointer text-xl text-red-600 hover:text-red-500"
                icon="ic:baseline-delete-forever"
                title="Eliminar permanentemente"
            />
        </template>
    </div>
</template>
