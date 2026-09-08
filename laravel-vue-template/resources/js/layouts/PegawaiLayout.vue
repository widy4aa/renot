<template>
    <div class="min-h-screen flex flex-col p-3 gap-3" style="background:#F0F0EE;">

        <!-- ── Topbar full width ── -->
        <AppTopbar
            :user="auth.user"
            :unread-count="unreadCount"
            notif-route="pegawai.notifikasi"
            profile-route="pegawai.profile"
            @logout="handleLogout"
            @notif-read="unreadCount = $event"
        />

        <!-- ── Area bawah: sidebar fixed center + konten ── -->
        <div class="flex gap-3 flex-1 relative">

            <!-- Sidebar: fixed, center vertical layar, tidak goyang -->
            <div
                class="shrink-0"
                :style="sidebarPlaceholderStyle"
            >
                <AppSidebar
                    :menu-groups="menuGroups"
                    :user="auth.user"
                    style="position:fixed; top:50%; transform:translateY(-50%); z-index:10;"
                    @logout="handleLogout"
                    @width-change="onWidthChange"
                />
            </div>

            <!-- Konten halaman -->
            <main class="flex-1 min-w-0 overflow-y-auto">
                <RouterView />
            </main>

        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';
import AppSidebar from '@/components/AppSidebar.vue';
import AppTopbar from '@/components/AppTopbar.vue';

const auth   = useAuthStore();
const router = useRouter();

const unreadCount    = ref(0);
const sidebarWidth   = ref(localStorage.getItem('renot_sidebar_collapsed') === 'true' ? 56 : 200);

// Placeholder lebar sidebar agar konten tidak tertimpa
const sidebarPlaceholderStyle = computed(() => ({
    width: sidebarWidth.value + 'px',
    transition: 'width 200ms ease',
}));

function onWidthChange(w) {
    sidebarWidth.value = w;
}

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
