<script setup>
import { computed, reactive, ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import FinancialEntryForm from '@/Pages/FinancialEntries/Partials/FinancialEntryForm.vue';

const props = defineProps({
    entries: {
        type: Object,
        required: true,
    },
    activeType: {
        type: String,
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
    statuses: {
        type: Array,
        required: true,
    },
    typeOptions: {
        type: Array,
        required: true,
    },
    summary: {
        type: Object,
        required: true,
    },
});

const filterForm = reactive({
    type: props.filters.type || props.activeType,
    search: props.filters.search || '',
    person_id: props.filters.person_id || '',
    status: props.filters.status || '',
});

const showingEntryModal = ref(false);
const editingEntry = ref(null);

const today = () => new Date().toISOString().slice(0, 10);
const isPayable = computed(() => props.activeType === 'payable');

const settledStatusFor = (type) => (type === 'payable' ? 'paid' : 'received');
const settledStatus = computed(() => settledStatusFor(props.activeType));

const labels = computed(() => {
    if (isPayable.value) {
        return {
            title: 'Contas a Pagar',
            subtitle: 'Lancamentos vinculados aos fornecedores cadastrados.',
            person: 'Fornecedor',
            personPlural: 'Fornecedores',
            personFilter: 'Fornecedor',
            actionTitle: 'Pagamentos',
            newButton: 'Nova conta',
            modalCreate: 'Nova conta a pagar',
            modalEdit: 'Editar conta a pagar',
            modalDescription: 'Informe os dados do pagamento previsto ou realizado.',
            settlementDate: 'Data do pagamento',
            settlementColumn: 'Pagamento',
            settledSummary: 'Pago',
            descriptionPlaceholder: 'Ex: Aluguel, fornecedor, assinatura',
            searchPlaceholder: 'Descricao ou fornecedor',
            empty: 'Nenhuma conta a pagar encontrada.',
        };
    }

    return {
        title: 'Contas a Receber',
        subtitle: 'Lancamentos vinculados aos clientes cadastrados.',
        person: 'Cliente',
        personPlural: 'Clientes',
        personFilter: 'Cliente',
        actionTitle: 'Recebimentos',
        newButton: 'Nova conta',
        modalCreate: 'Nova conta a receber',
        modalEdit: 'Editar conta a receber',
        modalDescription: 'Informe os dados do recebimento previsto ou realizado.',
        settlementDate: 'Data do recebimento',
        settlementColumn: 'Recebimento',
        settledSummary: 'Recebido',
        descriptionPlaceholder: 'Ex: Mensalidade de servico',
        searchPlaceholder: 'Descricao ou cliente',
        empty: 'Nenhuma conta a receber encontrada.',
    };
});

const entryForm = useForm({
    type: props.activeType,
    person_id: '',
    description: '',
    amount: '',
    issue_date: today(),
    due_date: '',
    status: 'pending',
    settlement_date: '',
});

const modalTitle = computed(() => (editingEntry.value ? labels.value.modalEdit : labels.value.modalCreate));
const submitLabel = computed(() => (editingEntry.value ? 'Salvar alteracoes' : 'Cadastrar'));

const summaryCards = computed(() => [
    { label: 'Pendente', value: props.summary.pending, class: 'border-amber-100 bg-amber-50/70 text-amber-700' },
    { label: labels.value.settledSummary, value: props.summary.settled, class: 'border-emerald-100 bg-emerald-50/70 text-emerald-700' },
    { label: 'Vencido', value: props.summary.overdue, class: 'border-rose-100 bg-rose-50/70 text-rose-700' },
    { label: 'Cancelado', value: props.summary.cancelled, class: 'border-slate-200 bg-slate-50 text-slate-600' },
]);

const statusBadgeClasses = {
    pending: 'bg-amber-50 text-amber-700 ring-amber-100',
    received: 'bg-emerald-50 text-emerald-700 ring-emerald-100',
    paid: 'bg-emerald-50 text-emerald-700 ring-emerald-100',
    overdue: 'bg-rose-50 text-rose-700 ring-rose-100',
    cancelled: 'bg-slate-100 text-slate-500 ring-slate-200',
};


const switchType = (type) => {
    router.get(route('financial-entries.index'), { type }, {
        replace: true,
    });
};

const resetEntryForm = (entry = null) => {
    entryForm.clearErrors();
    entryForm.type = entry?.type || props.activeType;
    entryForm.person_id = entry?.person_id || '';
    entryForm.description = entry?.description || '';
    entryForm.amount = entry?.amount || '';
    entryForm.issue_date = entry?.issue_date || today();
    entryForm.due_date = entry?.due_date || '';
    entryForm.status = entry?.status || 'pending';
    entryForm.settlement_date = entry?.settlement_date || '';
};

const openCreateModal = () => {
    editingEntry.value = null;
    resetEntryForm();
    showingEntryModal.value = true;
};

const openEditModal = (entry) => {
    editingEntry.value = entry;
    resetEntryForm(entry);
    showingEntryModal.value = true;
};

const closeEntryModal = (force = false) => {
    if (entryForm.processing && !force) {
        return;
    }

    showingEntryModal.value = false;
    editingEntry.value = null;
    entryForm.clearErrors();
};

const normalizedEntryData = (data) => ({
    ...data,
    amount: normalizeAmount(data.amount),
    settlement_date: data.status === settledStatusFor(data.type) ? data.settlement_date : null,
});

const submitEntry = () => {
    entryForm.transform(normalizedEntryData);

    const options = {
        preserveScroll: true,
        onSuccess: () => closeEntryModal(true),
    };

    if (editingEntry.value) {
        entryForm.put(route('financial-entries.update', editingEntry.value.id), options);
        return;
    }

    entryForm.post(route('financial-entries.store'), options);
};

const applyFilters = () => {
    filterForm.type = props.activeType;

    router.get(route('financial-entries.index'), filterForm, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.type = props.activeType;
    filterForm.search = '';
    filterForm.person_id = '';
    filterForm.status = '';

    router.get(route('financial-entries.index'), { type: props.activeType }, {
        preserveState: true,
        replace: true,
    });
};

const destroyEntry = (entry) => {
    if (! window.confirm(`Remover ${entry.description}?`)) {
        return;
    }

    router.delete(route('financial-entries.destroy', entry.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Contas">
        <template #header>
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                    Financeiro
                </p>
                <h1 class="text-xl font-semibold text-slate-900">
                    Contas
                </h1>
            </div>
        </template>

        <div class="space-y-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">
                        {{ labels.actionTitle }}
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ labels.subtitle }}
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="inline-flex rounded-lg bg-white p-1 shadow-sm ring-1 ring-black/5">
                        <button
                            v-for="option in typeOptions"
                            :key="option.value"
                            type="button"
                            class="rounded-md px-4 py-2 text-sm font-semibold transition"
                            :class="activeType === option.value ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-500 hover:text-slate-900'"
                            @click="switchType(option.value)"
                        >
                            {{ option.label }}
                        </button>
                    </div>

                    <button
                        type="button"
                        class="inline-flex justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
                        @click="openCreateModal"
                    >
                        {{ labels.newButton }}
                    </button>
                </div>
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
                            :placeholder="labels.searchPlaceholder"
                        >
                    </div>

                    <div>
                        <label for="person_id" class="text-sm font-medium text-slate-700">
                            {{ labels.personFilter }}
                        </label>
                        <select
                            id="person_id"
                            v-model="filterForm.person_id"
                            class="mt-2 w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                            <option value="">
                                Todos
                            </option>
                            <option v-for="person in people" :key="person.value" :value="person.value">
                                {{ person.label }}
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
                                    {{ labels.person }}
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
                                    {{ labels.settlementColumn }}
                                </th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Acoes
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="entries.data.length === 0">
                                <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-500">
                                    {{ labels.empty }}
                                </td>
                            </tr>

                            <tr v-for="entry in entries.data" :key="entry.id" class="hover:bg-slate-50/70">
                                <td class="px-5 py-4">
                                    <p class="text-sm font-semibold text-slate-900">
                                        {{ entry.person_name }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-400">
                                        Emissao {{ entry.issue_date_formatted }}
                                    </p>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ entry.description }}
                                </td>
                                <td class="px-5 py-4 text-sm font-semibold text-slate-900">
                                    {{ entry.amount_formatted }}
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
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"
                                            @click="openEditModal(entry)"
                                        >
                                            Editar
                                        </button>
                                        <button
                                            type="button"
                                            class="rounded-lg border border-rose-100 px-3 py-1.5 text-xs font-semibold text-rose-600 transition hover:bg-rose-50"
                                            @click="destroyEntry(entry)"
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
                    v-if="entries.links.length > 3"
                    class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-5 py-4"
                >
                    <p class="text-sm text-slate-500">
                        Mostrando {{ entries.from }} a {{ entries.to }} de {{ entries.total }} registros
                    </p>

                    <div class="flex flex-wrap gap-1">
                        <template v-for="link in entries.links" :key="link.label">
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

        <Modal :show="showingEntryModal" max-width="2xl" @close="closeEntryModal">
            <div class="border-b border-slate-100 px-6 py-5">
                <h2 class="text-lg font-semibold text-slate-900">
                    {{ modalTitle }}
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    {{ labels.modalDescription }}
                </p>
            </div>

            <FinancialEntryForm
                :form="entryForm"
                :people="people"
                :statuses="statuses"
                :labels="labels"
                :settled-status="settledStatus"
                :submit-label="submitLabel"
                @submit="submitEntry"
                @cancel="closeEntryModal"
            />
        </Modal>
    </AppLayout>
</template>