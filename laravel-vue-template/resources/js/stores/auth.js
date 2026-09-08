import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null);
    const token = ref(localStorage.getItem('auth_token'));

    const isAuthenticated = computed(() => !!token.value);
    const isAdmin = computed(() => user.value?.role === 'admin');
    const isPegawai = computed(() => user.value?.role === 'pegawai');

    if (token.value) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
    }

    function setToken(newToken) {
        token.value = newToken;
        localStorage.setItem('auth_token', newToken);
        axios.defaults.headers.common['Authorization'] = `Bearer ${newToken}`;
    }

    function clearAuth() {
        token.value = null;
        user.value = null;
        localStorage.removeItem('auth_token');
        delete axios.defaults.headers.common['Authorization'];
    }

    async function login(credentials) {
        const { data } = await axios.post('/api/login', credentials);
        setToken(data.token);
        user.value = data.user;
        return data.user;
    }

    async function logout() {
        try {
            await axios.post('/api/logout');
        } catch {
            // Token mungkin sudah expired, tetap clear
        } finally {
            clearAuth();
        }
    }

    async function fetchUser() {
        if (!token.value) {
            return;
        }
        try {
            const { data } = await axios.get('/api/me');
            user.value = data;
        } catch {
            clearAuth();
        }
    }

    async function updateProfile(formData) {
        const { data } = await axios.post('/api/pegawai/profile', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        // Sinkronkan data user di store
        user.value = {
            ...user.value,
            ...data.user,
        };
        return data;
    }

    async function forgotPassword(email) {
        const { data } = await axios.post('/api/forgot-password', { email });
        return data;
    }

    async function resetPassword(payload) {
        const { data } = await axios.post('/api/reset-password', payload);
        return data;
    }

    return {
        user,
        token,
        isAuthenticated,
        isAdmin,
        isPegawai,
        login,
        logout,
        fetchUser,
        updateProfile,
        forgotPassword,
        resetPassword,
    };
});
