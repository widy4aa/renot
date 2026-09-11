import { inject, onMounted, onUnmounted } from 'vue';

/**
 * Composable untuk set FAQ kontekstual per halaman.
 * Gunakan: useFaq([...items]) — FAQ otomatis di-set saat onMounted
 * dan di-reset saat halaman di-unmount.
 */
export function useFaq(faqs = null) {
    const setPageFaqs = inject('setPageFaqs', null);

    function setFaqs(items) {
        if (setPageFaqs) setPageFaqs(items);
    }

    if (faqs) {
        onMounted(() => {
            if (setPageFaqs) setPageFaqs(faqs);
        });
    }

    onUnmounted(() => {
        if (setPageFaqs) setPageFaqs(null);
    });

    return { setFaqs };
}
