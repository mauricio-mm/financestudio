import { ref } from 'vue';

export function useIncrementalList(initialPage, routeName, options = {}) {
    const getKey = options.getKey || ((item) => item.id);
    const extraParams = options.params || (() => ({}));

    const items = ref([]);
    const page = ref(1);
    const total = ref(0);
    const hasMore = ref(false);
    const loading = ref(false);
    const error = ref('');

    const sync = (payload = {}) => {
        items.value = [...(payload.data || [])];
        page.value = payload.current_page || 1;
        total.value = payload.total || items.value.length;
        hasMore.value = Boolean(payload.has_more);
        error.value = '';
    };

    const appendUnique = (incoming = []) => {
        const existing = new Set(items.value.map(getKey));

        incoming.forEach((item) => {
            const key = getKey(item);

            if (!existing.has(key)) {
                items.value.push(item);
                existing.add(key);
            }
        });
    };

    const fetchJson = async (url) => {
        const response = await fetch(url, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error('Nao foi possivel carregar os dados agora.');
        }

        return response.json();
    };

    const loadMore = async () => {
        if (loading.value || !hasMore.value) {
            return;
        }

        loading.value = true;
        error.value = '';

        try {
            const params = new URLSearchParams({
                ...extraParams(),
                page: String(page.value + 1),
            });

            const payload = await fetchJson(`${route(routeName)}?${params.toString()}`);

            appendUnique(payload.data || []);
            page.value = payload.current_page || page.value;
            total.value = payload.total || total.value;
            hasMore.value = Boolean(payload.has_more);
        } catch (requestError) {
            error.value = requestError.message;
        } finally {
            loading.value = false;
        }
    };

    sync(initialPage);

    return {
        items,
        page,
        total,
        hasMore,
        loading,
        error,
        loadMore,
        sync,
    };
}