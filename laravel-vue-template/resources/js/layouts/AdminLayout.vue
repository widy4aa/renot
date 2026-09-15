<template>
    <div class="min-h-screen flex flex-col p-3 gap-3" style="background:#F2F2F0;">

        <!-- Topbar full width -->
        <AppTopbar
            :user="auth.user"
            :unread-count="unreadCount"
            notif-route="admin.notifikasi"
            @notif-read="unreadCount = $event"
        />

        <!-- Area bawah: sidebar kiri + konten -->
        <div class="flex gap-3 flex-1">

            <!-- Sidebar vertikal kiri (fixed, scroll-aware) -->
            <!-- topbarOffset = h-14(56) + p-3 wrapper top(12) + gap-3(12) = 80px -->
            <AppSidebar
                :menu-groups="menuGroups"
                :faqs="pageFaqs"
                :user="auth.user"
                profile-route="admin.profile"
                :topbar-offset="80"
                @logout="handleLogout"
                @width-change="sidebarWidth = $event"
            />

            <!-- Spacer yang mengikuti lebar sidebar -->
            <div class="shrink-0 transition-all duration-200" :style="{ width: sidebarWidth + 'px' }"></div>

            <!-- Konten halaman -->
            <main class="flex-1 min-w-0">
                <RouterView />
            </main>

        </div>
    </div>
</template>

<script setup>
import { ref, provide, onMounted } from 'vue';import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';
import AppSidebar from '@/components/AppSidebar.vue';
import AppTopbar from '@/components/AppTopbar.vue';

const auth   = useAuthStore();
const router = useRouter();

const unreadCount = ref(0);
const sidebarWidth = ref(64); // default collapsed

// ── FAQ dinamis per halaman ────────────────────────────
const pageFaqs = ref(null);
provide('setPageFaqs', (faqs) => { pageFaqs.value = faqs; });

// ── Notifikasi unread count ────────────────────────────
async function fetchUnreadCount() {
    try {
        const { data } = await axios.get('/api/admin/notifications');
        unreadCount.value = data.unread_count;
    } catch {
        unreadCount.value = 0;
    }
}

const menuGroups = [
    {
        label: null,
        items: [
            {
                name: 'admin.dashboard',
                label: 'Dashboard',
                icon: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>`,
            },
        ],
    },
    {
        label: 'Manajemen',
        items: [
            {
                name: 'admin.dokumen',
                label: 'Dokumen',
                icon: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>`,
            },
            {
                name: 'admin.approval',
                label: 'Approval',
                icon: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
            },
            {
                name: 'admin.notifikasi',
                label: 'Notifikasi',
                badge: unreadCount,
                icon: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>`,
            },
            {
                name: 'admin.pegawai',
                label: 'Pegawai',
                icon: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>`,
            },
        ],
    },
    {
        label: 'Pengaturan',
        items: [
            {
                name: 'admin.departemen',
                label: 'Departemen',
                icon: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>`,
            },
            {
                name: 'admin.kategori',
                label: 'Kategori Sertifikasi',
                icon: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>`,
            },
            {
                name: 'admin.pengaturan',
                label: 'Pengaturan Sistem',
                icon: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>`,
            },
            {
                name: 'admin.audit',
                label: 'Audit Trail',
                icon: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>`,
            },
            {
                name: 'admin.management',
                label: 'Manajemen Admin',
                icon: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
            },
        ],
    },
];

async function handleLogout() {
    await auth.logout();
    router.push({ name: 'login' });
}

onMounted(fetchUnreadCount);
</script>
