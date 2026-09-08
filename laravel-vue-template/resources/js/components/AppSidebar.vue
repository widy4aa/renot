<template>
    <!-- Sidebar: fit-content height, tidak full, sticky dari atas -->
    <aside
        class="sidebar shrink-0 flex flex-col bg-white overflow-hidden"
        :class="collapsed ? 'sidebar-collapsed' : 'sidebar-expanded'"
        style="border-radius:16px; box-shadow:0 1px 4px rgba(0,0,0,0.08); align-self:start; position:sticky; top:12px;"
    >
        <!-- ── Navigation ────────────────────────────────── -->
        <nav class="py-3" :class="collapsed ? 'px-2' : 'px-2.5'">
            <template v-for="group in menuGroups" :key="group.label ?? 'main'">
                <!-- Label grup — hanya saat expanded -->
                <p
                    v-if="group.label && !collapsed"
                    class="text-[10px] font-semibold tracking-widest uppercase px-3 pt-3 pb-1.5"
                    style="color:#9CA3AF;"
                >
                    {{ group.label }}
                </p>

                <!-- Divider pengganti label saat collapsed -->
                <div
                    v-if="group.label && collapsed"
                    class="my-2 mx-1 h-px"
                    style="background:#F3F4F6;"
                ></div>

                <!-- Nav item -->
                <RouterLink
                    v-for="item in group.items"
                    :key="item.name"
                    :to="{ name: item.name }"
                    class="nav-item mb-0.5 relative"
                    :class="[
                        isActive(item) ? 'nav-active' : 'nav-default',
                        collapsed ? 'nav-collapsed' : 'nav-expanded'
                    ]"
                    :title="collapsed ? item.label : undefined"
                >
                    <!-- Icon -->
                    <span class="shrink-0 w-4 h-4 flex items-center justify-center" v-html="item.icon"></span>

                    <!-- Label — hanya saat expanded -->
                    <span v-if="!collapsed" class="flex-1 truncate text-sm">{{ item.label }}</span>

                    <!-- Badge saat expanded -->
                    <span
                        v-if="!collapsed && item.badge && item.badge > 0"
                        class="shrink-0 text-[10px] font-bold px-1.5 py-0.5 rounded-full text-white"
                        style="background:#ED1B2F; min-width:18px; text-align:center;"
                    >
                        {{ item.badge > 99 ? '99+' : item.badge }}
                    </span>

                    <!-- Badge dot saat collapsed -->
                    <span
                        v-if="collapsed && item.badge && item.badge > 0"
                        class="absolute top-1 right-1 w-2 h-2 rounded-full"
                        style="background:#ED1B2F;"
                    ></span>
                </RouterLink>
            </template>
        </nav>

        <!-- ── Divider + Toggle ───────────────────────────── -->
        <div
            class="flex items-center justify-center py-2 mx-2"
            style="border-top:1px solid #F3F4F6;"
        >
            <button
                @click="toggle"
                class="toggle-btn"
                :title="collapsed ? 'Perluas sidebar' : 'Ciutkan sidebar'"
            >
                <svg
                    class="w-3 h-3 transition-transform duration-200"
                    :class="collapsed ? 'rotate-180' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
        </div>
    </aside>
</template>

<script setup>
import { ref } from 'vue';
import { useRoute } from 'vue-router';

defineProps({
    menuGroups: { type: Array, required: true },
    user: { type: Object, default: null },
});

const emit = defineEmits(['logout', 'width-change']);

const route = useRoute();

const STORAGE_KEY = 'renot_sidebar_collapsed';
const collapsed = ref(localStorage.getItem(STORAGE_KEY) === 'true');

function toggle() {
    collapsed.value = !collapsed.value;
    localStorage.setItem(STORAGE_KEY, collapsed.value);
    // Emit lebar baru agar placeholder di layout bisa ikut
    emit('width-change', collapsed.value ? 56 : 200);
}

function isActive(item) {
    if (item.activeOn) {
        return item.activeOn.some(n => route.name === n || String(route.name).startsWith(n));
    }
    return route.name === item.name || String(route.name).startsWith(item.name + '.');
}
</script>

<style scoped>
.sidebar          { transition: width 200ms ease; }
.sidebar-expanded { width: 200px; }
.sidebar-collapsed{ width: 56px; }

.toggle-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 6px;
    color: #9CA3AF;
    transition: background 150ms, color 150ms;
}
.toggle-btn:hover { background: #F3F4F6; color: #374151; }

.nav-item {
    display: flex;
    align-items: center;
    border-radius: 10px;
    transition: background 150ms, color 150ms;
    font-weight: 500;
    text-decoration: none;
}
.nav-expanded  { gap: 10px; padding: 8px 12px; }
.nav-collapsed { justify-content: center; padding: 8px; }

.nav-default       { color: #6B7280; }
.nav-default:hover { background: #F9FAFB; color: #111827; }
.nav-active        { background: #EFF6FF; color: #006CB8; font-weight: 600; }
</style>
