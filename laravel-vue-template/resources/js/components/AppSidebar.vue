<template>
    <aside
        class="sidebar flex flex-col overflow-hidden shrink-0"
        :class="collapsed ? 'sidebar-collapsed' : 'sidebar-expanded'"
        style="background:#ED1B2F; border-radius:16px; position:sticky; top:12px; height:calc(100vh - 90px);"
    >
        <!-- ── Toggle collapse button ──────────────────────── -->
        <div class="flex items-center shrink-0" :class="collapsed ? 'justify-center px-0 pt-3 pb-1' : 'justify-end px-2.5 pt-3 pb-1'">
            <button
                @click="toggleCollapsed"
                class="w-7 h-7 rounded-lg flex items-center justify-center transition-all duration-150 icon-default nav-item"
                :title="collapsed ? 'Perluas sidebar' : 'Ciutkan sidebar'"
            >
                <svg class="w-4 h-4 transition-transform duration-200" :class="collapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#ffffff;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
            </button>
        </div>

        <!-- ── Nav items ──────────────────────────────────── -->
        <nav class="flex-1 flex flex-col justify-center overflow-y-auto py-2 px-2.5">
            <div class="flex flex-col gap-0.5">
            <template v-for="group in menuGroups" :key="group.label ?? 'main'">
                <!-- Group label (hidden when collapsed) -->
                <template v-if="!collapsed">
                    <p
                        v-if="group.label"
                        class="text-[10px] font-bold tracking-widest uppercase px-2 pt-4 pb-1.5"
                        style="color:rgba(255,255,255,0.50);"
                    >
                        {{ group.label }}
                    </p>
                    <div v-if="group.label" class="h-px mx-2 mb-2" style="background:rgba(255,255,255,0.15);"></div>
                </template>
                <!-- Spacer when collapsed and group has label -->
                <div v-else-if="group.label" class="h-2"></div>

                <RouterLink
                    v-for="item in group.items"
                    :key="item.name"
                    :to="{ name: item.name }"
                    class="nav-item flex items-center rounded-xl transition-all duration-150"
                    :class="[isActive(item) ? 'nav-active' : 'nav-default', collapsed ? 'justify-center px-0 py-2' : 'gap-2.5 px-3 py-2']"
                    :title="collapsed ? item.label : ''"
                >
                    <span
                        class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                        :class="isActive(item) ? 'icon-active' : 'icon-default'"
                    >
                        <span class="w-4 h-4 flex items-center justify-center" v-html="item.icon"></span>
                    </span>
                    <template v-if="!collapsed">
                        <span class="text-sm font-semibold truncate flex-1">{{ item.label }}</span>
                        <span
                            v-if="item.badge && item.badge > 0"
                            class="text-[10px] font-bold px-1.5 py-0.5 rounded-full shrink-0"
                            style="background:rgba(255,255,255,0.25); color:#fff;"
                        >
                            {{ item.badge > 99 ? '99+' : item.badge }}
                        </span>
                    </template>
                    <!-- Badge dot saat collapsed -->
                    <span
                        v-else-if="item.badge && item.badge > 0"
                        class="absolute top-1 right-1 w-2 h-2 rounded-full"
                        style="background:#fff;"
                    ></span>
                </RouterLink>
            </template>
            </div>
        </nav>

        <!-- ── Divider ────────────────────────────────────── -->
        <div class="mx-3 h-px" style="background:rgba(255,255,255,0.15);"></div>

        <!-- ── Bottom: FAQ + Profil + Logout ─────────────── -->
        <div class="px-2.5 py-3 space-y-0.5">

            <!-- FAQ toggle -->
            <button
                @click="showFaq = !showFaq"
                class="nav-item w-full flex items-center rounded-xl transition-all duration-150 nav-default"
                :class="collapsed ? 'justify-center px-0 py-2' : 'gap-2.5 px-3 py-2'"
                :title="collapsed ? 'Bantuan' : ''"
            >
                <span class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 icon-default">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <template v-if="!collapsed">
                    <span class="text-sm font-semibold flex-1 text-left">Bantuan</span>
                    <svg class="w-3.5 h-3.5 shrink-0 transition-transform duration-200" :class="showFaq ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:rgba(255,255,255,0.50);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </template>
            </button>

            <!-- Profil -->
            <RouterLink
                v-if="profileRoute"
                :to="{ name: profileRoute }"
                class="nav-item flex items-center rounded-xl transition-all duration-150 nav-default"
                :class="[isActiveRoute(profileRoute) ? 'nav-active' : 'nav-default', collapsed ? 'justify-center px-0 py-2' : 'gap-2.5 px-3 py-2']"
                :title="collapsed ? 'Profil' : ''"
            >
                <span class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0" :class="isActiveRoute(profileRoute) ? 'icon-active' : 'icon-default'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </span>
                <span v-if="!collapsed" class="text-sm font-semibold">Profil</span>
            </RouterLink>

            <!-- Logout -->
            <button
                @click="showLogoutModal = true"
                class="nav-item w-full flex items-center rounded-xl transition-all duration-150 nav-default"
                :class="collapsed ? 'justify-center px-0 py-2' : 'gap-2.5 px-3 py-2'"
                :title="collapsed ? 'Keluar' : ''"
            >
                <span class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 icon-default">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </span>
                <span v-if="!collapsed" class="text-sm font-semibold text-left">Keluar</span>
            </button>
        </div>

        <!-- ── FAQ Panel (slide up dari bawah sidebar) ────── -->
        <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-2"
        >
            <div
                v-if="showFaq"
                class="absolute bottom-full left-0 right-0 mb-2 rounded-2xl overflow-hidden"
                style="background:#fff; box-shadow:0 8px 32px rgba(0,0,0,0.15); border:1px solid #E5E7EB; z-index:20;"
            >
                <div class="px-4 py-3" style="border-bottom:1px solid #F3F4F6;">
                    <p class="text-xs font-bold tracking-widest uppercase" style="color:#ED1B2F;">Bantuan & FAQ</p>
                </div>
                <div v-for="faq in faqs" :key="faq.q" class="px-4 py-3.5" style="border-bottom:1px solid #F9FAFB;">
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0 mt-0.5" style="background:#FEE2E2;">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#ED1B2F;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="faq.icon"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold" style="color:#111827;">{{ faq.q }}</p>
                            <p class="text-xs mt-0.5 leading-relaxed" style="color:#6B7280;">{{ faq.a }}</p>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-2.5" style="background:#FAFAFA;">
                    <p class="text-[10px]" style="color:#9CA3AF;">ReNot · PT Pertamina (Persero)</p>
                </div>
            </div>
        </Transition>

    </aside>

    <!-- ── Modal Konfirmasi Logout ────────────────────────── -->
    <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="showLogoutModal" class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,0.40);" @click.self="showLogoutModal = false">
            <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background:#FEE2E2;">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:#ED1B2F;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-center" style="color:#111827;">Yakin ingin keluar?</h3>
                <p class="text-sm text-center mt-1" style="color:#6B7280;">Kamu akan keluar dari sesi ini dan perlu login kembali.</p>
                <div class="flex gap-3 mt-6">
                    <button @click="showLogoutModal = false" class="flex-1 py-2.5 rounded-xl text-sm font-semibold card-elevated transition-colors" style="color:#374151;">
                        Batal
                    </button>
                    <button @click="confirmLogout" class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white transition-colors" style="background:#ED1B2F;" onmouseover="this.style.background='#c8102e'" onmouseout="this.style.background='#ED1B2F'">
                        Ya, Keluar
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref } from 'vue';
import { useRoute } from 'vue-router';

const props = defineProps({
    menuGroups:   { type: Array,  required: true },
    user:         { type: Object, default: null },
    profileRoute: { type: String, default: null },
});

const emit = defineEmits(['logout', 'width-change']);
const route = useRoute();

const showFaq         = ref(false);
const showLogoutModal = ref(false);
const collapsed       = ref(true);

function toggleCollapsed() {
    collapsed.value = !collapsed.value;
    if (collapsed.value) { showFaq.value = false; }
    emit('width-change', collapsed.value ? 64 : 220);
}

function confirmLogout() {
    showLogoutModal.value = false;
    emit('logout');
}

function isActive(item) {
    if (item.activeOn) {
        return item.activeOn.some(n => route.name === n || String(route.name).startsWith(n));
    }
    return route.name === item.name || String(route.name).startsWith(item.name + '.');
}

function isActiveRoute(name) {
    return route.name === name;
}

const faqs = [
    { q: 'Cara upload dokumen baru?', a: 'Klik "Upload Dokumen" di Dashboard, isi formulir dan lampirkan file sertifikat.', icon: 'M12 4v16m8-8H4' },
    { q: 'Kapan reminder dikirim?', a: 'Sistem kirim notifikasi H-60, H-45, H-30, H-14, H-7, dan H-1 sebelum kadaluarsa.', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
    { q: 'Dokumen ditolak, apa yang dilakukan?', a: 'Baca alasan penolakan di detail dokumen, lalu edit dan upload ulang file yang sesuai.', icon: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z' },
];
</script>

<style scoped>
.sidebar { transition: width 200ms ease; }
.sidebar-expanded { width: 220px; }
.sidebar-collapsed { width: 64px; }

.nav-item { text-decoration: none; cursor: pointer; }
.nav-default { color: rgba(255,255,255,0.85); }
.nav-default:hover { background: rgba(255,255,255,0.12); color: #fff; }
.nav-default:hover .icon-default { background: rgba(255,255,255,0.22); }
.nav-active  { color: #fff; background: rgba(255,255,255,0.20); font-weight: 700; }

.icon-default { background: rgba(255,255,255,0.12); transition: background 150ms; }
.icon-active  { background: rgba(255,255,255,0.30); }
</style>
