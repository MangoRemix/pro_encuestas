import axios from 'axios';

// Configuración común para ambos entornos (SSR y Cliente)
axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Solo asignar a window si estamos en el navegador
if (typeof window !== 'undefined') {
    window.axios = axios;
}

export default axios;
