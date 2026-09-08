<template>
    <div class="min-h-screen flex flex-col p-3 gap-3" style="background:#F2F2F0;">

        <!-- ── Topbar full width ── -->
        <AppTopbar
            :user="auth.user"
            :unread-count="unreadCount"
            notif-route="pegawai.notifikasi"
            @notif-read="unreadCount = $event"
        />

        <!-- ── Area bawah: sidebar kiri + konten ── -->
        <div class="flex gap-3 flex-1 relative">

            <!-- Sidebar vertikal kiri -->
            <AppSidebar
                :menu-groups="menuGroups"
                :user="auth.user"
                profile-route="pegawai.profile"
                @logout="handleLogout"
            />

            <!-- Konten halaman -->
            <main class="flex-1 min-w-0">
                <RouterView />
            </main>

        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, provide } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';
import AppSidebar from '@/components/AppSidebar.vue';
import AppTopbar from '@/components/AppTopbar.vue';

const auth   = useAuthStore();
const router = useRouter();

const unreadCount = ref(0);

// ── FAQ dinamis per halaman ────────────────────────────
const pageFaqs = ref(null);
provide('setPageFaqs', (faqs) => { pageFaqs.value = faqs; });

const menuGroups = [
    {
        label: null,
        items: [
            {
                name: 'pegawai.dashboard',
                label: 'Dashboard',
                icon: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>`,
            },
            {
                name: 'pegawai.dokumen',
                label: 'Dokumen Saya',
                activeOn: ['pegawai.dokumen', 'pegawai.dokumen.tambah', 'pegawai.dokumen.detail', 'pegawai.dokumen.edit'],
                icon: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>`,
            },
            {
                name: 'pegawai.notifikasi',
                label: 'Notifikasi',
                icon: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>`,
            },
        ],
    },
];

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
