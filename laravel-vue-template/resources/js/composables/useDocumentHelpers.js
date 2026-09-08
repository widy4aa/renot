// Composable reusable untuk format dokumen di seluruh halaman pegawai

export function useDocumentHelpers() {
    const statusConfig = {
        aktif: {
            label: 'Aktif',
            badge: 'bg-green-100 text-green-700',
            dot: 'bg-green-500',
        },
        segera_expired: {
            label: 'Segera Expired',
            badge: 'bg-amber-100 text-amber-700',
            dot: 'bg-amber-500',
        },
        expired: {
            label: 'Expired',
            badge: 'bg-red-100 text-red-700',
            dot: 'bg-red-500',
        },
        pending_approval: {
            label: 'Pending Approval',
            badge: 'bg-gray-100 text-gray-600',
            dot: 'bg-gray-400',
        },
        ditolak: {
            label: 'Ditolak',
            badge: 'bg-pink-100 text-pink-700',
            dot: 'bg-pink-500',
        },
    };

    function getStatusConfig(status) {
        return statusConfig[status] ?? { label: status, badge: 'bg-gray-100 text-gray-600', dot: 'bg-gray-400' };
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
