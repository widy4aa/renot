<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
        <div class="w-full max-w-sm">

            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900">ReNot</h1>
                <p class="text-sm text-gray-500 mt-1">Buat kata sandi baru</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">

                <!-- Error token tidak valid -->
                <div v-if="!token" class="text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg px-4 py-3">
                    Link reset tidak valid atau sudah kadaluarsa.
                    <RouterLink :to="{ name: 'forgot-password' }" class="underline ml-1">Minta link baru.</RouterLink>
                </div>

                <template v-else>
                    <!-- Error -->
                    <div v-if="errorMessage" class="mb-5 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg px-4 py-3">
                        {{ errorMessage }}
                    </div>

                    <form @submit.prevent="handleSubmit" class="space-y-5">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                autocomplete="email"
                                required
                                placeholder="nama@perusahaan.com"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi Baru</label>
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                autocomplete="new-password"
                                required
                                minlength="8"
                                placeholder="Minimal 8 karakter"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Kata Sandi</label>
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                autocomplete="new-password"
                                required
                                placeholder="Ulangi kata sandi baru"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="isLoading"
                            class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            {{ isLoading ? 'Menyimpan...' : 'Simpan Kata Sandi Baru' }}
                        </button>
                    </form>
                </template>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const route = useRoute();
const router = useRouter();

const token = route.query.token ?? null;

const form = ref({
    token: token,
    email: route.query.email ?? '',
    password: '',
    password_confirmation: '',
});

const isLoading = ref(false);
const errorMessage = ref('');

async function handleSubmit() {
    isLoading.value = true;
    errorMessage.value = '';

    try {
        await auth.resetPassword(form.value);
        router.push({ name: 'login', query: { reset: 'success' } });
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
