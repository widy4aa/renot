<template>
    <div class="flex min-h-screen items-center justify-center bg-gray-50 px-4">
        <div class="w-full max-w-sm">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Masuk ke akun Anda</h1>
                <p class="text-gray-500 text-sm mt-2">Masukkan email dan kata sandi Anda</p>
            </div>

            <form @submit.prevent="handleLogin" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 space-y-5">
                <div v-if="errorMessage" class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-4 py-3">
                    {{ errorMessage }}
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="nama@contoh.com"
                    />
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="••••••••"
                    />
                </div>

                <button
                    type="submit"
                    :disabled="isLoading"
                    class="w-full bg-indigo-600 text-white py-2 px-4 rounded-lg text-sm font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    {{ isLoading ? 'Memproses...' : 'Masuk' }}
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const router = useRouter();

const form = ref({ email: '', password: '' });
const isLoading = ref(false);
const errorMessage = ref('');

async function handleLogin() {
    isLoading.value = true;
    errorMessage.value = '';

    try {
        await auth.login(form.value);
        router.push({ name: 'dashboard' });
    } catch (error) {
        errorMessage.value = error.response?.data?.message ?? 'Login gagal. Coba lagi.';
    } finally {
        isLoading.value = false;
    }
}
</script>
