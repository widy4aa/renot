import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import { useAuthStore } from './stores/auth';
import './bootstrap';
import '../css/app.css';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);

// Restore user DULU sebelum router di-mount
// supaya beforeEach guard punya data auth yang benar
const auth = useAuthStore();

auth.fetchUser().finally(async () => {
    const { default: router } = await import('./router');
    app.use(router);
    app.mount('#app');
});
