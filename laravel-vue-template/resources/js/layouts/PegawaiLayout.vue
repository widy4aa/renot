<template>
    <div class="min-h-screen flex bg-gray-100">
        <!-- Sidebar -->
        <aside class="w-60 shrink-0 bg-white border-r border-gray-200 flex flex-col">
            <!-- Logo -->
            <div class="h-16 flex items-center px-6 border-b border-gray-200">
                <span class="text-lg font-bold text-gray-900">ReNot</span>
                <span class="ml-2 text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">Pegawai</span>
            </div>

            <!-- Menu -->
            <nav class="flex-1 py-4 px-3 space-y-1 overflow-y-auto">
                <RouterLink
                    :to="{ name: 'pegawai.dashboard' }"
                    class="flex items-center gap-3 px-3 py-2 text-sm text-gray-600 rounded-md hover:bg-gray-100 hover:text-gray-900 transition-colors"
                    active-class="bg-green-50 text-green-700 font-medium"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Dashboard</span>
                </RouterLink>

                <RouterLink
                    :to="{ name: 'pegawai.dokumen' }"
                    class="flex items-center gap-3 px-3 py-2 text-sm text-gray-600 rounded-md hover:bg-gray-100 hover:text-gray-900 transition-colors"
                    active-class="bg-green-50 text-green-700 font-medium"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Dokumen Saya</span>
                </RouterLink>

                <RouterLink
                    :to="{ name: 'pegawai.notifikasi' }"
                    class="flex items-center gap-3 px-3 py-2 text-sm text-gray-600 rounded-md hover:bg-gray-100 hover:text-gray-900 transition-colors"
                    active-class="bg-green-50 text-green-700 font-medium"
                >
                    <div class="relative shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span
                            v-if="unreadCount > 0"
                            class="absolute -top-1.5 -right-1.5 min-w-[16px] h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center px-0.5"
                        >
                            {{ unreadCount > 99 ? '99+' : unreadCount }}
                        </span>
                    </div>
                    <span>Notifikasi</span>
                    <span
                        v-if="unreadCount > 0"
                        class="ml-auto text-xs bg-red-100 text-red-600 font-semibold px-1.5 py-0.5 rounded-full"
                    >
                        {{ unreadCount }}
                    </span>
                </RouterLink>

                <RouterLink
                    :to="{ name: 'pegawai.profile' }"
                    class="flex items-center gap-3 px-3 py-2 text-sm text-gray-600 rounded-md hover:bg-gray-100 hover:text-gray-900 transition-colors"
                    active-class="bg-green-50 text-green-700 font-medium"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Profil Saya</span>
                </RouterLink>
            </nav>

            <!-- User info + Logout -->
            <div class="border-t border-gray-200 p-4">
                <div class="flex items-center gap-3 mb-3">
                    <div class="relative shrink-0">
                        <img
                            v-if="auth.user?.avatar"
                            :src="auth.user.avatar"
                            alt="avatar"
                            class="w-8 h-8 rounded-full object-cover"
                        />
                        <div
                            v-else
                            class="w-8 h-8 rounded-full bg-green-600 flex items-center justify-center text-white text-sm font-semibold"
                        >
                            {{ auth.user?.name?.charAt(0) ?? 'P' }}
                        </div>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ auth.user?.name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ auth.user?.employee_number ?? auth.user?.email }}</p>
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
                <h1 class="text-sm font-medium text-gray-500">Portal Pegawai</h1>
            </header>

            <!-- Page content -->
            <main class="flex-1 p-6 overflow-y-auto">
                <RouterView />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

const auth   = useAuthStore();
const router = useRouter();

const unreadCount = ref(0);

async function fetchUnreadCount() {
    try {
        const { data } = await axios.get('/api/pegawai/notifications');
        unreadCount.value = data.unread_count;
    } catch {
        unreadCount.value = 0;
    }
}

async function handleLogout() {
    await auth.logout();
    router.push({ name: 'login' });
}

onMounted(fetchUnreadCount);
</script>
