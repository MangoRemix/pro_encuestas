const LABELS = {
    ADMIN: 'Administrador',
    GESTOR_ENCUESTAS: 'Gestor de Encuestas',
    POLLSTER: 'Encuestador',
};

export const roleLabel = (roleName) => LABELS[roleName] ?? 'Encuestador';
