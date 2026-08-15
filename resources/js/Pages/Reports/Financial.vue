<script setup>
import { computed, reactive, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { money } from '@/Utils/money';

const props = defineProps({
    report: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    people: {
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
    summary: {
        type: Object,
        required: true,
    },
});

const filterForm = reactive({
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
    person_id: props.filters.person_id || '',
    type: props.filters.type || '',
    status: props.filters.status || '',
});

const numberFormatter = new Intl.NumberFormat('pt-BR');

const filteredPeople = computed(() => {
    if (filterForm.type === 'receivable') {
        return props.people.filter((person) => person.type_slug === 'customer');
    }

    if (filterForm.type === 'payable') {
        return props.people.filter((person) => person.type_slug === 'supplier');
    }

    return props.people;
});

const filteredStatuses = computed(() => {
    if (filterForm.type === 'receivable') {
        return props.statusOptions.filter((status) => status.value !== 'paid');
    }

    if (filterForm.type === 'payable') {
        return props.statusOptions.filter((status) => status.value !== 'received');
    }

    return props.statusOptions;
});

const summaryCards = computed(() => [
    {
        label: 'Registros',
        value: numberFormatter.format(props.summary.count),
        detail: 'Linhas filtradas',
        class: 'border-indigo-100 bg-indigo-50/70 text-indigo-700',
    },
    {
        label: 'Total filtrado',
        value: money(props.summary.total_amount),
        detail: 'Soma geral do filtro',
        class: 'border-slate-200 bg-white text-slate-700',
    },
    {
        label: 'A receber',
        value: money(props.summary.receivable_total),
        detail: 'Entradas no filtro',
        class: 'border-emerald-100 bg-emerald-50/70 text-emerald-700',
    },
    {
        label: 'A pagar',
        value: money(props.summary.payable_total),
        detail: 'Saidas no filtro',
        class: 'border-rose-100 bg-rose-50/70 text-rose-700',
    },
    {
        label: 'Saldo',
        value: money(props.summary.balance),
        detail: 'Receber menos pagar',
        class: props.summary.balance >= 0
            ? 'border-sky-100 bg-sky-50/70 text-sky-700'
            : 'border-rose-100 bg-rose-50/70 text-rose-700',
    },
]);

const secondaryTotals = computed(() => [
    { label: 'Pendente', value: props.summary.pending_total, class: 'text-amber-700' },
    { label: 'Recebido', value: props.summary.received_total, class: 'text-emerald-700' },
    { label: 'Pago', value: props.summary.paid_total, class: 'text-sky-700' },
    { label: 'Vencido', value: props.summary.overdue_total, class: 'text-rose-700' },
    { label: 'Cancelado', value: props.summary.cancelled_total, class: 'text-slate-500' },
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

watch(() => filterForm.type, () => {
    filterForm.person_id = '';
    filterForm.status = '';
});

const applyFilters = () => {
    router.get(route('reports.index'), filterForm, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.date_from = '';
    filterForm.date_to = '';
    filterForm.person_id = '';
    filterForm.type = '';
    filterForm.status = '';

    router.get(route('reports.index'), {}, {
        preserveState: true,
        replace: true,
    });
};
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
                    Movimentacoes filtradas
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Filtre por vencimento, pessoa, tipo da conta e status.
                </p>
            </div>

            <section class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-black/5">
                <form class="grid gap-4 xl:grid-cols-[150px_150px_220px_260px_180px_auto]" @submit.prevent="applyFilters">
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
                        <label for="type" class="text-sm font-medium text-slate-700">
                            Tipo
                        </label>
                        <select
                            id="type"
                            v-model="filterForm.type"
                            class="mt-2 w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                            <option value="">
                                Todos
                            </option>
                            <option v-for="type in typeOptions" :key="type.value" :value="type.value">
                                {{ type.label }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="person_id" class="text-sm font-medium text-slate-700">
                            Cliente/Fornecedor
                        </label>
                        <select
                            id="person_id"
                            v-model="filterForm.person_id"
                            class="mt-2 w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                            <option value="">
                                Todos
                            </option>
                            <option v-for="person in filteredPeople" :key="person.value" :value="person.value">
                                {{ person.label }} - {{ person.type_label }}
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

                    <div class="flex items-end gap-2">
                        <button
                            type="submit"
                            class="inline-flex w-full justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 xl:w-auto"
                        >
                            Filtrar
                        </button>
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
                            <tr v-if="report.data.length === 0">
                                <td colspan="8" class="px-5 py-10 text-center text-sm text-slate-500">
                                    Nenhuma movimentacao encontrada para os filtros informados.
                                </td>
                            </tr>

                            <tr v-for="entry in report.data" :key="entry.id" class="hover:bg-slate-50/70">
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
                                        {{ entry.person_type_label }}
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

                <div
                    v-if="report.links.length > 3"
                    class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-5 py-4"
                >
                    <p class="text-sm text-slate-500">
                        Mostrando {{ report.from }} a {{ report.to }} de {{ report.total }} registros
                    </p>

                    <div class="flex flex-wrap gap-1">
                        <template v-for="link in report.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                preserve-scroll
                                class="rounded-lg px-3 py-1.5 text-sm font-medium transition"
                                :class="link.active ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100'"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="rounded-lg px-3 py-1.5 text-sm font-medium text-slate-300"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>