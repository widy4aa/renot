// Composable reusable untuk format dokumen di seluruh halaman pegawai

export function useDocumentHelpers() {
    // Warna aktif menggunakan Lime Pertamina #ACC42A (PHILOSOPHY v1.1)
    const statusConfig = {
        aktif: {
            label: 'Aktif',
            badge: 'bg-lime-100 text-lime-700',
            badgeStyle: 'background:#F7FEE7; color:#5a6e0f;',
            dot: 'bg-lime-500',
            dotStyle: 'background:#ACC42A;',
            accentColor: '#ACC42A',
        },
        segera_expired: {
            label: 'Segera Expired',
            badge: 'bg-amber-100 text-amber-700',
            badgeStyle: 'background:#FEF3C7; color:#92400E;',
            dot: 'bg-amber-500',
            dotStyle: 'background:#D97706;',
            accentColor: '#D97706',
        },
        expired: {
            label: 'Expired',
            badge: 'bg-red-100 text-red-700',
            badgeStyle: 'background:#FEE2E2; color:#991B1B;',
            dot: 'bg-red-500',
            dotStyle: 'background:#ED1B2F;',
            accentColor: '#ED1B2F',
        },
        pending_approval: {
            label: 'Pending Approval',
            badge: 'bg-gray-100 text-gray-600',
            badgeStyle: 'background:#F3F4F6; color:#6B7280;',
            dot: 'bg-gray-400',
            dotStyle: 'background:#6B7280;',
            accentColor: '#6B7280',
        },
        ditolak: {
            label: 'Ditolak',
            badge: 'bg-pink-100 text-pink-700',
            badgeStyle: 'background:#FCE7F3; color:#9D174D;',
            dot: 'bg-pink-500',
            dotStyle: 'background:#DB2777;',
            accentColor: '#DB2777',
        },
    };

    function getStatusConfig(status) {
        return statusConfig[status] ?? {
            label: status,
            badge: 'bg-gray-100 text-gray-600',
            badgeStyle: 'background:#F3F4F6; color:#6B7280;',
            dot: 'bg-gray-400',
            dotStyle: 'background:#6B7280;',
            accentColor: '#6B7280',
        };
    }

    function formatDate(dateStr) {
        if (!dateStr) return '—';
        return new Date(dateStr).toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        });
    }

    function formatFileSize(bytes) {
        if (!bytes) return '—';
        if (bytes < 1024) return `${bytes} B`;
        if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
        return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
    }

    function daysUntilExpiry(expiryDate) {
        if (!expiryDate) return null;
        const diff = Math.floor((new Date(expiryDate) - new Date()) / (1000 * 60 * 60 * 24));
        return diff;
    }

    return { getStatusConfig, formatDate, formatFileSize, daysUntilExpiry };
}
