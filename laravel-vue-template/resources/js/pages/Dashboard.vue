<template>
    <div>
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-gray-500 mt-1">Selamat datang, {{ auth.user?.name ?? 'Pengguna' }}.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <StatCard title="Total Pengguna" value="0" description="Pengguna terdaftar" />
            <StatCard title="Sesi Aktif" value="0" description="Sesi hari ini" />
            <StatCard title="Pendapatan" value="Rp 0" description="Bulan ini" />
        </div>
    </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth';
import StatCard from '@/components/StatCard.vue';

const auth = useAuthStore();
</script>
