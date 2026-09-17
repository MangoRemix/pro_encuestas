<script setup>
import { Icon } from '@iconify/vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    item: { type: Object, required: true },
    depth: { type: Number, default: 0 },
    openDropdowns: { type: Object, required: true },
    toggleDropdown: { type: Function, required: true },
    userRole: { type: String, default: null },
});

const emit = defineEmits(['close']);

const page = usePage();

const currentPath = computed(() => page.url.split('?')[0]);

const isActiveLink = (link) => {
    if (!link) {
        return false;
    }

    return link.split('?')[0] === currentPath.value;
};

const isActive = computed(() => isActiveLink(props.item.link));

const isVisible = computed(() => {
    const hasPerm =
        !props.item.permission || props.userRole === props.item.permission;

    if (!hasPerm) {
        return false;
    }

    if (props.item.children) {
        return visibleChildren.value.length > 0;
    }

    return !!props.item.link;
});

const visibleChildren = computed(() => {
    if (!props.item.children) {
        return [];
    }

    return props.item.children.filter((child) => {
        if (!child.permission) {
            return true;
        }

        return props.userRole === child.permission;
    });
});
</script>

<template>
    <li v-if="isVisible" class="relative">
        <!-- Item con hijos (dropdown) -->
        <div v-if="item.children && visibleChildren.length > 0">
            <button
                @click="toggleDropdown(item._key)"
                class="flex w-full cursor-pointer items-center justify-between rounded-xl px-4 py-3 text-slate-700 transition-all duration-200 hover:bg-slate-50 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800/50 dark:hover:text-white"
                :class="{ 'pl-8': depth > 0 }"
            >
                <div class="flex items-center gap-3">
                    <Icon
                        v-if="item.icon && depth === 0"
                        :icon="item.icon"
                        class="text-xl text-slate-500"
                    />
                    <span class="text-sm font-medium">{{ item.label }}</span>
                </div>
                <Icon
                    icon="ic:round-keyboard-arrow-down"
                    class="text-xl text-slate-400 transition-transform duration-200"
                    :class="{ 'rotate-180': openDropdowns[item._key] }"
                />
            </button>

            <Transition name="expand">
                <ul
                    v-show="openDropdowns[item._key]"
                    class="mt-1 space-y-1 overflow-hidden"
                    :class="
                        depth === 0
                            ? 'pl-4'
                            : 'ml-3 border-l border-slate-200 pl-4 dark:border-slate-700'
                    "
                >
                    <MenuItem
                        v-for="(child, childIndex) in visibleChildren"
                        :key="childIndex"
                        :item="{ ...child, _key: `${item._key}-${childIndex}` }"
                        :depth="depth + 1"
                        :open-dropdowns="openDropdowns"
                        :toggle-dropdown="toggleDropdown"
                        :user-role="userRole"
                        @close="emit('close')"
                    />
                </ul>
            </Transition>
        </div>

        <!-- Item enlace simple -->
        <Link
            v-else
            :href="item.link"
            class="flex items-center gap-3 rounded-xl px-4 py-3 transition-all duration-200"
            :class="[
                depth > 0 ? 'pl-8 text-sm' : '',
                depth > 1 ? 'text-xs' : '',
                isActive
                    ? 'bg-blue-50 font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
                    : 'text-slate-700 hover:bg-slate-50 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800/50 dark:hover:text-white',
            ]"
            @click="emit('close')"
        >
            <Icon
                v-if="item.icon && depth === 0"
                :icon="item.icon"
                class="text-xl"
                :class="
                    isActive
                        ? 'text-blue-600 dark:text-blue-400'
                        : 'text-slate-500'
                "
            />
            <span
                class="font-medium"
                :class="{ 'text-sm': depth <= 1, 'text-xs': depth > 1 }"
                >{{ item.label }}</span
            >
        </Link>
    </li>
</template>
