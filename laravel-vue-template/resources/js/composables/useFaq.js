import { inject, onUnmounted } from 'vue';

/**
 * Composable untuk set FAQ kontekstual per halaman.
 * Halaman memanggil setFaqs([...]) saat onMounted.
 * FAQ otomatis di-reset saat halaman di-unmount.
 */
export function useFaq() {
    const setPageFaqs = inject('setPageFaqs', null);

    function setFaqs(faqs) {
        if (setPageFaqs) setPageFaqs(faqs);
    }

    // Reset ke FAQ default saat komponen di-unmount
    onUnmounted(() => {
        if (setPageFaqs) setPageFaqs(null);
    });

    return { setFaqs };
}
