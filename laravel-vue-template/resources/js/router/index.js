import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        // ─── Auth (guest only) ──────────────────────────────────
        {
            path: '/login',
            name: 'login',
            component: () => import('@/pages/auth/Login.vue'),
            meta: { guestOnly: true },
        },
        {
            path: '/lupa-password',
            name: 'forgot-password',
            component: () => import('@/pages/auth/ForgotPassword.vue'),
            meta: { guestOnly: true },
        },
        {
            path: '/reset-password',
            name: 'reset-password',
            component: () => import('@/pages/auth/ResetPassword.vue'),
            meta: { guestOnly: true },
        },

        // ─── Redirect /dashboard → berdasarkan role ─────────────
        {
            path: '/dashboard',
            name: 'dashboard',
            redirect: () => {
                const auth = useAuthStore();
                if (auth.isAdmin) return { name: 'admin.dashboard' };
                if (auth.isPegawai) return { name: 'pegawai.dashboard' };
                return { name: 'login' };
            },
        },

        // ─── Admin (admin only) ─────────────────────────────────
        {
            path: '/admin',
            component: () => import('@/layouts/AdminLayout.vue'),
            meta: { requiresAuth: true, role: 'admin' },
            children: [
                {
                    path: 'dashboard',
                    name: 'admin.dashboard',
                    component: () => import('@/pages/admin/Dashboard.vue'),
                },
                {
                    path: 'notifikasi',
                    name: 'admin.notifikasi',
                    component: () => import('@/pages/admin/Notifikasi.vue'),
                },
                {
                    path: 'profil',
                    name: 'admin.profile',
                    component: () => import('@/pages/admin/Profile.vue'),
                },
                {
                    path: 'dokumen',
                    name: 'admin.dokumen',
                    component: () => import('@/pages/admin/Dokumen.vue'),
                },
                {
                    path: 'dokumen/tambah',
                    name: 'admin.dokumen.tambah',
                    component: () => import('@/pages/admin/DokumenForm.vue'),
                },
                {
                    path: 'dokumen/:id',
                    name: 'admin.dokumen.detail',
                    component: () => import('@/pages/admin/DokumenDetail.vue'),
                },
                {
                    path: 'dokumen/:id/edit',
                    name: 'admin.dokumen.edit',
                    component: () => import('@/pages/admin/DokumenForm.vue'),
                },
                {
                    path: 'approval',
                    name: 'admin.approval',
                    component: () => import('@/pages/admin/Approval.vue'),
                },
                {
                    path: 'pegawai',
                    name: 'admin.pegawai',
                    component: () => import('@/pages/admin/Pegawai.vue'),
                },
                {
                    path: 'pegawai/tambah',
                    name: 'admin.pegawai.tambah',
                    component: () => import('@/pages/admin/PegawaiForm.vue'),
                },
                {
                    path: 'pegawai/:id/edit',
                    name: 'admin.pegawai.edit',
                    component: () => import('@/pages/admin/PegawaiForm.vue'),
                },
                {
                    path: 'departemen',
                    name: 'admin.departemen',
                    component: () => import('@/pages/admin/Departemen.vue'),
                },
                {
                    path: 'kategori',
                    name: 'admin.kategori',
                    component: () => import('@/pages/admin/Kategori.vue'),
                },
                {
                    path: 'pengaturan',
                    name: 'admin.pengaturan',
                    component: () => import('@/pages/admin/Pengaturan.vue'),
                },
                {
                    path: 'audit',
                    name: 'admin.audit',
                    component: () => import('@/pages/admin/Audit.vue'),
                },
                {
                    path: 'admin-management',
                    name: 'admin.management',
                    component: () => import('@/pages/admin/ManajemenAdmin.vue'),
                },
            ],
        },

        // ─── Pegawai (pegawai only) ─────────────────────────────
        {
            path: '/pegawai',
            component: () => import('@/layouts/PegawaiLayout.vue'),
            meta: { requiresAuth: true, role: 'pegawai' },
            children: [
                {
                    path: 'dashboard',
                    name: 'pegawai.dashboard',
                    component: () => import('@/pages/pegawai/Dashboard.vue'),
                },
                {
                    path: 'profil',
                    name: 'pegawai.profile',
                    component: () => import('@/pages/pegawai/Profile.vue'),
                },
                {
                    path: 'dokumen',
                    name: 'pegawai.dokumen',
                    component: () => import('@/pages/pegawai/Dokumen.vue'),
                },
                {
                    path: 'dokumen/tambah',
                    name: 'pegawai.dokumen.tambah',
                    component: () => import('@/pages/pegawai/DokumenForm.vue'),
                },
                {
                    path: 'dokumen/:id',
                    name: 'pegawai.dokumen.detail',
                    component: () => import('@/pages/pegawai/DokumenDetail.vue'),
                },
                {
                    path: 'dokumen/:id/edit',
                    name: 'pegawai.dokumen.edit',
                    component: () => import('@/pages/pegawai/DokumenForm.vue'),
                },
                {
                    path: 'notifikasi',
                    name: 'pegawai.notifikasi',
                    component: () => import('@/pages/pegawai/Notifikasi.vue'),
                },
            ],
        },

        // ─── Fallback ────────────────────────────────────────────
        {
            path: '/:pathMatch(.*)*',
            redirect: () => {
                const auth = useAuthStore();
                if (!auth.isAuthenticated) return { name: 'login' };
                if (auth.isAdmin) return { name: 'admin.dashboard' };
                return { name: 'pegawai.dashboard' };
            },
        },
    ],
});

router.beforeEach((to) => {
    const auth = useAuthStore();

    // Halaman guest only: redirect ke dashboard yang sesuai role
    if (to.meta.guestOnly && auth.isAuthenticated) {
        if (auth.isAdmin) return { name: 'admin.dashboard' };
        return { name: 'pegawai.dashboard' };
    }

    // Halaman yang butuh auth
    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        return { name: 'login' };
    }

    // Halaman yang butuh role tertentu — jika salah role, arahkan ke dashboard role yang benar
    if (to.meta.role && auth.user?.role !== to.meta.role) {
        if (auth.isAdmin) return { name: 'admin.dashboard' };
        if (auth.isPegawai) return { name: 'pegawai.dashboard' };
        return { name: 'login' };
    }
});

export default router;
