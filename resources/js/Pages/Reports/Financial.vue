<script setup>
import { computed, reactive, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useIncrementalList } from '@/Composables/useIncrementalList';
import { money, normalizeText, onlyDigits } from '@/Utils/formatters';

const props = defineProps({
    initialEntries: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    personTypeOptions: {
        type: Array,
        required: true,
    },
    typeOptions: {
        type: Array,
        required: true,
    },
    statusOptions: {
        type: Array,
        required: true,
    },
});

const filterForm = reactive({
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
    person_type: props.filters.person_type || '',
    person_search: props.filters.person_search || '',
    type: props.filters.type || '',
    status: props.filters.status || '',
});

const {
    items: loadedEntries,
    total: entriesTotal,
    hasMore: hasMoreEntries,
    loading: loadingEntries,
    error: entriesError,
    loadMore: loadMoreEntries,
    sync: syncEntries,
} = useIncrementalList(props.initialEntries, 'reports.entries');

watch(() => props.initialEntries, (entries) => syncEntries(entries));

const numberFormatter = new Intl.NumberFormat('pt-BR');

const filteredStatuses = computed(() => {
    if (filterForm.type === 'receivable') {
        return props.statusOptions.filter((status) => status.value !== 'paid');
    }

    if (filterForm.type === 'payable') {
        return props.statusOptions.filter((status) => status.value !== 'received');
    }

    return props.statusOptions;
});

const matchesPersonSearch = (entry) => {
    const search = normalizeText(filterForm.person_search);

    if (!search) {
        return true;
    }

    const searchDigits = onlyDigits(filterForm.person_search);
    const personText = normalizeText(`${entry.person_name || ''} ${entry.person_document || ''} ${entry.person_document_digits || ''}`);
    const documentDigits = onlyDigits(entry.person_document_digits);

    return personText.includes(search) || Boolean(searchDigits && documentDigits.includes(searchDigits));
};

const filteredEntries = computed(() => loadedEntries.value.filter((entry) => {
    if (filterForm.date_from && (!entry.due_date || entry.due_date < filterForm.date_from)) {
        return false;
    }

    if (filterForm.date_to && (!entry.due_date || entry.due_date > filterForm.date_to)) {
        return false;
    }

    if (filterForm.person_type && entry.person_type_slug !== filterForm.person_type) {
        return false;
    }

    if (filterForm.type && entry.type !== filterForm.type) {
        return false;
    }

    if (filterForm.status && entry.status !== filterForm.status) {
        return false;
    }

    return matchesPersonSearch(entry);
}));

const summary = computed(() => filteredEntries.value.reduce((totals, entry) => {
    const amount = Number(entry.amount || 0);

    totals.count += 1;
    totals.total_amount += amount;

    if (entry.type === 'receivable') {
        totals.receivable_total += amount;
    }

    if (entry.type === 'payable') {
        totals.payable_total += amount;
    }

    if (entry.status === 'pending') {
        totals.pending_total += amount;
    }

    if (entry.status === 'received') {
        totals.received_total += amount;
    }

    if (entry.status === 'paid') {
        totals.paid_total += amount;
    }

    if (entry.status === 'overdue') {
        totals.overdue_total += amount;
    }

    if (entry.status === 'cancelled') {
        totals.cancelled_total += amount;
    }

    totals.balance = totals.receivable_total - totals.payable_total;

    return totals;
}, {
    count: 0,
    total_amount: 0,
    receivable_total: 0,
    payable_total: 0,
    received_total: 0,
    paid_total: 0,
    pending_total: 0,
    overdue_total: 0,
    cancelled_total: 0,
    balance: 0,
}));

const summaryCards = computed(() => [
    {
        label: 'Registros',
        value: numberFormatter.format(summary.value.count),
        detail: 'Linhas filtradas',
        class: 'border-indigo-100 bg-indigo-50/70 text-indigo-700',
    },
    {
        label: 'Total filtrado',
        value: money(summary.value.total_amount),
        detail: 'Soma geral do filtro',
        class: 'border-slate-200 bg-white text-slate-700',
    },
    {
        label: 'A receber',
        value: money(summary.value.receivable_total),
        detail: 'Entradas no filtro',
        class: 'border-emerald-100 bg-emerald-50/70 text-emerald-700',
    },
    {
        label: 'A pagar',
        value: money(summary.value.payable_total),
        detail: 'Saidas no filtro',
        class: 'border-rose-100 bg-rose-50/70 text-rose-700',
    },
    {
        label: 'Saldo',
        value: money(summary.value.balance),
        detail: 'Receber menos pagar',
        class: summary.value.balance >= 0
            ? 'border-sky-100 bg-sky-50/70 text-sky-700'
            : 'border-rose-100 bg-rose-50/70 text-rose-700',
    },
]);

const secondaryTotals = computed(() => [
    { label: 'Pendente', value: summary.value.pending_total, class: 'text-amber-700' },
    { label: 'Recebido', value: summary.value.received_total, class: 'text-emerald-700' },
    { label: 'Pago', value: summary.value.paid_total, class: 'text-sky-700' },
    { label: 'Vencido', value: summary.value.overdue_total, class: 'text-rose-700' },
    { label: 'Cancelado', value: summary.value.cancelled_total, class: 'text-slate-500' },
]);

const typeBadgeClasses = {
    receivable: 'bg-emerald-50 text-emerald-700 ring-emerald-100',
    payable: 'bg-sky-50 text-sky-700 ring-sky-100',
};

const statusBadgeClasses = {
    pending: 'bg-amber-50 text-amber-700 ring-amber-100',
    received: 'bg-emerald-50 text-emerald-700 ring-emerald-100',
    paid: 'bg-sky-50 text-sky-700 ring-sky-100',
    overdue: 'bg-rose-50 text-rose-700 ring-rose-100',
    cancelled: 'bg-slate-100 text-slate-500 ring-slate-200',
};

const clearFilters = () => {
    filterForm.date_from = '';
    filterForm.date_to = '';
    filterForm.person_type = '';
    filterForm.person_search = '';
    filterForm.type = '';
    filterForm.status = '';
};

watch(() => filterForm.type, () => {
    filterForm.status = '';
});
</script>

<template>
    <AppLayout title="Relatorios">
        <template #header>
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                    Financeiro
                </p>
                <h1 class="text-xl font-semibold text-slate-900">
                    Relatorio financeiro
                </h1>
            </div>
        </template>

        <div class="space-y-6">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">
                    Movimentacoes financeiras
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    {{ filteredEntries.length }} registros filtrados em {{ loadedEntries.length }} carregados.
                </p>
            </div>

            <section class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-black/5">
                <form class="grid gap-4 xl:grid-cols-[150px_150px_190px_180px_180px_minmax(220px,1fr)_auto]" @submit.prevent>
                    <div>
                        <label for="date_from" class="text-sm font-medium text-slate-700">
                            Vencimento de
                        </label>
                        <input
                            id="date_from"
                            v-model="filterForm.date_from"
                            type="date"
                            class="mt-2 w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                    </div>

                    <div>
                        <label for="date_to" class="text-sm font-medium text-slate-700">
                            Vencimento ate
                        </label>
                        <input
                            id="date_to"
                            v-model="filterForm.date_to"
                            type="date"
                            class="mt-2 w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                    </div>

                    <div>
                        <label for="person_type" class="text-sm font-medium text-slate-700">
                            Cliente/Fornecedor
                        </label>
                        <select
                            id="person_type"
                            v-model="filterForm.person_type"
                            class="mt-2 w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                            <option value="">
                                Todos
                            </option>
                            <option v-for="personType in personTypeOptions" :key="personType.value" :value="personType.value">
                                {{ personType.label }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="type" class="text-sm font-medium text-slate-700">
                            Conta
                        </label>
                        <select
                            id="type"
                            v-model="filterForm.type"
                            class="mt-2 w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                            <option value="">
                                Todas
                            </option>
                            <option v-for="type in typeOptions" :key="type.value" :value="type.value">
                                {{ type.label }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="status" class="text-sm font-medium text-slate-700">
                            Status
                        </label>
                        <select
                            id="status"
                            v-model="filterForm.status"
                            class="mt-2 w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                            <option value="">
                                Todos
                            </option>
                            <option v-for="status in filteredStatuses" :key="status.value" :value="status.value">
                                {{ status.label }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="person_search" class="text-sm font-medium text-slate-700">
                            Buscar pessoa
                        </label>
                        <input
                            id="person_search"
                            v-model="filterForm.person_search"
                            type="search"
                            placeholder="Nome, CPF ou CNPJ"
                            class="mt-2 w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                    </div>

                    <div class="flex items-start gap-2 xl:items-end">
                        <button
                            type="button"
                            class="inline-flex w-full justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 xl:w-auto"
                            @click="clearFilters"
                        >
                            Limpar
                        </button>
                    </div>
                </form>
            </section>

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <article
                    v-for="card in summaryCards"
                    :key="card.label"
                    class="rounded-lg border p-5 shadow-sm"
                    :class="card.class"
                >
                    <p class="text-xs font-semibold uppercase tracking-wide opacity-70">
                        {{ card.label }}
                    </p>
                    <p class="mt-3 text-2xl font-semibold tracking-normal">
                        {{ card.value }}
                    </p>
                    <p class="mt-2 text-xs opacity-70">
                        {{ card.detail }}
                    </p>
                </article>
            </section>

            <section class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-black/5">
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                    <div v-for="item in secondaryTotals" :key="item.label">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            {{ item.label }}
                        </p>
                        <p class="mt-2 text-lg font-semibold" :class="item.class">
                            {{ money(item.value) }}
                        </p>
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-black/5">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Tipo
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Cliente/Fornecedor
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Descricao
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Valor
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Emissao
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Vencimento
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Status
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Liquidacao
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="filteredEntries.length === 0">
                                <td colspan="8" class="px-5 py-10 text-center text-sm text-slate-500">
                                    Nenhuma movimentacao encontrada nos dados carregados.
                                </td>
                            </tr>

                            <tr v-for="entry in filteredEntries" :key="entry.id" class="hover:bg-slate-50/70">
                                <td class="px-5 py-4">
                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-semibold ring-1"
                                        :class="typeBadgeClasses[entry.type]"
                                    >
                                        {{ entry.type_label }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-sm font-semibold text-slate-900">
                                        {{ entry.person_name }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ entry.person_type_label }}<span v-if="entry.person_document"> - {{ entry.person_document }}</span>
                                    </p>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ entry.description }}
                                </td>
                                <td class="px-5 py-4 text-sm font-semibold text-slate-900">
                                    {{ entry.amount_formatted }}
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ entry.issue_date_formatted }}
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ entry.due_date_formatted }}
                                </td>
                                <td class="px-5 py-4">
                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-semibold ring-1"
                                        :class="statusBadgeClasses[entry.status] || 'bg-slate-100 text-slate-600 ring-slate-200'"
                                    >
                                        {{ entry.status_label }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ entry.settlement_date_formatted || '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-5 py-4">
                    <div>
                        <p class="text-sm text-slate-500">
                            Mostrando {{ filteredEntries.length }} filtrados em {{ loadedEntries.length }} registros carregados de {{ entriesTotal }} disponiveis.
                        </p>
                        <p v-if="entriesError" class="mt-2 text-xs font-medium text-rose-600">
                            {{ entriesError }}
                        </p>
                    </div>

                    <button
                        v-if="hasMoreEntries"
                        type="button"
                        class="inline-flex justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300"
                        :disabled="loadingEntries"
                        @click="loadMoreEntries"
                    >
                        {{ loadingEntries ? 'Carregando...' : 'Carregar +20 contas' }}
                    </button>
                    <span v-else class="text-sm font-medium text-slate-400">
                        Todas as contas foram carregadas
                    </span>
                </div>
            </section>
        </div>
    </AppLayout>
</template>