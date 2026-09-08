<template>
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <RouterLink :to="{ name: 'home' }" class="text-xl font-bold text-gray-900">
                        {{ appName }}
                    </RouterLink>
                    <div class="hidden sm:ml-8 sm:flex sm:gap-6">
                        <RouterLink
                            :to="{ name: 'dashboard' }"
                            class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors"
                            active-class="text-gray-900 border-b-2 border-indigo-500"
                        >
                            Dashboard
                        </RouterLink>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-600">{{ auth.user?.name }}</span>
                    <button
                        @click="handleLogout"
                        class="text-sm text-gray-600 hover:text-gray-900 transition-colors"
                    >
                        Keluar
                    </button>
                </div>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const router = useRouter();
const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

async function handleLogout() {
    await auth.logout();
    router.push({ name: 'login' });
}
</script>
