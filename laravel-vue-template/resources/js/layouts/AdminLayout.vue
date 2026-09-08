<template>
    <div class="min-h-screen flex bg-gray-100">
        <!-- Sidebar -->
        <aside class="w-60 shrink-0 bg-white border-r border-gray-200 flex flex-col">
            <!-- Logo -->
            <div class="h-16 flex items-center px-6 border-b border-gray-200">
                <span class="text-lg font-bold text-gray-900">ReNot</span>
                <span class="ml-2 text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">Admin</span>
            </div>

            <!-- Menu -->
            <nav class="flex-1 py-4 px-3 space-y-1 overflow-y-auto">
                <RouterLink
                    :to="{ name: 'admin.dashboard' }"
                    class="flex items-center gap-3 px-3 py-2 text-sm text-gray-600 rounded-md hover:bg-gray-100 hover:text-gray-900 transition-colors"
                    active-class="bg-blue-50 text-blue-700 font-medium"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Dashboard</span>
                </RouterLink>
            </nav>

            <!-- User info + Logout -->
            <div class="border-t border-gray-200 p-4">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-sm font-semibold shrink-0">
                        {{ auth.user?.name?.charAt(0) ?? 'A' }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ auth.user?.name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ auth.user?.email }}</p>
                    </div>
                </div>
                <button
                    @click="handleLogout"
                    class="w-full text-left text-sm text-red-600 hover:text-red-700 flex items-center gap-2 px-2 py-1.5 rounded-md hover:bg-red-50 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Keluar
                </button>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Topbar -->
            <header class="h-16 bg-white border-b border-gray-200 flex items-center px-6">
                <h1 class="text-sm font-medium text-gray-500">
                    Panel Admin
                </h1>
            </header>

            <!-- Page content -->
            <main class="flex-1 p-6 overflow-y-auto">
                <RouterView />
            </main>
        </div>
    </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const router = useRouter();

async function handleLogout() {
    await auth.logout();
    router.push({ name: 'login' });
}
</script>
