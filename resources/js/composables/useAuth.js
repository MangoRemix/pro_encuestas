import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useAuth() {
    const page = usePage();

    const user = computed(() => page.props.auth?.user ?? null);
    const isAdmin = computed(() => user.value?.role === 'ADMIN');
    const canManageSurveys = computed(() =>
        ['ADMIN', 'GESTOR_ENCUESTAS'].includes(user.value?.role),
    );

    return { user, isAdmin, canManageSurveys };
}
