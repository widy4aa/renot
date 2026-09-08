<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
        <div class="w-full max-w-sm">

            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900">ReNot</h1>
                <p class="text-sm text-gray-500 mt-1">Reset kata sandi Anda</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">

                <!-- Sukses -->
                <div v-if="successMessage" class="mb-5 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg px-4 py-3">
                    {{ successMessage }}
                </div>

                <!-- Error -->
                <div v-if="errorMessage" class="mb-5 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg px-4 py-3">
                    {{ errorMessage }}
                </div>

                <template v-if="!successMessage">
                    <p class="text-sm text-gray-600 mb-5">
                        Masukkan email Anda dan kami akan mengirimkan link untuk mereset kata sandi.
                    </p>

                    <form @submit.prevent="handleSubmit" class="space-y-5">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input
                                id="email"
                                v-model="email"
                                type="email"
                                autocomplete="email"
                                required
                                placeholder="nama@perusahaan.com"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="isLoading"
                            class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            {{ isLoading ? 'Mengirim...' : 'Kirim Link Reset' }}
                        </button>
                    </form>
                </template>

                <div class="mt-5 text-center">
                    <RouterLink :to="{ name: 'login' }" class="text-sm text-blue-600 hover:underline">
                        Kembali ke halaman login
                    </RouterLink>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();

const email = ref('');
const isLoading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

async function handleSubmit() {
    isLoading.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        const data = await auth.forgotPassword(email.value);
        successMessage.value = data.message;
    } catch (error) {
        const errors = error.response?.data?.errors;
        if (errors?.email) {
            errorMessage.value = errors.email[0];
        } else {
            errorMessage.value = error.response?.data?.message ?? 'Terjadi kesalahan. Coba lagi.';
        }
    } finally {
        isLoading.value = false;
    }
}
</script>
