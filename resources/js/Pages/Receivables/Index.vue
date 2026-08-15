<script setup>
import { computed, reactive, ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import ReceivableForm from '@/Pages/Receivables/Partials/ReceivableForm.vue';

const props = defineProps({
    receivables: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    customers: {
        type: Array,
        required: true,
    },
    statuses: {
        type: Array,
        required: true,
    },
    summary: {
        type: Object,
        required: true,
    },
});

const filterForm = reactive({
    search: props.filters.search || '',
    person_id: props.filters.person_id || '',
    status: props.filters.status || '',
});

const showingReceivableModal = ref(false);
const editingReceivable = ref(null);

const today = () => new Date().toISOString().slice(0, 10);

const receivableForm = useForm({
    person_id: '',
    description: '',
    amount: '',
    issue_date: today(),
    due_date: '',
    status: 'pending',
    settlement_date: '',
});

const modalTitle = computed(() => (editingReceivable.value ? 'Editar conta a receber' : 'Nova conta a receber'));
const submitLabel = computed(() => (editingReceivable.value ? 'Salvar alteracoes' : 'Cadastrar'));

const summaryCards = computed(() => [
    { label: 'Pendente', value: props.summary.pending, class: 'border-amber-100 bg-amber-50/70 text-amber-700' },
    { label: 'Recebido', value: props.summary.received, class: 'border-emerald-100 bg-emerald-50/70 text-emerald-700' },
    { label: 'Vencido', value: props.summary.overdue, class: 'border-rose-100 bg-rose-50/70 text-rose-700' },
    { label: 'Cancelado', value: props.summary.cancelled, class: 'border-slate-200 bg-slate-50 text-slate-600' },
]);

const statusBadgeClasses = {
    pending: 'bg-amber-50 text-amber-700 ring-amber-100',
    received: 'bg-emerald-50 text-emerald-700 ring-emerald-100',
    overdue: 'bg-rose-50 text-rose-700 ring-rose-100',
    cancelled: 'bg-slate-100 text-slate-500 ring-slate-200',
};

const normalizeAmount = (value) => {
    let normalized = String(value || '').trim();

    if (normalized.includes(',') && normalized.includes('.')) {
        normalized = normalized.replaceAll('.', '');
    }

    return normalized.replace(',', '.');
};

const resetReceivableForm = (receivable = null) => {
    receivableForm.clearErrors();
    receivableForm.person_id = receivable?.person_id || '';
    receivableForm.description = receivable?.description || '';
    receivableForm.amount = receivable?.amount || '';
    receivableForm.issue_date = receivable?.issue_date || today();
    receivableForm.due_date = receivable?.due_date || '';
    receivableForm.status = receivable?.status || 'pending';
    receivableForm.settlement_date = receivable?.settlement_date || '';
};

const openCreateModal = () => {
    editingReceivable.value = null;
    resetReceivableForm();
    showingReceivableModal.value = true;
};

const openEditModal = (receivable) => {
    editingReceivable.value = receivable;
    resetReceivableForm(receivable);
    showingReceivableModal.value = true;
};

const closeReceivableModal = (force = false) => {
    if (receivableForm.processing && !force) {
        return;
    }

    showingReceivableModal.value = false;
    editingReceivable.value = null;
    receivableForm.clearErrors();
};

const normalizedReceivableData = (data) => ({
    ...data,
    amount: normalizeAmount(data.amount),
    settlement_date: data.status === 'received' ? data.settlement_date : null,
});

const submitReceivable = () => {
    receivableForm.transform(normalizedReceivableData);

    const options = {
        preserveScroll: true,
        onSuccess: () => closeReceivableModal(true),
    };

    if (editingReceivable.value) {
        receivableForm.put(route('receivables.update', editingReceivable.value.id), options);
        return;
    }

    receivableForm.post(route('receivables.store'), options);
};

const applyFilters = () => {
    router.get(route('receivables.index'), filterForm, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.search = '';
    filterForm.person_id = '';
    filterForm.status = '';

    router.get(route('receivables.index'), {}, {
        preserveState: true,
        replace: true,
    });
};

const destroyReceivable = (receivable) => {
    if (! window.confirm(`Remover ${receivable.description}?`)) {
        return;
    }

    router.delete(route('receivables.destroy', receivable.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Contas a Receber">
        <template #header>
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                    Financeiro
                </p>
                <h1 class="text-xl font-semibold text-slate-900">
                    Contas a Receber
                </h1>
            </div>
        </template>

        <div class="space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">
                        Recebimentos
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Lancamentos vinculados aos clientes cadastrados.
                    </p>
                </div>

                <button
                    type="button"
                    class="inline-flex justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
                    @click="openCreateModal"
                >
                    Nova conta
                </button>
            </div>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article
                    v-for="card in summaryCards"
                    :key="card.label"
                    class="rounded-lg border bg-white p-5 shadow-sm"
                    :class="card.class"
                >
                    <p class="text-xs font-semibold uppercase tracking-wide opacity-70">
                        {{ card.label }}
                    </p>
                    <p class="mt-3 text-2xl font-semibold tracking-normal">
                        {{ card.value }}
                    </p>
                </article>
            </section>

            <section class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-black/5">
                <form class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_260px_180px_auto]" @submit.prevent="applyFilters">
                    <div>
                        <label for="search" class="text-sm font-medium text-slate-700">
                            Buscar
                        </label>
                        <input
                            id="search"
                            v-model="filterForm.search"
                            type="text"
                            class="mt-2 w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                            placeholder="Descricao ou cliente"
                        >
                    </div>

                    <div>
                        <label for="person_id" class="text-sm font-medium text-slate-700">
                            Cliente
                        </label>
                        <select
                            id="person_id"
                            v-model="filterForm.person_id"
                            class="mt-2 w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                            <option value="">
                                Todos
                            </option>
                            <option v-for="customer in customers" :key="customer.value" :value="customer.value">
                                {{ customer.label }}
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
                            <option v-for="status in statuses" :key="status.value" :value="status.value">
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

            <section class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-black/5">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Cliente
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Descricao
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Valor
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Vencimento
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Status
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Recebimento
                                </th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Acoes
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="receivables.data.length === 0">
                                <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-500">
                                    Nenhuma conta a receber encontrada.
                                </td>
                            </tr>

                            <tr v-for="receivable in receivables.data" :key="receivable.id" class="hover:bg-slate-50/70">
                                <td class="px-5 py-4">
                                    <p class="text-sm font-semibold text-slate-900">
                                        {{ receivable.customer_name }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-400">
                                        Emissao {{ receivable.issue_date_formatted }}
                                    </p>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ receivable.description }}
                                </td>
                                <td class="px-5 py-4 text-sm font-semibold text-slate-900">
                                    {{ receivable.amount_formatted }}
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ receivable.due_date_formatted }}
                                </td>
                                <td class="px-5 py-4">
                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-semibold ring-1"
                                        :class="statusBadgeClasses[receivable.status] || 'bg-slate-100 text-slate-600 ring-slate-200'"
                                    >
                                        {{ receivable.status_label }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ receivable.settlement_date_formatted || '-' }}
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"
                                            @click="openEditModal(receivable)"
                                        >
                                            Editar
                                        </button>
                                        <button
                                            type="button"
                                            class="rounded-lg border border-rose-100 px-3 py-1.5 text-xs font-semibold text-rose-600 transition hover:bg-rose-50"
                                            @click="destroyReceivable(receivable)"
                                        >
                                            Excluir
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="receivables.links.length > 3"
                    class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-5 py-4"
                >
                    <p class="text-sm text-slate-500">
                        Mostrando {{ receivables.from }} a {{ receivables.to }} de {{ receivables.total }} registros
                    </p>

                    <div class="flex flex-wrap gap-1">
                        <template v-for="link in receivables.links" :key="link.label">
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

        <Modal :show="showingReceivableModal" max-width="2xl" @close="closeReceivableModal">
            <div class="border-b border-slate-100 px-6 py-5">
                <h2 class="text-lg font-semibold text-slate-900">
                    {{ modalTitle }}
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Informe os dados do recebimento previsto ou realizado.
                </p>
            </div>

            <ReceivableForm
                :form="receivableForm"
                :customers="customers"
                :statuses="statuses"
                :submit-label="submitLabel"
                @submit="submitReceivable"
                @cancel="closeReceivableModal"
            />
        </Modal>
    </AppLayout>
</template>